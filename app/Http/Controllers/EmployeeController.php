<?php

namespace App\Http\Controllers;

use App\Models\Pulse;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function dashboard()
    {
        $user        = auth()->user();
        $activePulse = $user->getActivePulse();
        $activeTimer = $user->getActiveTimer();
        $todaySec    = $user->getTodaySeconds();
        $weekSec     = $user->getWeekSeconds();

        $recentLogs = $user->timeLogs()
            ->with('pulse')
            ->whereNotNull('ended_at')
            ->latest('started_at')
            ->take(5)
            ->get();

        $pendingPulse = $user->pulses()->where('status', 'pending')->latest()->first();
        $spentSec     = $activePulse ? $activePulse->getSpentSeconds() : 0;

        return view('employee.dashboard', compact(
            'user', 'activePulse', 'activeTimer',
            'todaySec', 'weekSec', 'recentLogs', 'pendingPulse', 'spentSec'
        ));

    }

    public function history(Request $request)
    {
        $user  = auth()->user();
        $query = $user->timeLogs()->with('pulse')->latest('started_at');

        if ($request->date_from) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        $logs      = $query->paginate(15)->withQueryString();
        $totalSec  = $user->timeLogs()->whereNotNull('ended_at')->sum('duration_seconds');

        return view('employee.history', compact('logs', 'totalSec'));
    }
}
