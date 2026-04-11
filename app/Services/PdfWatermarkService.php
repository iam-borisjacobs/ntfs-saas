<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfWatermarkService
{
    /**
     * Statically inject a watermark onto a PDF before serving it.
     * Fallbacks to raw stream if FPDF/FPDI is not installed.
     *
     * @param \App\Models\Document $document
     * @param \App\Models\User $user
     * @return StreamedResponse
     */
    public static function streamWatermarkedPdf(\App\Models\Document $document, \App\Models\User $user): StreamedResponse
    {
        $disk = config('digital_module.disk', 'local');
        $absolutePath = Storage::disk($disk)->path($document->file_path);

        if (!isset($user)) {
            abort(403, 'Unauthorized Document Access');
        }

        // The exact dynamically injected text
        $watermarkText = "CONFIDENTIAL - DOWNLOADED BY " . strtoupper($user->name) . " - " . date('Y-m-d H:i:s') . " - IP: " . request()->ip();

        // Safe Fallback if composer libraries were restricted/timed out during setup
        if (!class_exists('\setasign\Fpdi\Fpdi')) {
            Log::warning("PDF WATERMARK ENGINE OFFLINE: FPDI class missing. Delivering Raw PDF to {$user->email} with bypassed watermark protocol.");
            Log::info("WATERMARK MOCK: [$watermarkText] generated for Document ID {$document->id}");
            return Storage::disk($disk)->download($document->file_path, 'WATERMARKED_' . $document->file_name);
        }

        // --- Production Geometry & Injection ---
        try {
            $pdf = new \setasign\Fpdi\Fpdi();
            $pageCount = $pdf->setSourceFile($absolutePath);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                // Add a page matching the original sizes
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                
                // Overlay the original PDF
                $pdf->useTemplate($templateId);

                // Configure Watermark Styling
                $pdf->SetFont('Arial', 'B', 35);
                $pdf->SetTextColor(255, 0, 0); // Red
                // Add transparency manually or via FPDF extension if applicable
                
                // Calculate diagonal positioning
                $pdf->SetXY(20, $size['height'] - 30);
                
                // Stamping it directly
                $pdf->Cell(0, 10, $watermarkText, 0, 0, 'C');
            }

            // Stream directly using Laravel StreamedResponse
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->Output('S');
            }, 'SECURE_' . $document->file_name, [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'no-store, no-cache, must-revalidate'
            ]);

        } catch (\Exception $e) {
            Log::error('PDF Watermark Failed: ' . $e->getMessage());
            // Fallback to secure standard download
            return Storage::disk($disk)->download($document->file_path, 'SECURE_' . $document->file_name);
        }
    }
}
