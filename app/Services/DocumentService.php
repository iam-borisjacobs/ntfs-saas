<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Exception;

class DocumentService
{
    /**
     * Store a new document or attach a new version to an existing document.
     */
    public function storeDocument(UploadedFile $file, array $metadata, User $user, ?Document $existingDocument = null)
    {
        $disk = config('digital_module.disk', 'local');
        $uploadDir = 'digital_documents/' . date('Y/m');
        
        // Generate secure filename and store
        $extension = $file->getClientOriginalExtension();
        $secureFilename = \Illuminate\Support\Str::uuid() . '.' . $extension;
        
        // Use streaming putFileAs to avoid RAM exhaustion on large files
        $path = $file->storeAs($uploadDir, $secureFilename, $disk);
        
        if (!$path) {
            throw new Exception("Failed to write document to secure storage disk.");
        }

        // Calculate absolute path for hashing
        $absolutePath = Storage::disk($disk)->path($path);
        $hash = hash_file('sha256', $absolutePath);

        DB::beginTransaction();
        try {
            if ($existingDocument) {
                // We are uploading a new version of an existing document
                
                // 1. Archive the current state into the historical log
                DocumentVersion::create([
                    'document_id' => $existingDocument->id,
                    'version_number' => $existingDocument->version_number,
                    'file_path' => $existingDocument->file_path,
                    'uploaded_by' => $existingDocument->uploaded_by,
                ]);

                // 2. Update the parent Document
                $existingDocument->update([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'file_hash' => $hash,
                    'version_number' => $existingDocument->version_number + 1.0,
                    'uploaded_by' => $user->id,
                    // Note: file_id, movement_id, document_type usually do not change on a version update
                ]);

                $document = $existingDocument;
            } else {
                // This is a brand new Document upload
                $document = Document::create([
                    'file_id' => $metadata['file_id'] ?? null,
                    'movement_id' => $metadata['movement_id'] ?? null,
                    'uploaded_by' => $user->id,
                    'document_type' => $metadata['document_type'],
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'file_hash' => $hash,
                    'version_number' => 1.0,
                    'status' => 'ACTIVE',
                ]);
            }

            DB::commit();
            
            return $document;
            
        } catch (Exception $e) {
            DB::rollBack();
            // Clean up the orphaned file on disk so we don't leak storage
            Storage::disk($disk)->delete($path);
            throw $e;
        }
    }
}
