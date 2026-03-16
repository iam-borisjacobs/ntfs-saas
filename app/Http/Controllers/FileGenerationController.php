<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\FileJacket;
use App\Models\FileRecord;
use App\Models\Status;
use App\Services\DocumentService;
use App\Services\FileMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FileGenerationController extends Controller
{
    /**
     * Show the form for creating a new file.
     */
    public function create(Request $request)
    {
        if (! Auth::user() || ! Auth::user()->hasAnyRole(['Super Admin', 'Sys Admin', 'Agency Admin', 'Supervisor', 'Officer', 'Clerk', 'Registry Officer'])) {
            abort(403, 'Unauthorized action. Clerks cannot create files.');
        }

        $departments = Department::orderBy('name')->get();
        $userDeptId = Auth::user()->department_id;
        $jackets = FileJacket::where('department_id', $userDeptId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'jacket_code', 'title']);
        $closedFiles = FileRecord::where('current_department_id', $userDeptId)
            ->whereHas('status', function ($query) {
                $query->where('name', 'CLOSED');
            })
            ->orderBy('created_at', 'desc')
            ->get(['id', 'file_reference_number', 'title']);

        $preselectedJacketId = $request->query('file_jacket_id');

        return view('files.create', compact('departments', 'jackets', 'closedFiles', 'preselectedJacketId'));
    }

    /**
     * Store a newly created file in storage.
     */
    public function store(Request $request, DocumentService $documentService)
    {
        if (! Auth::user() || ! Auth::user()->hasAnyRole(['Super Admin', 'Sys Admin', 'Agency Admin', 'Supervisor', 'Officer', 'Clerk', 'Registry Officer'])) {
            abort(403, 'Unauthorized action. Clerks cannot create files.');
        }

        $maxSize = config('digital_module.max_upload_size', 10240);
        $mimes = implode(',', config('digital_module.allowed_mimes', ['pdf', 'jpeg', 'png', 'jpg', 'doc', 'docx']));

        $rules = [
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'priority_level' => 'required|integer|in:1,2,3',
            'confidentiality_level' => 'required|integer|in:1,2,3',
            'file_jacket_id' => 'nullable|exists:file_jackets,id',
            'reference_file_id' => 'nullable|exists:file_records,id',
            'dispatch_department_id' => 'nullable|exists:departments,id',
            'dispatch_user_id' => 'nullable|exists:users,id',
        ];

        if (config('digital_module.enabled')) {
            $rules['digital_document'] = "nullable|file|mimes:{$mimes}|max:{$maxSize}";
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            $status = Status::where('name', 'RECEIVED')->firstOrFail();

            // Generate a unique File Reference Number (simulated logic for now)
            $refNumber = 'NAMA/'.date('Y').'/'.strtoupper(Str::random(6));

            $userDeptId = Auth::user()->department_id;

            $file = FileRecord::create([
                'uuid' => (string) Str::uuid(),
                'file_reference_number' => $refNumber,
                'title' => $validated['title'],
                'originating_department_id' => $userDeptId,
                'current_department_id' => $userDeptId,
                'current_owner_id' => Auth::id(),
                'created_by' => Auth::id(),
                'status_id' => $status->id,
                'priority_level' => $validated['priority_level'],
                'confidentiality_level' => $validated['confidentiality_level'],
                'file_jacket_id' => $validated['file_jacket_id'] ?? null,
                'reference_file_id' => $validated['reference_file_id'] ?? null,
            ]);

            $isDispatching = $request->filled('dispatch_department_id');

            // Create the Genesis movement ledger entry
            $movement = $file->movements()->create([
                'request_uuid' => (string) Str::uuid(),
                'from_user_id' => Auth::id(),
                'to_user_id' => $isDispatching ? ($request->dispatch_user_id ? (int) $request->dispatch_user_id : null) : Auth::id(),
                'from_department_id' => $userDeptId,
                'to_department_id' => $isDispatching ? (int) $request->dispatch_department_id : $userDeptId,
                'movement_type' => $isDispatching ? 'DISPATCH' : 'CREATION',
                'remarks' => $isDispatching ? 'Initial dispatch upon file generation.' : 'File physically generated and logged into the system.',
                'acknowledgment_status' => $isDispatching ? 'PENDING' : 'ACCEPTED',
                'received_at' => $isDispatching ? null : now(),
            ]);

            // Append the digital document optionally inside the Generation transaction
            if (config('digital_module.enabled') && $request->hasFile('digital_document')) {
                $documentService->storeDocument(
                    $request->file('digital_document'),
                    [
                        'file_id' => $file->id,
                        'movement_id' => $movement->id,
                        'document_type' => 'PRIMARY',
                    ],
                    $request->user()
                );
            }

            // STEP 2 — Resolve target status and audit if dispatched
            $successMsg = 'File generated successfully: ' . $refNumber;
            if ($isDispatching) {
                // Update file status to IN_TRANSIT
                $inTransitStatusId = \App\Models\Status::where('name', 'IN_TRANSIT')->firstOrFail()->id;
                $file->status_id = $inTransitStatusId;
                $file->save();

                $deptName = \App\Models\Department::find($request->dispatch_department_id)->name ?? 'Unknown';
                $successMsg .= ' — Dispatched to ' . $deptName;
                
                $auditDetail = 'Sent to ' . $deptName . ' Department Inbox';
                if ($request->dispatch_user_id) {
                    $userName = \App\Models\User::find($request->dispatch_user_id)->name ?? 'Unknown';
                    $successMsg .= ', assigned to ' . $userName;
                    $auditDetail = 'Sent to ' . $userName . ' (' . $deptName . ')';
                }

                \Illuminate\Support\Facades\DB::table('audit_logs')->insert([
                    'agency_id' => Auth::user()->agency_id ?? 1, // Fallback to 1 if not set
                    'action_type' => 'DISPATCH',
                    'entity_type' => 'file_movements',
                    'entity_id' => $movement->id,
                    'old_values' => json_encode(['status_id' => $status->id]),
                    'new_values' => json_encode(['status_id' => $inTransitStatusId, 'to_user_id' => $request->dispatch_user_id, 'detail' => $auditDetail]),
                    'user_id' => Auth::id(),
                    'ip_address' => request()->ip(),
                    'created_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('files.show', $file->uuid)->with('success', $successMsg);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('File Generation Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->withInput()->withErrors(['error' => 'Failed to generate file record: '.$e->getMessage()]);
        }
    }
}
