<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\Pulse;
use Illuminate\Http\Request;

class TimerController extends Controller
{
    public function start(Request $request)
    {
        $user        = auth()->user();
        $activePulse = $user->getActivePulse();
        $activeTimer = $user->getActiveTimer();

        if ($activeTimer) {
            return response()->json(['error' => 'A timer is already running.'], 422);
        }

        if (!$activePulse) {
            return response()->json(['error' => 'No approved pulse available. Request a pulse first.'], 422);
        }

        $log = TimeLog::create([
            'employee_id' => $user->id,
            'pulse_id'    => $activePulse->id,
            'started_at'  => now(),
        ]);

        return response()->json([
            'success'    => true,
            'log_id'     => $log->id,
            'started_at' => $log->started_at->toISOString(),
        ]);
    }

    public function stop(Request $request)
    {
        $user        = auth()->user();
        $activeTimer = $user->getActiveTimer();

        if (!$activeTimer) {
            return response()->json(['error' => 'No active timer found.'], 422);
        }

        $now      = now();
        $duration = (int) $now->diffInSeconds($activeTimer->started_at);

        $activeTimer->update([
            'ended_at'         => $now,
            'duration_seconds' => $duration,
            'notes'            => $request->notes,
        ]);

        return response()->json([
            'success'          => true,
            'duration_seconds' => $duration,
            'formatted'        => $activeTimer->getDurationFormatted(),
        ]);
    }

    public function status()
    {
        $user        = auth()->user();
        $activeTimer = $user->getActiveTimer();
        $activePulse = $user->getActivePulse();

        return response()->json([
            'has_active_timer' => (bool) $activeTimer,
            'has_active_pulse' => (bool) $activePulse,
            'started_at'       => $activeTimer?->started_at?->toISOString(),
            'log_id'           => $activeTimer?->id,
        ]);
    }
}
