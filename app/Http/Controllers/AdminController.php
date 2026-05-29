<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pulse;
use App\Models\TimeLog;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers     = User::count();
        $totalEmployees = User::where('role', 'employee')->count();
        $totalManagers  = User::where('role', 'manager')->count();
        $activeToday    = TimeLog::whereDate('started_at', today())->distinct('employee_id')->count();
        $pendingPulses  = Pulse::where('status', 'pending')->count();

        $recentUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalEmployees', 'totalManagers',
            'activeToday', 'pendingPulses', 'recentUsers'
        ));
    }

    public function users(Request $request)
    {
        $query = User::with('manager')->latest();

        if ($request->role) {
            $query->where('role', $request->role);
        }
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('employee_id', 'like', '%' . $request->search . '%');
            });
        }

        $users    = $query->paginate(20)->withQueryString();
        $managers = User::where('role', 'manager')->orderBy('name')->get();
        return view('admin.users.index', compact('users', 'managers'));
    }

    public function createUser()
    {
        $managers = User::where('role', 'manager')->orderBy('name')->get();
        return view('admin.users.create', compact('managers'));
    }

    public function storeUser(Request $request)
    {
        $rules = [
            'username'    => ['required', 'string', 'max:50', 'unique:users'],
            'email'       => ['required', 'email', 'unique:users'],
            'password'    => ['required', 'string', 'min:8'],
            'role'        => ['required', 'in:admin,manager,employee'],
            'manager_id'  => ['nullable', 'exists:users,id'],
        ];

        $data = $request->validate($rules);
        $data['name'] = $data['username']; // Use username as display name
        $data['password'] = bcrypt($data['password']);

        User::create($data);

        return redirect()->route('admin.users')->with('success', 'User created successfully.');
    }

    public function storeEmployee(Request $request)
    {
        $data = $request->validate([
            'username'    => ['required', 'string', 'max:50', 'unique:users'],
            'email'       => ['required', 'email', 'unique:users'],
            'password'    => ['required', 'string', 'min:8'],
            'role'        => ['required', 'in:employee,manager'],
        ]);

        $data['name']       = $data['username'];
        $data['password']   = bcrypt($data['password']);
        
        // If it's a manager being created, they don't necessarily need a manager_id
        // But if we want to keep them under the current manager, we can.
        // Usually managers are independent.
        $data['manager_id'] = ($data['role'] === 'manager') ? null : auth()->id();

        User::create($data);

        return redirect()->route('manager.team')->with('success', ucfirst($data['role']) . ' added successfully.');
    }


    public function editUser(User $user)
    {
        $managers = User::where('role', 'manager')->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'managers'));
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'username'    => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id],
            'email'       => ['required', 'email', 'unique:users,email,' . $user->id],
            'role'        => ['required', 'in:admin,manager,employee'],
            'manager_id'  => ['nullable', 'exists:users,id'],
            'password'    => ['nullable', 'string', 'min:8'],
        ]);

        $data['name'] = $data['username'];
        if (empty($data['password'])) { unset($data['password']); }
        else { $data['password'] = bcrypt($data['password']); }

        $user->update($data);
        $route = auth()->user()->isAdmin() ? 'admin.users' : 'manager.team';
        return redirect()->route($route)->with('success', 'User updated successfully.');
    }

    public function toggleUser(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$status} successfully.");
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    public function activityLogs(Request $request)
    {
        $query = TimeLog::with(['employee', 'pulse'])->latest();

        if ($request->search) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }

        $logs = $query->paginate(30)->withQueryString();
        return view('admin.activity', compact('logs'));
    }

    public function clearActivityLogs()
    {
        TimeLog::query()->delete();
        return back()->with('success', 'All activity history has been cleared.');
    }

    public function exportActivityCsv(Request $request)
    {
        $query = TimeLog::with(['employee'])->latest();
        if ($request->search) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }
        $logs = $query->get();

        $filename = "activity_history_" . now()->format('Y-m-d') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee', 'Username', 'Date', 'Start', 'End', 'Duration (Sec)', 'Allocated (H)']);
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->employee->name,
                    $log->employee->username,
                    $log->started_at->format('Y-m-d'),
                    $log->started_at->format('H:i:s'),
                    $log->ended_at ? $log->ended_at->format('H:i:s') : 'Running',
                    $log->duration_seconds ?? 0,
                    $log->allocated_hours ?? 0,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportActivityPdf(Request $request)
    {
        $query = TimeLog::with(['employee'])->latest();
        if ($request->search) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }
        $logs = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.activity_pdf', compact('logs'));
        return $pdf->download("activity_report_" . now()->format('Y-m-d') . ".pdf");
    }
}
