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
        $preselectedJacketId = $request->query('file_jacket_id');

        return view('files.create', compact('departments', 'jackets', 'preselectedJacketId'));
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
            'file_jacket_id' => 'required|exists:file_jackets,id',
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

            $file = FileRecord::create([
                'uuid' => (string) Str::uuid(),
                'file_reference_number' => $refNumber,
                'title' => $validated['title'],
                'originating_department_id' => $validated['department_id'],
                'current_department_id' => $validated['department_id'],
                'current_owner_id' => Auth::id(),
                'created_by' => Auth::id(),
                'status_id' => $status->id,
                'priority_level' => $validated['priority_level'],
                'confidentiality_level' => $validated['confidentiality_level'],
                'file_jacket_id' => $validated['file_jacket_id'] ?? null,
            ]);

            // Create the Genesis movement ledger entry
            $movement = $file->movements()->create([
                'request_uuid' => (string) Str::uuid(),
                'from_user_id' => Auth::id(),
                'to_user_id' => Auth::id(), // Starts on the creator's desk
                'from_department_id' => $validated['department_id'],
                'to_department_id' => $validated['department_id'],
                'movement_type' => 'CREATION',
                'remarks' => 'File physically generated and logged into the system.',
                'acknowledgment_status' => 'ACCEPTED', // Genesis is auto-acknowledged
                'received_at' => now(),
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

            // STEP 2 — Optional initial dispatch
            $successMsg = 'File generated successfully: ' . $refNumber;
            if ($request->filled('dispatch_department_id')) {
                $movementService = app(FileMovementService::class);
                $movementService->dispatchFile(
                    $file->id,
                    $request->dispatch_user_id ? (int) $request->dispatch_user_id : null,
                    (int) $request->dispatch_department_id,
                    'Initial dispatch upon file generation.',
                    (string) Str::uuid()
                );

                $deptName = Department::find($request->dispatch_department_id)->name ?? 'Unknown';
                $successMsg .= ' — Dispatched to ' . $deptName;
                if ($request->dispatch_user_id) {
                    $userName = \App\Models\User::find($request->dispatch_user_id)->name ?? 'Unknown';
                    $successMsg .= ', assigned to ' . $userName;
                } else {
                    $successMsg .= ' (Department Inbox)';
                }
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
