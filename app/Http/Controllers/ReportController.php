<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Status;
use App\Models\User;
use App\Services\FileSearchService;
use App\Services\ReportService;
use App\Services\CacheService;

class ReportController extends Controller
{
    private FileSearchService $searchService;
    private ReportService $reportService;

    public function __construct(FileSearchService $searchService, ReportService $reportService)
    {
        $this->searchService = $searchService;
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $filters = $request->except(['page', 'q']);
        $term = $request->input('q');

        if (!empty($term)) {
            $files = $this->searchService->executeQuickSearch($term);
        } else {
            $files = $this->searchService->executeAdvancedFilters($filters);
        }

        $cache = new CacheService();
        $departments = $cache->getDepartments();
        $statuses = $cache->getStatuses();
        
        $users = User::orderBy('name')->get(); // Keep direct query for users

        return view('reports.index', compact('files', 'departments', 'statuses', 'users', 'filters', 'term'));
    }

    public function export(Request $request)
    {
        $format = $request->query('format', 'csv');
        $filters = $request->except(['export', 'format', 'page', 'q']);

        if ($format === 'pdf') {
            $pdf = $this->reportService->exportToPdf($filters);
            return $pdf->download('NAMA_Report_' . time() . '.pdf');
        }

        // CSV Download Stream
        $tempPath = $this->reportService->exportToCsv($filters);

        return response()->download($tempPath, 'NAMA_Report_' . time() . '.csv')->deleteFileAfterSend(true);
    }
}
