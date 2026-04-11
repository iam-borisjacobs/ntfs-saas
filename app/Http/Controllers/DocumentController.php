<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\FileRecord;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Exception;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    protected $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Store a newly created attachment against a physical file.
     */
    public function store(Request $request, FileRecord $file)
    {
        if (!config('digital_module.enabled', true)) {
            abort(404, 'Digital Module Disabled.');
        }

        $request->validate([
            'document' => [
                'required',
                'file',
                'max:' . config('digital_module.max_upload_size_kb', 10240),
                'mimes:' . implode(',', config('digital_module.allowed_mimes', ['pdf', 'jpeg', 'png', 'docx'])),
            ],
            'document_type' => 'required|string|in:Memo,Official Letter,Approval,Attachment,Other',
        ]);

        // Construct metadata payload connecting the digital file to the physical world
        $metadata = [
            'file_id' => $file->id,
            'movement_id' => null, // Optional tracking if we want to bind to a specific dispatch later
            'document_type' => $request->document_type,
        ];

        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            $this->documentService->storeDocument(
                $request->file('document'),
                $metadata,
                $user
            );

            return back()->with('status', 'Document securely attached to file record.');
        } catch (Exception $e) {
            return back()->withErrors(['document' => 'Upload failed securely. ' . $e->getMessage()]);
        }
    }

    /**
     * Store a new version of an existing attachment.
     */
    public function updateVersion(Request $request, Document $document)
    {
        if (!config('digital_module.enabled', true)) {
            abort(404);
        }

        $this->authorize('update', $document);

        $request->validate([
            'document' => [
                'required',
                'file',
                'max:' . config('digital_module.max_upload_size_kb', 10240),
                'mimes:' . implode(',', config('digital_module.allowed_mimes', ['pdf', 'jpeg', 'png', 'docx'])),
            ],
        ]);

        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $this->documentService->storeDocument(
                $request->file('document'),
                [], // Metadata inherits from existing
                $user,
                $document
            );

            return back()->with('status', 'New version stored. Previous version archived immutably.');
        } catch (Exception $e) {
            return back()->withErrors(['document' => 'Version update failed. ' . $e->getMessage()]);
        }
    }

    /**
     * Handle strictly authorized, performance-optimized streaming downloads.
     */
    public function download(Document $document)
    {
        if (!config('digital_module.enabled', true)) {
            abort(404);
        }

        $this->authorize('view', $document);

        // 1. Log the distinct download event for audit trails
        \Illuminate\Support\Facades\DB::table('download_logs')->insert([
            'document_id' => $document->id,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'downloaded_at' => now(),
        ]);

        $disk = config('digital_module.disk', 'local');
        
        if (!Storage::disk($disk)->exists($document->file_path)) {
            abort(404, 'File missing from secure storage layer.');
        }

        // 2. Cryptographic PDF Watermark Injection
        if (strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION)) === 'pdf' || str_ends_with($document->file_path, '.pdf')) {
            return \App\Services\PdfWatermarkService::streamWatermarkedPdf($document, Auth::user());
        }

        // 3. Stream execution saves server RAM on non-PDF architectures
        return Storage::disk($disk)->download($document->file_path, 'SECURE_' . $document->file_name);
    }
}
