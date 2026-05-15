<?php

namespace App\Http\Controllers;

use App\Models\Pulse;
use App\Models\User;
use App\Notifications\PulseRequestedNotification;
use App\Notifications\PulseDecisionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PulseController extends Controller
{
    /** Employee: show pulse request form */
    public function create()
    {
        $user = auth()->user();
        $hasPending = $user->pulses()->where('status', 'pending')->exists();
        return view('employee.pulse.create', compact('user', 'hasPending'));
    }

    /** Employee: submit pulse request */
    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'image'       => ['required', 'image', 'max:5120'],
            'description' => ['required', 'string', 'max:500'],
        ]);

        $path = $request->file('image')->store('uploads/pulses', 'public');

        $pulse = Pulse::create([
            'employee_id' => $user->id,
            'manager_id'  => $user->manager_id,
            'image_path'  => $path,
            'description' => $request->description,
            'status'      => 'pending',
        ]);



        // Notify all managers
        try {
            $managers = User::whereIn('role', ['manager', 'admin'])->get();
            foreach ($managers as $manager) {
                $manager->notify(new PulseRequestedNotification($pulse));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mail Error: ' . $e->getMessage());
        }



        return redirect()->route('employee.dashboard')
            ->with('success', 'Pulse request sent! Waiting for manager approval.');
    }

    /** Manager: list all pulses for their team */
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Pulse::with(['employee', 'approver'])
            ->latest();


        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }

        $pulses = $query->paginate(20)->withQueryString();
        return view('manager.pulses', compact('pulses'));
    }

    /** Manager: approve a pulse and start employee timer */
    public function approve(Request $request, Pulse $pulse)
    {
        $this->authorizeManager($pulse);

        if ($pulse->status !== 'pending') {
            return back()->with('error', 'This pulse has already been processed.');
        }


        $request->validate([
            'hours'   => ['required', 'integer', 'min:0', 'max:24'],
            'minutes' => ['required', 'integer', 'min:0', 'max:59'],
        ]);

        $durationHours = $request->hours + ($request->minutes / 60);
        
        if ($durationHours <= 0) {
            return back()->with('error', 'Duration must be greater than 0.');
        }

        $pulse->update([
            'status'         => 'approved',
            'duration_hours' => $durationHours,
            'approved_at'    => now(),
            'approved_by'    => auth()->id(),
        ]);


        // Auto-start the timer (create TimeLog)
        \App\Models\TimeLog::create([
            'employee_id'     => $pulse->employee_id,
            'pulse_id'        => $pulse->id,
            'allocated_hours' => $durationHours,
            'started_at'      => now(),
        ]);


        try {
            $pulse->employee->notify(new PulseDecisionNotification($pulse));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mail Error: ' . $e->getMessage());
        }


        return back()->with('success', 'Pulse approved and timer started for ' . $request->duration_hours . ' hours.');
    }

    /** Manager: reject a pulse */
    public function reject(Request $request, Pulse $pulse)
    {
        $this->authorizeManager($pulse);

        if ($pulse->status !== 'pending') {
            return back()->with('error', 'This pulse has already been processed.');
        }

        $request->validate(['reason' => ['nullable', 'string', 'max:300']]);


        $pulse->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        try {
            $pulse->employee->notify(new PulseDecisionNotification($pulse));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mail Error: ' . $e->getMessage());
        }


        return back()->with('success', 'Pulse rejected.');
    }

    private function authorizeManager(Pulse $pulse): void
    {
        if (!auth()->user()->isManager() && !auth()->user()->isAdmin()) {
            abort(403);
        }
    }

}
