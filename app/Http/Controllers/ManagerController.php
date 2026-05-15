<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pulse;
use App\Models\TimeLog;
use App\Models\Project;

use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function reports(Request $request)
    {
        $query = TimeLog::with(['employee', 'pulse.approver'])->latest();

        if ($request->search) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->date_from) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(30)->withQueryString();
        return view('manager.reports', compact('logs'));
    }

    public function dashboard()

    {
        $user = auth()->user();

        // Show ALL pending pulses from ANY employee
        $pendingPulses = Pulse::where('status', 'pending')
            ->with('employee')
            ->latest()
            ->take(10)
            ->get();

        $teamCount      = User::where('role', 'employee')->count();
        $todayPulses    = Pulse::whereDate('created_at', today())->count();
        $approvedToday  = Pulse::where('status', 'approved')->whereDate('approved_at', today())->count();

        // Show stats for all employees
        $teamStats = User::where('role', 'employee')->get()->map(function($e) {
            // Get all pulses approved today
            $pulsesToday = $e->pulses()->whereDate('approved_at', today())->get();
            
            $todaySec = abs((int)$e->timeLogs()->whereDate('started_at', today())->whereNotNull('ended_at')->sum('duration_seconds'));
            $allocatedSec = $pulsesToday->sum('duration_hours') * 3600;
            
            $activeTimer = $e->getActiveTimer();
            if ($activeTimer) {
                $elapsed = max(0, now()->timestamp - $activeTimer->started_at->timestamp);
                $pulse = $activeTimer->pulse;
                if ($pulse) {
                    $spentSoFar = $pulse->timeLog()->where('id', '!=', $activeTimer->id)->whereNotNull('ended_at')->sum('duration_seconds');
                    $remaining = ($pulse->duration_hours * 3600) - $spentSoFar;
                    $todaySec += min($elapsed, $remaining);
                } else {
                    $todaySec += $elapsed;
                }
            }

            return [
                'id'              => $e->id,
                'name'            => $e->name,
                'today_sec'       => $todaySec,
                'allocated_sec'   => $allocatedSec,
                'sessions_count'  => $pulsesToday->count(),
                'is_active'       => $activeTimer !== null,
                'stop_requested'  => (bool) ($e->getActivePulse()->stop_requested ?? false),
            ];
        });






        return view('manager.dashboard', compact(
            'pendingPulses', 'teamCount', 'todayPulses', 'approvedToday', 'teamStats'
        ));
    }


    public function team(Request $request)
    {
        $user  = auth()->user();
        
        // Show all employees and managers (except current user and admins)
        $query = User::where('role', '!=', 'admin')
            ->where('id', '!=', $user->id)
            ->with(['timeLogs', 'pulses']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $employees = $query->paginate(15)->withQueryString();
        return view('manager.team', compact('employees'));
    }

    public function destroyUser(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'You cannot delete an administrator.');
        }
        
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();
        return back()->with('success', 'User deleted from the system.');
    }

}
