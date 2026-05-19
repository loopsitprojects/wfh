@extends('layouts.app')
@section('title','Pulse Requests')
@section('page-title','Pulse Requests')

@section('content')
<div class="card" style="margin-bottom:20px">
  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
    <div class="form-group" style="margin:0;flex:1;min-width:140px">
      <label>Status</label>
      <select name="status" class="form-control">
        <option value="">All</option>
        <option value="pending"  {{ request('status')=='pending'  ? 'selected':'' }}>Pending</option>
        <option value="approved" {{ request('status')=='approved' ? 'selected':'' }}>Approved</option>
        <option value="rejected" {{ request('status')=='rejected' ? 'selected':'' }}>Rejected</option>
      </select>
    </div>
    <div class="form-group" style="margin:0;flex:1;min-width:140px">
      <label>Date</label>
      <input type="date" name="date" class="form-control" value="{{ request('date') }}">
    </div>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('manager.pulses') }}" class="btn btn-outline">Reset</a>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Photo</th><th>Employee</th><th>Description</th><th>Status</th><th>Submitted</th><th>Approver</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($pulses as $pulse)
        <tr>
          <td><img src="{{ asset($pulse->image_path) }}" class="pulse-thumb" alt=""
                   onclick="document.getElementById('img-{{ $pulse->id }}').classList.add('open')"></td>
          <td style="font-weight:500">{{ $pulse->employee->name }}</td>
          <td style="color:var(--muted);max-width:200px">{{ Str::limit($pulse->description,60,'…') ?? '—' }}</td>
          <td><span class="badge {{ $pulse->statusBadgeClass() }}">{{ ucfirst($pulse->status) }}</span></td>
          <td style="color:var(--muted)">{{ $pulse->created_at->format('M d, Y h:i A') }}</td>
          <td><span style="font-weight:500">{{ $pulse->approver->name ?? '—' }}</span></td>
          <td>
            @if($pulse->isPending())
              <div style="display:flex;align-items:center;gap:12px">
                <form method="POST" action="{{ route('manager.pulses.approve',$pulse->id) }}" style="display:flex;gap:8px;align-items:center" onsubmit="this.querySelector('button').classList.add('loading')">
                  @csrf
                  <div style="display:flex;align-items:center;gap:6px">
                    <div style="display:flex;align-items:center;gap:3px">
                      <input type="number" name="hours" min="0" max="24" value="1" class="form-control" style="width:50px;padding:6px;font-size:12px;font-weight:600;text-align:center">
                      <span style="font-size:11px;color:var(--text);font-weight:500">h</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:3px">
                      <input type="number" name="minutes" min="0" max="59" value="0" class="form-control" style="width:50px;padding:6px;font-size:12px;font-weight:600;text-align:center">
                      <span style="font-size:11px;color:var(--text);font-weight:500">m</span>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-success btn-sm" style="padding:6px 12px;font-weight:600">Approve</button>
                </form>
                <button class="btn btn-danger btn-sm" style="padding:6px 12px;font-weight:600" onclick="this.classList.add('loading'); document.getElementById('r-{{ $pulse->id }}').classList.add('open'); this.classList.remove('loading')">Reject</button>
              </div>
            @elseif($pulse->isRejected() && $pulse->rejection_reason)
              <span style="font-size:12px;color:var(--muted)" title="{{ $pulse->rejection_reason }}">Reason: {{ Str::limit($pulse->rejection_reason,30) }}</span>
            @else
              <span style="color:var(--muted)">—</span>
            @endif
          </td>

        </tr>        {{-- Image Modal --}}
        <div class="modal-overlay" id="img-{{ $pulse->id }}" onclick="this.classList.remove('open')">
          <img src="{{ asset($pulse->image_path) }}" style="max-width:90vw;max-height:90vh;border-radius:12px" onclick="event.stopPropagation()">
        </div>

        {{-- Reject Modal --}}
        @if($pulse->isPending())
        <div class="modal-overlay" id="r-{{ $pulse->id }}">
          <div class="modal">
            <div class="modal-header">
              <span class="modal-title">Reject Pulse — {{ $pulse->employee->name }}</span>
              <button class="modal-close" onclick="document.getElementById('r-{{ $pulse->id }}').classList.remove('open')">×</button>
            </div>
            <form method="POST" action="{{ route('manager.pulses.reject',$pulse->id) }}">
              @csrf
              <div class="form-group"><textarea name="reason" class="form-control" placeholder="Optional reason…"></textarea></div>
              <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('r-{{ $pulse->id }}').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject</button>
              </div>
            </form>
          </div>
        </div>
        @endif
        @empty
          <tr><td colspan="6"><div class="empty-state"><div class="empty-state-icon">📭</div><h3>No pulse requests found</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $pulses->links('vendor.pagination.custom') }}
</div>
@endsection
