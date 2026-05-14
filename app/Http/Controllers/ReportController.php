<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TimeLog;
use App\Models\Pulse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        $user      = auth()->user();
        $employees = $user->isAdmin()
            ? User::where('role', 'employee')->get()
            : $user->employees()->get();

        return view('manager.reports', compact('employees'));
    }

    public function generate(Request $request)
    {
        $data = $this->buildReportData($request);
        return response()->json($data);
    }

    public function exportCsv(Request $request)
    {
        $data = $this->buildReportData($request);

        $filename = 'report_' . now()->format('Y-m-d') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee', 'Date', 'Hours Worked', 'Pulses', 'Sessions']);
            foreach ($data['rows'] as $row) {
                fputcsv($file, [
                    $row['name'],
                    $row['date'],
                    number_format($row['hours'], 2),
                    $row['pulses'],
                    $row['sessions'],
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $data     = $this->buildReportData($request);
        $pdf      = Pdf::loadView('manager.report_pdf', $data)->setPaper('a4', 'landscape');
        $filename = 'report_' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    private function buildReportData(Request $request): array
    {
        $user      = auth()->user();
        $dateFrom  = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo    = $request->date_to   ?? now()->toDateString();
        $empFilter = $request->employee_id;

        $empQuery = $user->isAdmin()
            ? User::where('role', 'employee')
            : $user->employees();

        if ($empFilter) {
            $empQuery->where('id', $empFilter);
        }

        $employees = $empQuery->get();
        $rows      = [];
        $details   = [];
        $summary   = ['total_hours' => 0, 'total_sessions' => 0, 'total_pulses' => 0];

        foreach ($employees as $emp) {
            $logs = $emp->timeLogs()
                ->whereBetween('started_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->whereNotNull('ended_at')
                ->with('pulse')
                ->get();

            $pulseCount = $emp->pulses()
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count();

            $totalSec = $logs->sum('duration_seconds');
            $hours    = round($totalSec / 3600, 2);

            $rows[] = [
                'name'     => $emp->name,
                'email'    => $emp->email,
                'date'     => "$dateFrom to $dateTo",
                'hours'    => $hours,
                'pulses'   => $pulseCount,
                'sessions' => $logs->count(),
            ];

            foreach ($logs as $log) {
                $details[] = [
                    'employee' => $emp->name,
                    'date'     => $log->started_at->format('Y-m-d'),
                    'start'    => $log->started_at->format('h:i A'),
                    'end'      => $log->ended_at->format('h:i A'),
                    'duration' => $log->getDurationFormatted(),
                    'pulse'    => $log->pulse->description ?? 'No description',
                ];
            }

            $summary['total_hours']    += $hours;
            $summary['total_sessions'] += $logs->count();
            $summary['total_pulses']   += $pulseCount;
        }

        return compact('rows', 'details', 'summary', 'dateFrom', 'dateTo');
    }
}
