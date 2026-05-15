<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TimeLog;
use App\Models\Pulse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildReportData($request);
        $employees = User::where('role', 'employee')->get();
        return view('manager.reports', array_merge($data, ['employees' => $employees]));
    }

    public function generate(Request $request)
    {
        $data = $this->buildReportData($request);
        return response()->json($data);
    }

    public function exportCsv(Request $request)
    {
        $data = $this->buildReportData($request);
        $type = $request->type ?? 'summary';
        $filename = 'wfh_report_' . $type . '_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data, $type) {
            $file = fopen('php://output', 'w');
            if ($type === 'summary') {
                fputcsv($file, ['Employee', 'Email', 'Total Hours', 'Total Sessions', 'Total Pulses']);
                foreach ($data['rows'] as $row) {
                    fputcsv($file, [$row['name'], $row['email'], $row['hours'], $row['sessions'], $row['pulses']]);
                }
            } else {
                fputcsv($file, ['Employee', 'Email', 'Date', 'Start Time', 'End Time', 'Duration', 'Pulse Description', 'Approver']);
                foreach ($data['details'] as $d) {
                    fputcsv($file, [$d['employee'], $d['email'], $d['date'], $d['start'], $d['end'], $d['duration'], $d['pulse'], $d['approver']]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->buildReportData($request);
        $type = $request->type ?? 'summary';
        $data['type'] = $type;

        $view = ($type === 'summary') ? 'manager.report_summary_pdf' : 'manager.report_detailed_pdf';
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'landscape');
        
        $filename = 'wfh_report_' . $type . '_' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    private function buildReportData(Request $request): array
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo   = $request->date_to   ?? now()->toDateString();
        $search   = $request->search;

        $empQuery = User::where('role', 'employee');
        if ($search) {
            $empQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")->orWhere('email', 'like', "%$search%");
            });
        }

        $employees = $empQuery->get();
        $rows      = [];
        $details   = [];
        $summary   = ['total_hours' => 0, 'total_sessions' => 0, 'total_pulses' => 0];

        foreach ($employees as $emp) {
            $logs = $emp->timeLogs()
                ->whereBetween('started_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->whereNotNull('ended_at')
                ->with(['pulse.approver'])
                ->get();

            $pulseCount = $emp->pulses()
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count();

            $totalSec = $logs->sum('duration_seconds');
            $hours    = round($totalSec / 3600, 2);

            $rows[] = [
                'name'     => $emp->name,
                'email'    => $emp->email,
                'hours'    => $hours,
                'pulses'   => $pulseCount,
                'sessions' => $logs->count(),
            ];

            foreach ($logs as $log) {
                $details[] = [
                    'employee' => $emp->name,
                    'email'    => $emp->email,
                    'date'     => $log->started_at->format('M d, Y'),
                    'start'    => $log->started_at->format('h:i A'),
                    'end'      => $log->ended_at->format('h:i A'),
                    'duration' => $log->getDurationFormatted(),
                    'pulse'    => $log->pulse->description ?? 'No description',
                    'image'    => $log->pulse->image_path ?? null,
                    'approver' => $log->pulse->approver->name ?? '—',
                ];
            }

            $summary['total_hours']    += $hours;
            $summary['total_sessions'] += $logs->count();
            $summary['total_pulses']   += $pulseCount;
        }

        // Sort details by date latest first
        usort($details, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return compact('rows', 'details', 'summary', 'dateFrom', 'dateTo');
    }
}
