@extends('layouts.app')
@section('title','Work Reports')
@section('page-title','Team Work Reports')

@section('content')
<div class="card" style="margin-bottom:24px">
  <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:20px">
    {{-- Filters --}}
    <form method="GET" style="display:flex;gap:12px;flex:1;min-width:300px;align-items:flex-end">
      <div class="form-group" style="margin:0;flex:1">
        <label style="font-size:12px;font-weight:600;color:var(--muted)">Search Employee</label>
        <input type="text" name="search" class="form-control" placeholder="Name or email…" value="{{ request('search') }}">
      </div>
      <div class="form-group" style="margin:0;width:140px">
        <label style="font-size:12px;font-weight:600;color:var(--muted)">From</label>
        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
      </div>
      <div class="form-group" style="margin:0;width:140px">
        <label style="font-size:12px;font-weight:600;color:var(--muted)">To</label>
        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
      </div>
      <button type="submit" class="btn btn-primary" style="padding:10px 20px">Update View</button>
    </form>

    {{-- Export Actions --}}
    <div style="display:flex;gap:10px;align-items:center;background:rgba(255,255,255,0.03);padding:15px;border-radius:12px;border:1px solid rgba(255,255,255,0.05)">
      <div style="margin-right:10px">
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:700;margin-bottom:8px">Export Reports</div>
        <div style="display:flex;gap:8px">
          {{-- Summary Column --}}
          <div style="display:flex;flex-direction:column;gap:5px">
            <span style="font-size:10px;color:var(--muted);text-align:center">Summary</span>
            <div style="display:flex;gap:4px">
              <a href="{{ route('manager.reports.pdf', ['type' => 'summary', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => request('search')]) }}" class="btn btn-outline btn-sm" title="Download Summary PDF">📄 PDF</a>
              <a href="{{ route('manager.reports.csv', ['type' => 'summary', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => request('search')]) }}" class="btn btn-outline btn-sm" title="Download Summary CSV">📊 CSV</a>
            </div>
          </div>
          {{-- Detailed Column --}}
          <div style="display:flex;flex-direction:column;gap:5px">
            <span style="font-size:10px;color:var(--muted);text-align:center">Detailed</span>
            <div style="display:flex;gap:4px">
              <a href="{{ route('manager.reports.pdf', ['type' => 'detailed', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => request('search')]) }}" class="btn btn-success btn-sm" style="background:#10b981;border-color:#10b981" title="Download Detailed PDF">📄 PDF</a>
              <a href="{{ route('manager.reports.csv', ['type' => 'detailed', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => request('search')]) }}" class="btn btn-success btn-sm" style="background:#10b981;border-color:#10b981" title="Download Detailed CSV">📊 CSV</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Summary Report --}}
<div class="card" style="margin-bottom:24px">
  <div class="card-header">
    <div class="card-title">Summary Report (Total Hours)</div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employee</th>
          <th>Total Hours</th>
          <th>Total Tasks</th>
          <th>Total Sessions</th>
        </tr>
      </thead>
      <tbody>
      @forelse($rows as $row)
      <tr>
        <td>
          <div style="font-weight:600">{{ $row['name'] }}</div>
          <div style="font-size:11px;color:var(--muted)">{{ $row['email'] }}</div>
        </td>
        <td style="font-weight:600;color:var(--primary)">{{ $row['hours_formatted'] }}</td>
        <td>{{ $row['pulses'] }}</td>
        <td>{{ $row['sessions'] }}</td>
      </tr>
      @empty
        <tr><td colspan="4"><div class="empty-state" style="padding: 20px">No data found</div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Detailed Report --}}
<div class="card">
  <div class="card-header">
    <div class="card-title">Detailed Report</div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employee</th>
          <th>Date</th>
          <th>Session Time</th>
          <th>Duration</th>
          <th>Proof</th>
          <th>Approver</th>
        </tr>
      </thead>
      <tbody>
      @forelse($details as $d)
      <tr>
        <td>
          <div style="font-weight:600">{{ $d['employee'] }}</div>
          <div style="font-size:11px;color:var(--muted)">{{ $d['email'] }}</div>
        </td>
        <td>{{ $d['date'] }}</td>
        <td>
          <div style="font-size:13px">{{ $d['start'] }} - {{ $d['end'] }}</div>
        </td>
        <td>
          <span style="font-weight:600;color:var(--success)">{{ $d['duration'] }}</span>
        </td>
        <td>
          @if($d['image'])
            <a href="{{ asset($d['image']) }}" target="_blank" class="btn btn-outline btn-sm" style="padding:4px 8px;font-size:11px">View Photo</a>
          @else
            <span style="color:var(--muted)">—</span>
          @endif
        </td>
        <td style="font-weight:600;color:var(--primary)">
          {{ $d['approver'] }}
        </td>
      </tr>
      @empty
        <tr><td colspan="6"><div class="empty-state"><h3>No session logs found</h3><p>Try adjusting your date filters.</p></div></td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
