@extends('layouts.app')
@section('title','Reports')
@section('page-title','Reports & Analytics')

@section('content')
<div class="grid-2" style="margin-bottom:24px">
  <div class="card">
    <div class="card-title" style="margin-bottom:16px">Generate Report</div>
    <form id="report-form">
      @csrf
      <div class="form-group">
        <label>Date From</label>
        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}">
      </div>
      <div class="form-group">
        <label>Date To</label>
        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ now()->toDateString() }}">
      </div>
      <div class="form-group">
        <label>Employee</label>
        <select name="employee_id" id="employee_id" class="form-control">
          <option value="">All Employees</option>
          @foreach($employees as $emp)
            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
          @endforeach
        </select>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <button type="button" class="btn btn-primary" onclick="generateReport()">Generate</button>
        <a id="csv-btn" href="#" class="btn btn-outline">⬇ CSV</a>
        <a id="pdf-btn" href="#" class="btn btn-outline">⬇ PDF</a>
      </div>
    </form>
  </div>

  <div class="card" id="summary-card" style="display:none">
    <div class="card-title" style="margin-bottom:16px">Summary</div>
    <div class="stats-grid" style="grid-template-columns:1fr;gap:12px">
      <div class="stat-card success"><div class="stat-value" id="s-hours">—</div><div class="stat-label">Total Hours</div></div>
      <div class="stat-card info"><div class="stat-value" id="s-sessions">—</div><div class="stat-label">Sessions</div></div>
      <div class="stat-card warning"><div class="stat-value" id="s-pulses">—</div><div class="stat-label">Pulses</div></div>
    </div>
  </div>
</div>

<div class="card" id="report-table-wrap" style="display:none">
  <div class="card-title" style="margin-bottom:16px">Report Results</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Employee</th><th>Period</th><th>Hours</th><th>Sessions</th><th>Pulses</th></tr></thead>
      <tbody id="report-tbody"></tbody>
    </table>
  </div>
</div>
<div class="card" id="details-table-wrap" style="display:none;margin-top:24px">
  <div class="card-title" style="margin-bottom:16px">Detailed Session History</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Date</th><th>Employee</th><th>Start</th><th>End</th><th>Duration</th><th>Pulse Description</th></tr></thead>
      <tbody id="details-tbody"></tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script>
function getParams(){
  const d = new FormData(document.getElementById('report-form'));
  return new URLSearchParams({
    date_from: d.get('date_from'),
    date_to:   d.get('date_to'),
    employee_id: d.get('employee_id') || '',
  });
}

function generateReport(){
  const p = getParams();
  const btn = event.target;
  btn.disabled = true; btn.textContent = 'Generating…';

  fetch('{{ route("manager.reports.generate") }}', {
    method:'POST',
    headers:{'X-CSRF-TOKEN':csrfToken,'Content-Type':'application/x-www-form-urlencoded'},
    body: p.toString()
  }).then(r=>r.json()).then(data=>{
    btn.disabled = false; btn.textContent = 'Generate';
    
    document.getElementById('summary-card').style.display='block';
    document.getElementById('report-table-wrap').style.display='block';
    document.getElementById('details-table-wrap').style.display='block';
    
    document.getElementById('s-hours').textContent    = data.summary.total_hours.toFixed(1)+'h';
    document.getElementById('s-sessions').textContent = data.summary.total_sessions;
    document.getElementById('s-pulses').textContent   = data.summary.total_pulses;

    const tbody = document.getElementById('report-tbody');
    tbody.innerHTML = data.rows.map(r=>`
      <tr>
        <td style="font-weight:600">${r.name}</td>
        <td style="color:var(--muted);font-size:12px">${r.date}</td>
        <td><span class="badge badge-success">${r.hours}h</span></td>
        <td>${r.sessions}</td>
        <td>${r.pulses}</td>
      </tr>`).join('');

    const dtbody = document.getElementById('details-tbody');
    dtbody.innerHTML = data.details.map(d=>`
      <tr>
        <td>${d.date}</td>
        <td style="font-weight:500">${d.employee}</td>
        <td>${d.start}</td>
        <td>${d.end}</td>
        <td><span class="badge badge-primary">${d.duration}</span></td>
        <td style="font-size:12px;color:var(--muted)">${d.pulse}</td>
      </tr>`).join('');

    document.getElementById('csv-btn').href = '{{ route("manager.reports.csv") }}?' + p;
    document.getElementById('pdf-btn').href = '{{ route("manager.reports.pdf") }}?' + p;
  }).catch(() => {
    btn.disabled = false; btn.textContent = 'Generate';
    alert('Error generating report.');
  });
}
</script>
@endpush
