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
      <thead><tr><th>Photo</th><th>Employee</th><th>Description</th><th>Status</th><th>Submitted</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($pulses as $pulse)
        <tr>
          <td><img src="{{ Storage::url($pulse->image_path) }}" class="pulse-thumb" alt=""
                   onclick="document.getElementById('img-{{ $pulse->id }}').classList.add('open')"></td>
          <td style="font-weight:500">{{ $pulse->employee->name }}</td>
          <td style="color:var(--muted);max-width:200px">{{ Str::limit($pulse->description,60,'…') ?? '—' }}</td>
          <td><span class="badge {{ $pulse->statusBadgeClass() }}">{{ ucfirst($pulse->status) }}</span></td>
          <td style="color:var(--muted)">{{ $pulse->created_at->format('M d, Y h:i A') }}</td>
          <td>
            @if($pulse->isPending())
              <div style="display:flex;flex-direction:column;gap:6px">
                <form method="POST" action="{{ route('manager.pulses.approve',$pulse->id) }}" style="display:flex;gap:4px">
                  @csrf
                  <input type="number" name="duration_hours" step="0.5" min="0.5" max="24" value="1" class="form-control" style="width:60px;padding:4px;font-size:11px">
                  <button class="btn btn-success btn-sm">Approve</button>
                </form>
                <button class="btn btn-danger btn-sm" onclick="document.getElementById('r-{{ $pulse->id }}').classList.add('open')">Reject</button>
              </div>
            @elseif($pulse->isRejected() && $pulse->rejection_reason)
              <span style="font-size:12px;color:var(--muted)" title="{{ $pulse->rejection_reason }}">Reason: {{ Str::limit($pulse->rejection_reason,30) }}</span>
            @else
              <span style="color:var(--muted)">—</span>
            @endif
          </td>
        </tr>

        {{-- Image Modal --}}
        <div class="modal-overlay" id="img-{{ $pulse->id }}" onclick="this.classList.remove('open')">
          <img src="{{ Storage::url($pulse->image_path) }}" style="max-width:90vw;max-height:90vh;border-radius:12px" onclick="event.stopPropagation()">
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
