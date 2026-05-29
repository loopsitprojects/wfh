<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\Pulse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class TimerController extends Controller
{
    private function getRemainingSeconds(Pulse $pulse, ?TimeLog $excludeTimer = null)
    {
        $query = $pulse->timeLog()->whereNotNull('ended_at');
        if ($excludeTimer) {
            $query->where('id', '!=', $excludeTimer->id);
        }
        $alreadySpent = (int) $query->sum('duration_seconds');
        $totalAllocatedSeconds = (int) round($pulse->duration_hours * 3600);
        return max(0, $totalAllocatedSeconds - $alreadySpent);
    }

    public function forceStop(User $user)
    {
        $activeTimer = $user->getActiveTimer();
        $activePulse = $user->getActivePulse();

        if ($activeTimer && $activePulse) {
            $now = now();
            $remaining = $this->getRemainingSeconds($activePulse, $activeTimer);
            $elapsed = abs((int) $now->diffInSeconds($activeTimer->started_at));
            
            // Cap the logged duration to the remaining time
            $finalDuration = min($elapsed, $remaining);
            $endedAt = $activeTimer->started_at->copy()->addSeconds($finalDuration);

            $activeTimer->update([
                'ended_at'         => $endedAt,
                'duration_seconds' => $finalDuration,
                'notes'            => 'Session force-ended by manager: ' . auth()->user()->name,
            ]);
        }

        if ($activePulse) {
            $activePulse->update([
                'status'         => 'completed',
                'is_paused'      => false,
                'stop_requested' => false
            ]);
        }

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

        $remaining = $this->getRemainingSeconds($activePulse);
        if ($remaining <= 0) {
            $activePulse->update(['status' => 'completed', 'is_paused' => false]);
            return response()->json(['error' => 'Allocated time has already been consumed.'], 422);
        }

        $log = TimeLog::create([
            'employee_id' => $user->id,
            'pulse_id'    => $activePulse->id,
            'allocated_hours' => $activePulse->duration_hours,
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
            $pulse = $activeTimer->pulse;
            $now = now();
            
            $remaining = $this->getRemainingSeconds($pulse, $activeTimer);
            $elapsed = abs((int) $now->diffInSeconds($activeTimer->started_at));
            $finalDuration = min($elapsed, $remaining);
            $endedAt = $activeTimer->started_at->copy()->addSeconds($finalDuration);

            $activeTimer->update([
                'ended_at'         => $endedAt,
                'duration_seconds' => $finalDuration,
                'notes'            => 'Paused by user',
            ]);
            
            if ($finalDuration >= $remaining) {
                $pulse->update(['status' => 'completed', 'is_paused' => false]);
            } else {
                $pulse->update(['is_paused' => true]);
            }
        }
        return response()->json(['success' => true]);
    }

    public function resume(Request $request)
    {
        $user        = auth()->user();
        $activePulse = $user->getActivePulse();
        
        if ($activePulse && $activePulse->is_paused) {
            $remaining = $this->getRemainingSeconds($activePulse);
            if ($remaining <= 0) {
                $activePulse->update(['status' => 'completed', 'is_paused' => false]);
                return response()->json(['error' => 'Allocated time has already been consumed.'], 422);
            }

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
        $user        = auth()->user();
        $activeTimer = $user->getActiveTimer();
        $reason      = $request->input('reason', 'No reason provided');

        if ($activeTimer) {
            $pulse = $activeTimer->pulse;
            $now = now();
            
            $remaining = $this->getRemainingSeconds($pulse, $activeTimer);
            $elapsed = abs((int) $now->diffInSeconds($activeTimer->started_at));
            $finalDuration = min($elapsed, $remaining);
            $endedAt = $activeTimer->started_at->copy()->addSeconds($finalDuration);

            $notes = 'Emergency Stop: ' . $reason;
            if ($reason === 'Auto-stopped (Allocated time finished)') {
                $notes = 'Timer auto-stopped (Allocated time finished)';
            }

            $activeTimer->update([
                'ended_at'         => $endedAt,
                'duration_seconds' => $finalDuration,
                'notes'            => $notes,
            ]);
            
            // Mark pulse as completed so it can't be resumed
            $pulse->update(['status' => 'completed', 'is_paused' => false]);
        }
        return response()->json(['success' => true]);
    }

    public function status()
    {
        $user        = auth()->user();
        $activeTimer = $user->getActiveTimer();

        // Auto-stop if time is up
        if ($activeTimer && $activeTimer->allocated_hours > 0) {
            $pulse = $activeTimer->pulse;
            $remaining = $this->getRemainingSeconds($pulse, $activeTimer);
            $endAt = $activeTimer->started_at->copy()->addSeconds($remaining);
            
            if (now()->greaterThanOrEqualTo($endAt)) {
                $duration = abs((int) $endAt->diffInSeconds($activeTimer->started_at));
                $activeTimer->update([
                    'ended_at'         => $endAt,
                    'duration_seconds' => $duration,
                    'notes'            => 'Timer auto-stopped (Allocated time finished)',
                ]);
                $pulse->update([
                    'status' => 'completed',
                    'is_paused' => false,
                    'stop_requested' => false
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
