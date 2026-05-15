<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\Pulse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use App\Models\User;

class TimerController extends Controller
{
    public function forceStop(User $user)
    {
        $activeTimer = $user->getActiveTimer();
        $activePulse = $user->getActivePulse();

        if ($activeTimer && $activePulse) {
            $now = now();
            $elapsed = abs((int) $now->diffInSeconds($activeTimer->started_at));
            
            // Calculate how much time is actually left in the pulse
            $totalAllocated = (int) ($activePulse->duration_hours * 3600);
            $alreadySpent   = (int) $activePulse->timeLog()->where('id', '!=', $activeTimer->id)->whereNotNull('ended_at')->sum('duration_seconds');
            $remaining      = max(0, $totalAllocated - $alreadySpent);
            
            // Cap the logged duration to the remaining time
            $finalDuration = min($elapsed, $remaining);

            $activeTimer->update([
                'ended_at'         => $now,
                'duration_seconds' => $finalDuration,
                'notes'            => 'Session force-ended by manager: ' . auth()->user()->name,
            ]);
        }

        // 2. Mark any active/approved pulse as completed
        $user->pulses()->where('status', 'approved')->update([
            'status'         => 'completed',
            'is_paused'      => false,
            'stop_requested' => false
        ]);

        return back()->with('success', 'Session formally ended for ' . $user->name);
    }




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

    public function pause(Request $request)
    {
        $user        = auth()->user();
        $activeTimer = $user->getActiveTimer();
        if ($activeTimer) {
            $now = now();
            $duration = abs((int) $now->diffInSeconds($activeTimer->started_at));
            $activeTimer->update([
                'ended_at'         => $now,
                'duration_seconds' => $duration,
                'notes'            => 'Paused by user',
            ]);
            $user->getActivePulse()->update(['is_paused' => true]);
        }
        return response()->json(['success' => true]);
    }

    public function resume(Request $request)
    {
        $user        = auth()->user();
        $activePulse = $user->getActivePulse();
        if ($activePulse && $activePulse->is_paused) {
            TimeLog::create([
                'employee_id'     => $user->id,
                'pulse_id'        => $activePulse->id,
                'allocated_hours' => $activePulse->duration_hours,
                'started_at'      => now(),
            ]);
            $activePulse->update(['is_paused' => false]);
        }
        return response()->json(['success' => true]);
    }

    public function requestStop(Request $request)
    {
        $user        = auth()->user();
        $pulse       = $user->getActivePulse();
        if ($pulse) {
            $pulse->update(['stop_requested' => true]);
            
            // Notify managers
            try {
                $managers = \App\Models\User::whereIn('role', ['manager', 'admin'])->get();
                foreach ($managers as $manager) {
                    $manager->notify(new \App\Notifications\StopRequestedNotification($pulse));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Mail Error (Stop Request): ' . $e->getMessage());
            }
        }
        return response()->json(['success' => true]);
    }

    public function stop(Request $request)
    {
        // Keep for legacy or internal use, but we hide it from employees
        return $this->pause($request); 
    }


    public function status()
    {
        $user        = auth()->user();
        $activeTimer = $user->getActiveTimer();

        // Auto-stop if time is up
        if ($activeTimer && $activeTimer->allocated_hours > 0) {
            $endAt = $activeTimer->started_at->addMinutes($activeTimer->allocated_hours * 60);
            if (now()->greaterThanOrEqualTo($endAt)) {
                $duration = abs((int) $endAt->diffInSeconds($activeTimer->started_at));
                $activeTimer->update([
                    'ended_at'         => $endAt,
                    'duration_seconds' => $duration,
                    'notes'            => 'Timer auto-stopped (Allocated time finished)',
                ]);
                $activeTimer = null; // Mark as stopped for the response
            }
        }

        $activePulse = $user->getActivePulse();

        return response()->json([
            'has_active_timer' => (bool) $activeTimer,
            'has_active_pulse' => (bool) $activePulse,
            'is_paused'        => (bool) ($activePulse->is_paused ?? false),
            'stop_requested'   => (bool) ($activePulse->stop_requested ?? false),
            'started_at'       => $activeTimer?->started_at?->toISOString(),
            'server_time'      => now()->toISOString(),
            'allocated_hours'  => $activeTimer?->allocated_hours,
            'log_id'           => $activeTimer?->id,
        ]);

    }


}
