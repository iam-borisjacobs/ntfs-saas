<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\FileJacket;
use App\Models\FileRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FileJacketController extends Controller
{
    /**
     * Enforce department isolation.
     */
    private function authorizeJacket(FileJacket $jacket): void
    {
        if ($jacket->department_id !== Auth::user()->department_id) {
            abort(403, 'You do not have access to this file jacket.');
        }
    }

    /**
     * List jackets for the user's department with search & filters.
     */
    public function index(Request $request)
    {
        $departmentId = Auth::user()->department_id;

        $query = FileJacket::with(['department', 'creator'])
            ->withCount('currentFiles')
            ->where('department_id', $departmentId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('jacket_code', 'ILIKE', "%{$search}%")
                  ->orWhere('title', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        $jackets = $query->orderBy('created_at', 'desc')->paginate(20);

        // Stats for the department
        $stats = [
            'total' => FileJacket::where('department_id', $departmentId)->count(),
            'active' => FileJacket::where('department_id', $departmentId)->where('status', 'active')->count(),
            'closed' => FileJacket::where('department_id', $departmentId)->where('status', 'closed')->count(),
            'archived' => FileJacket::where('department_id', $departmentId)->where('status', 'archived')->count(),
        ];

        return view('file-jackets.index', compact('jackets', 'stats'));
    }

    /**
     * Show the create jacket form.
     */
    public function create()
    {
        $department = Auth::user()->department;
        return view('file-jackets.create', compact('department'));
    }

    /**
     * Store a new jacket with auto-generated code.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $user = Auth::user();
        $department = Department::findOrFail($user->department_id);

        // Generate jacket code: [DEPT-CODE]/[YEAR]/[SEQ]
        $year = date('Y');
        $deptCode = strtoupper($department->code ?? substr($department->name, 0, 3));

        $lastSeq = FileJacket::where('department_id', $department->id)
            ->whereYear('created_at', $year)
            ->count();

        $nextSeq = str_pad($lastSeq + 1, 3, '0', STR_PAD_LEFT);
        $jacketCode = "{$deptCode}/{$year}/{$nextSeq}";

        while (FileJacket::where('jacket_code', $jacketCode)->exists()) {
            $lastSeq++;
            $nextSeq = str_pad($lastSeq + 1, 3, '0', STR_PAD_LEFT);
            $jacketCode = "{$deptCode}/{$year}/{$nextSeq}";
        }

        $jacket = FileJacket::create([
            'jacket_code' => $jacketCode,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'department_id' => $department->id,
            'created_by' => $user->id,
            'status' => 'active',
        ]);

        $this->audit('JACKET_CREATED', $jacket->id, null, [
            'jacket_code' => $jacketCode,
            'title' => $validated['title'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $jacket->id,
                'jacket_code' => $jacketCode,
                'title' => $validated['title'],
            ], 201);
        }

        return redirect()->route('file-jackets.show', $jacket->id)
            ->with('success', "File jacket {$jacketCode} created successfully.");
    }

    /**
     * Show jacket detail with linked documents and timeline.
     */
    public function show(FileJacket $jacket)
    {
        $this->authorizeJacket($jacket);
        $jacket->load(['department', 'creator', 'currentDepartment', 'currentHolder']);

        // Documents CURRENTLY physically stored in this jacket
        $files = FileRecord::with(['status', 'currentDepartment', 'currentOwner', 'movements' => function ($q) {
                $q->orderBy('dispatched_at', 'desc');
            }])
            ->where('current_file_jacket_id', $jacket->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Jacket Movements
        $jacketMovements = $jacket->movements()
            ->with(['fromDepartment', 'toDepartment', 'fromUser', 'toUser', 'dispatchedBy', 'receivedBy'])
            ->orderBy('dispatched_at', 'desc')
            ->get();

        // Build a timeline from all movements across all files
        $timeline = collect();
        foreach ($files as $file) {
            foreach ($file->movements as $movement) {
                $timeline->push([
                    'date' => $movement->dispatched_at,
                    'file_title' => $file->title,
                    'file_ref' => $file->file_reference_number,
                    'file_uuid' => $file->uuid,
                    'type' => $movement->movement_type,
                    'from' => $movement->fromUser->name ?? 'System',
                    'to' => $movement->toUser->name ?? ($movement->toDepartment->name ?? 'Department Inbox'),
                    'status' => $movement->acknowledgment_status,
                    'closed' => $movement->movement_closed,
                ]);
            }
        }
        $timeline = $timeline->sortByDesc('date')->values();

        // Available files to file into this jacket (same department, not filed anywhere)
        $availableFiles = FileRecord::where('current_department_id', $jacket->department_id)
            ->whereNull('current_file_jacket_id')
            ->whereHas('status', fn($q) => $q->where('is_terminal', false))
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get(['id', 'uuid', 'file_reference_number', 'title']);

        return view('file-jackets.show', compact('jacket', 'files', 'timeline', 'availableFiles', 'jacketMovements'));
    }

    /**
     * Show edit form.
     */
    public function edit(FileJacket $jacket)
    {
        $this->authorizeJacket($jacket);
        return view('file-jackets.edit', compact('jacket'));
    }

    /**
     * Update jacket title/description.
     */
    public function update(Request $request, FileJacket $jacket)
    {
        $this->authorizeJacket($jacket);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $jacket->update($validated);

        $this->audit('JACKET_UPDATED', $jacket->id, null, $validated);

        return redirect()->route('file-jackets.show', $jacket->id)
            ->with('success', 'File jacket updated successfully.');
    }

    /**
     * Close a jacket.
     */
    public function close(FileJacket $jacket)
    {
        $this->authorizeJacket($jacket);

        if ($jacket->status === 'closed') {
            return back()->withErrors(['error' => 'This jacket is already closed.']);
        }

        $jacket->update(['status' => 'closed']);
        $this->audit('JACKET_CLOSED', $jacket->id, null, ['status' => 'closed']);

        return back()->with('success', 'File jacket closed successfully.');
    }

    /**
     * Archive a jacket.
     */
    public function archive(FileJacket $jacket)
    {
        $this->authorizeJacket($jacket);

        if ($jacket->status === 'archived') {
            return back()->withErrors(['error' => 'This jacket is already archived.']);
        }

        $jacket->update(['status' => 'archived']);
        $this->audit('JACKET_ARCHIVED', $jacket->id, null, ['status' => 'archived']);

        return back()->with('success', 'File jacket archived successfully.');
    }

    /**
     * Reactivate a closed or archived jacket.
     */
    public function reactivate(FileJacket $jacket)
    {
        $this->authorizeJacket($jacket);

        if ($jacket->status === 'active') {
            return back()->withErrors(['error' => 'This jacket is already active.']);
        }

        $jacket->update(['status' => 'active']);
        $this->audit('JACKET_UPDATED', $jacket->id, null, ['status' => 'active', 'action' => 'reactivated']);

        return back()->with('success', 'File jacket reactivated successfully.');
    }

    /**
     * File a document into this jacket (sets current_file_jacket_id).
     */
    public function fileDocument(Request $request, FileJacket $jacket)
    {
        $this->authorizeJacket($jacket);

        if (in_array($jacket->status, ['closed', 'archived'])) {
            return back()->withErrors(['error' => 'Cannot file documents into a closed or archived jacket.']);
        }

        $validated = $request->validate([
            'file_id' => 'required|exists:file_records,id',
        ]);

        $file = FileRecord::findOrFail($validated['file_id']);

        if ($file->current_file_jacket_id !== null) {
            return back()->withErrors(['error' => 'This document is already filed in a jacket.']);
        }

        $file->update(['current_file_jacket_id' => $jacket->id]);

        $this->audit('DOCUMENT_FILED', $jacket->id, $file->id, [
            'file_reference' => $file->file_reference_number,
            'jacket_code' => $jacket->jacket_code,
        ]);

        return back()->with('success', "Document {$file->file_reference_number} filed into jacket.");
    }

    /**
     * Remove a document from a jacket (sets current_file_jacket_id = null).
     */
    public function unfileDocument(Request $request, FileJacket $jacket)
    {
        $this->authorizeJacket($jacket);

        $validated = $request->validate([
            'file_id' => 'required|exists:file_records,id',
        ]);

        $file = FileRecord::findOrFail($validated['file_id']);

        if ($file->current_file_jacket_id != $jacket->id) {
            return back()->withErrors(['error' => 'This document is not in this jacket.']);
        }

        $file->update(['current_file_jacket_id' => null]);

        $this->audit('DOCUMENT_UNFILED', $jacket->id, $file->id, [
            'file_reference' => $file->file_reference_number,
            'jacket_code' => $jacket->jacket_code,
        ]);

        return back()->with('success', "Document {$file->file_reference_number} removed from jacket.");
    }

    /**
     * Log an audit event.
     */
    private function audit(string $action, int $jacketId, ?int $fileId, array $data): void
    {
        DB::table('audit_logs')->insert([
            'agency_id' => 1,
            'action_type' => $action,
            'entity_type' => 'file_jackets',
            'entity_id' => $jacketId,
            'new_values' => json_encode(array_merge($data, ['file_id' => $fileId])),
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
