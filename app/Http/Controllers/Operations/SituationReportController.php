<?php

namespace App\Http\Controllers\Operations;

use App\Services\Operations\SituationReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class SituationReportController extends OperationsController
{
    public function index(SituationReportService $service)
    {
        return view('operations.reports', ['reports' => $service->visibleReports(Auth::user())] + $this->viewContext());
    }

    public function generate(SituationReportService $service)
    {
        $service->generate(Auth::user());
        return redirect()->back()->with('message', 'Situation report generated successfully.');
    }

    public function downloadLink(int $id, SituationReportService $service)
    {
        $service->visibleReport(Auth::user(), $id);
        return redirect(URL::temporarySignedRoute('Operations.reports.download', now()->addMinutes(15), ['id' => $id]));
    }

    public function download(int $id, SituationReportService $service)
    {
        $report = $service->visibleReport(Auth::user(), $id);
        $snapshot = $report->snapshot;
        $rows = [
            ['Election Situation Report', ''], ['Scope', $report->scope_type.($report->scope_id ? ' #'.$report->scope_id : '')],
            ['Period ending', $report->period_ending->toIso8601String()], ['Checksum', $report->checksum], [],
            ['Metric', 'Value'],
        ];
        foreach ($snapshot['results'] as $key => $value) $rows[] = ['Results: '.str_replace('_', ' ', $key), $value];
        foreach ($snapshot['incidents'] as $key => $value) $rows[] = ['Incidents: '.str_replace('_', ' ', $key), $value];

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) fputcsv($handle, $row);
            fclose($handle);
        }, 'situation-report-'.$report->id.'.csv', ['Content-Type' => 'text/csv']);
    }
}
