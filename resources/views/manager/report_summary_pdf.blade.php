<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; margin: 0; padding: 15px; }
  .header { border-bottom: 2px solid #4F7EFF; padding-bottom: 10px; margin-bottom: 20px; }
  h1 { font-size: 18px; margin: 0; color: #4F7EFF; }
  .meta { font-size: 10px; color: #666; margin-top: 5px; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th { background: #4F7EFF; color: #fff; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
  td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
  tr:nth-child(even) td { background: #f9fafb; }
  .footer { margin-top: 30px; font-size: 9px; color: #999; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
  .summary-grid { display: table; width: 100%; margin-bottom: 20px; border-spacing: 10px 0; }
  .summary-box { display: table-cell; background: #f1f5f9; border-left: 3px solid #4F7EFF; padding: 10px; border-radius: 4px; }
  .s-val { font-size: 18px; font-weight: 700; color: #4F7EFF; }
  .s-lbl { font-size: 9px; color: #666; text-transform: uppercase; }
</style>
</head>
<body>
  <div class="header" style="display:flex;align-items:center;justify-content:space-between">
    <img src="{{ public_path('LoopsBlack.png') }}" style="height:35px;width:auto;margin-bottom:10px">
    <h1 style="margin-top:5px">Summary Performance Report</h1>
    <div class="meta">Generated for period: <strong>{{ $dateFrom }}</strong> to <strong>{{ $dateTo }}</strong> | Date: {{ now()->format('M d, Y H:i') }}</div>
  </div>

  <div class="summary-grid">
    <div class="summary-box">
      <div class="s-val">{{ number_format($summary['total_hours'], 1) }}h</div>
      <div class="s-lbl">Total Work Hours</div>
    </div>
    <div class="summary-box">
      <div class="s-val">{{ $summary['total_sessions'] }}</div>
      <div class="s-lbl">Total Sessions</div>
    </div>
    <div class="summary-box">
      <div class="s-val">{{ $summary['total_pulses'] }}</div>
      <div class="s-lbl">Pulse Requests</div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Employee Name</th>
        <th>Email Address</th>
        <th style="text-align:right">Total Hours</th>
        <th style="text-align:right">Sessions</th>
        <th style="text-align:right">Pulses</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $row)
      <tr>
        <td style="font-weight:bold">{{ $row['name'] }}</td>
        <td style="color:#666">{{ $row['email'] }}</td>
        <td style="text-align:right;font-weight:bold">{{ number_format($row['hours'], 2) }}h</td>
        <td style="text-align:right">{{ $row['sessions'] }}</td>
        <td style="text-align:right">{{ $row['pulses'] }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">
    This is an automated system generated report from Loops.
  </div>
</body>
</html>
