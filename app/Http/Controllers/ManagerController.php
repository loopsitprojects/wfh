<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pulse;
use App\Models\TimeLog;
use App\Models\Project;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        $pendingPulses = Pulse::where('manager_id', $user->id)
            ->where('status', 'pending')
            ->with('employee')
            ->latest()
            ->take(10)
            ->get();

        $teamCount      = $user->employees()->count();
        $todayPulses    = Pulse::where('manager_id', $user->id)->whereDate('created_at', today())->count();
        $approvedToday  = Pulse::where('manager_id', $user->id)->where('status', 'approved')->whereDate('approved_at', today())->count();

        $teamStats = $user->employees()->get()->map(function($e) {
            $todaySec = (int)$e->timeLogs()->whereDate('started_at', today())->whereNotNull('ended_at')->sum('duration_seconds');
            $activeTimer = $e->getActiveTimer();
            if ($activeTimer) {
                $elapsed = now()->timestamp - $activeTimer->started_at->timestamp;
                $todaySec += max(0, $elapsed);
            }
            return [
                'name'       => $e->name,
                'today_sec'  => $todaySec,
                'is_active'  => $activeTimer !== null,
            ];
        });

        return view('manager.dashboard', compact(
            'pendingPulses', 'teamCount', 'todayPulses', 'approvedToday', 'teamStats'
        ));
    }

    public function team(Request $request)
    {
        $user  = auth()->user();
        $query = $user->employees()->with(['timeLogs', 'pulses']);

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $employees = $query->paginate(15)->withQueryString();
        return view('manager.team', compact('employees'));
    }
}
