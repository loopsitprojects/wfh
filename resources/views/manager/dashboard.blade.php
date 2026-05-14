@extends('layouts.app')
@section('title','Manager Dashboard')
@section('page-title','Manager Dashboard')

@section('content')
<div class="stats-grid">
  <div class="stat-card warning">
    <div class="stat-icon" style="background:rgba(255,181,71,.12)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFB547" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
    </div>
    <div class="stat-value">{{ $pendingPulses->count() }}</div>
    <div class="stat-label">Pending Pulses</div>
  </div>
  <div class="stat-card info">
    <div class="stat-icon" style="background:rgba(56,189,248,.12)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div class="stat-value">{{ $teamCount }}</div>
    <div class="stat-label">Team Members</div>
  </div>
  <div class="stat-card success">
    <div class="stat-icon" style="background:rgba(16,217,122,.12)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10D97A" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <div class="stat-value">{{ $approvedToday }}</div>
    <div class="stat-label">Approved Today</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(79,126,255,.12)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
    </div>
    <div class="stat-value">{{ $todayPulses }}</div>
    <div class="stat-label">Pulses Today</div>
  </div>
</div>

<div class="grid-2">
  {{-- Pending Requests --}}
  <div class="card" style="grid-column:1/-1">
    <div class="card-header">
      <div><div class="card-title">⚡ Pending Pulse Requests</div><div class="card-subtitle">Respond to unlock employee timers</div></div>
      <a href="{{ route('manager.pulses') }}" class="btn btn-outline btn-sm">View All</a>
    </div>

    @forelse($pendingPulses as $pulse)
    <div style="display:flex;align-items:center;gap:16px;padding:16px;background:var(--card-hover);border-radius:var(--radius-sm);margin-bottom:10px">
      <img src="{{ Storage::url($pulse->image_path) }}" class="pulse-thumb" alt="Work photo"
           onclick="document.getElementById('img-modal-{{ $pulse->id }}').classList.add('open')">
      <div style="flex:1">
        <div style="font-weight:600">{{ $pulse->employee->name }}</div>
        <div style="font-size:12px;color:var(--muted);margin-top:2px">{{ $pulse->description ?? 'No description' }}</div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px">{{ $pulse->created_at->diffForHumans() }}</div>
      </div>
      <div style="display:flex;flex-direction:column;gap:8px">
        <form method="POST" action="{{ route('manager.pulses.approve',$pulse->id) }}" style="display:flex;gap:6px;align-items:center">
          @csrf
          <input type="number" name="duration_hours" step="0.5" min="0.5" max="24" value="1" class="form-control" style="width:70px;padding:6px;font-size:12px" title="Hours">
          <button class="btn btn-success btn-sm">✓ Approve</button>
        </form>
        <button class="btn btn-danger btn-sm" onclick="document.getElementById('reject-modal-{{ $pulse->id }}').classList.add('open')">✕ Reject</button>
      </div>
    </div>

    {{-- Image Modal --}}
    <div class="modal-overlay" id="img-modal-{{ $pulse->id }}" onclick="this.classList.remove('open')">
      <img src="{{ Storage::url($pulse->image_path) }}" style="max-width:90vw;max-height:90vh;border-radius:12px" onclick="event.stopPropagation()">
    </div>

    {{-- Reject Modal --}}
    <div class="modal-overlay" id="reject-modal-{{ $pulse->id }}">
      <div class="modal">
        <div class="modal-header">
          <span class="modal-title">Reject Pulse</span>
          <button class="modal-close" onclick="document.getElementById('reject-modal-{{ $pulse->id }}').classList.remove('open')">×</button>
        </div>
        <p style="color:var(--muted);font-size:13px;margin-bottom:16px">Optionally provide a reason for {{ $pulse->employee->name }}</p>
        <form method="POST" action="{{ route('manager.pulses.reject',$pulse->id) }}">
          @csrf
          <div class="form-group">
            <textarea name="reason" class="form-control" placeholder="Reason (optional)…"></textarea>
          </div>
          <div style="display:flex;gap:10px;justify-content:flex-end">
            <button type="button" class="btn btn-outline" onclick="document.getElementById('reject-modal-{{ $pulse->id }}').classList.remove('open')">Cancel</button>
            <button type="submit" class="btn btn-danger">Reject</button>
          </div>
        </form>
      </div>
    </div>
    @empty
      <div class="empty-state"><div class="empty-state-icon">✅</div><h3>All caught up!</h3><p>No pending pulse requests right now.</p></div>
    @endforelse
  </div>
</div>

{{-- Team Activity --}}
<div class="card" style="margin-top:24px">
  <div class="card-header">
    <div class="card-title">👥 Team Activity Today</div>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Employee</th><th>Today's Hours</th><th>Status</th></tr></thead>
      <tbody>
        @foreach($teamStats as $stat)
        <tr>
          <td style="font-weight:600">
            {{ $stat['name'] }}
          </td>
          <td style="font-weight:500;color:var(--text)">
            @php 
              $total = max(0, $stat['today_sec']);
              $h = floor($total / 3600);
              $m = floor(($total % 3600) / 60);
            @endphp
            {{ $h }}h {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}m
          </td>
          <td>
            @if($stat['is_active'])
              <div style="display:flex;align-items:center;gap:8px">
                <span class="badge badge-success">● Working Now</span>
              </div>
            @else
              <span class="badge badge-muted">Offline</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
