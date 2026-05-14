<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a2e; margin: 0; padding: 20px; }
  h1 { font-size: 20px; margin-bottom: 4px; color: #4F7EFF; }
  .meta { font-size: 11px; color: #666; margin-bottom: 20px; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #4F7EFF; color: #fff; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; }
  td { padding: 9px 12px; border-bottom: 1px solid #e5e7eb; }
  tr:nth-child(even) td { background: #f9fafb; }
  .summary { display: flex; gap: 20px; margin-bottom: 20px; }
  .s-box { background: #f1f5f9; border-left: 4px solid #4F7EFF; padding: 10px 16px; flex: 1; border-radius: 4px; }
  .s-val { font-size: 22px; font-weight: 700; color: #4F7EFF; }
  .s-lbl { font-size: 11px; color: #666; }
  .footer { margin-top: 24px; font-size: 10px; color: #999; text-align: center; }
</style>
</head>
<body>
  <h1>WFH Pulse — Performance Report</h1>
  <div class="meta">Period: {{ $dateFrom }} to {{ $dateTo }} &nbsp;|&nbsp; Generated: {{ now()->format('M d, Y H:i') }}</div>

  <div class="summary">
    <div class="s-box"><div class="s-val">{{ number_format($summary['total_hours'],1) }}h</div><div class="s-lbl">Total Hours</div></div>
    <div class="s-box"><div class="s-val">{{ $summary['total_sessions'] }}</div><div class="s-lbl">Sessions</div></div>
    <div class="s-box"><div class="s-val">{{ $summary['total_pulses'] }}</div><div class="s-lbl">Pulses</div></div>
  </div>

  <table>
    <thead><tr><th>Employee</th><th>Email</th><th>Hours</th><th>Sessions</th><th>Pulses</th></tr></thead>
    <tbody>
      @foreach($rows as $row)
      <tr>
        <td>{{ $row['name'] }}</td>
        <td>{{ $row['email'] }}</td>
        <td>{{ number_format($row['hours'],2) }}h</td>
        <td>{{ $row['sessions'] }}</td>
        <td>{{ $row['pulses'] }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <div class="footer">WFH Pulse Tracker — Confidential Report</div>
</body>
</html>
