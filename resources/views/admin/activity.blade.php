@extends('layouts.app')
@section('title','Activity History')
@section('page-title','System Activity History')

@section('content')
<div class="card" style="margin-bottom:20px">
  <form method="GET" style="display:flex;gap:12px;align-items:flex-end">
    <div class="form-group" style="margin:0;flex:1">
      <label>Search Employee</label>
      <input type="text" name="search" class="form-control" placeholder="Name or username…" value="{{ request('search') }}">
    </div>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('admin.activity') }}" class="btn btn-outline">Reset</a>
    <div style="margin-left:auto;display:flex;gap:8px">
      <a href="{{ route('admin.activity.csv', request()->all()) }}" class="btn btn-outline">Export CSV</a>
      <a href="{{ route('admin.activity.pdf', request()->all()) }}" class="btn btn-outline">Export PDF</a>
    </div>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>User</th>
          <th>Date</th>
          <th>Session</th>
          <th>Duration</th>
          <th>Allocated</th>
          <th>Pulse Proof</th>
        </tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
        <tr>
          <td>
            <div style="font-weight:600">{{ $log->employee->name }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $log->employee->role }}</div>
          </td>
          <td>{{ $log->started_at->format('M d, Y') }}</td>
          <td>
            <div style="font-size:13px">{{ $log->started_at->format('h:i A') }} - {{ $log->ended_at ? $log->ended_at->format('h:i A') : 'Active' }}</div>
          </td>
          <td>
            @if($log->duration_seconds)
              {{ gmdate('H:i:s', $log->duration_seconds) }}
            @else
              <span class="pulse-indicator running" style="margin:0">Running</span>
            @endif
          </td>
          <td style="color:var(--muted)">{{ $log->allocated_hours ? $log->allocated_hours . 'h' : '—' }}</td>
          <td>
            @if($log->pulse && $log->pulse->image_path)
              <a href="{{ asset('storage/' . $log->pulse->image_path) }}" target="_blank" class="btn btn-outline btn-sm" style="padding:4px 8px;font-size:11px">View Image</a>
            @else
              —
            @endif
          </td>
        </tr>
        @empty
          <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No activity logs found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="margin-top:20px">
    {{ $logs->links('vendor.pagination.custom') }}
  </div>
</div>
@endsection
