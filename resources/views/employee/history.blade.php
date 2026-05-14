@extends('layouts.app')
@section('title','My History')
@section('page-title','My Work History')

@section('content')
<div class="card" style="margin-bottom:20px">
  <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
    <div class="form-group" style="margin:0;flex:1;min-width:150px">
      <label>From</label>
      <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
    </div>
    <div class="form-group" style="margin:0;flex:1;min-width:150px">
      <label>To</label>
      <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
    </div>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('employee.history') }}" class="btn btn-outline">Reset</a>
  </form>
</div>

<div class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Session History</div>
      <div class="card-subtitle">Total: {{ gmdate('H\h i\m', $totalSec) }}</div>
    </div>
  </div>

  @if($logs->count())
  <div class="table-wrap">
    <table>
      <thead><tr><th>Date</th><th>Started</th><th>Ended</th><th>Duration</th><th>Notes</th></tr></thead>
      <tbody>
        @foreach($logs as $log)
        <tr>
          <td>{{ $log->started_at->format('M d, Y') }}</td>
          <td>{{ $log->started_at->format('h:i A') }}</td>
          <td>{{ $log->ended_at?->format('h:i A') ?? '<span class="badge badge-warning">Running</span>' }}</td>
          <td>
            @if($log->ended_at)
              <span class="badge badge-success">{{ $log->getDurationFormatted() }}</span>
            @else
              <span class="badge badge-warning">In progress</span>
            @endif
          </td>
          <td style="color:var(--muted)">{{ $log->notes ?? '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  {{ $logs->links('vendor.pagination.custom') }}
  @else
    <div class="empty-state"><div class="empty-state-icon">📭</div><h3>No sessions found</h3><p>Your completed work sessions will appear here.</p></div>
  @endif
</div>
@endsection
