<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; margin: 0; padding: 15px; }
  .header { border-bottom: 2px solid #4F7EFF; padding-bottom: 10px; margin-bottom: 15px; }
  h1 { font-size: 16px; margin: 0; color: #4F7EFF; }
  .meta { font-size: 9px; color: #666; margin-top: 5px; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th { background: #4F7EFF; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; white-space: nowrap; }
  td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
  tr:nth-child(even) td { background: #f9fafb; }
  .footer { margin-top: 30px; font-size: 8px; color: #999; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
  .emp-info { font-weight: bold; font-size: 10px; }
  .emp-email { font-size: 8px; color: #666; }
  .pulse-desc { color: #444; line-height: 1.4; max-width: 250px; }
</style>
</head>
<body>
  <div class="header">
    <img src="{{ public_path('LoopsBlack.png') }}" style="height:30px;width:auto;margin-bottom:10px">
    <h1>Detailed Session Report</h1>
    <div class="meta">Period: <strong>{{ $dateFrom }}</strong> to <strong>{{ $dateTo }}</strong> | Generated: {{ now()->format('M d, Y H:i') }}</div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Employee</th>
        <th>Date</th>
        <th>Session Time</th>
        <th>Duration</th>
        <th>Description</th>
        <th>Approver</th>
      </tr>
    </thead>
    <tbody>
      @foreach($details as $d)
      <tr>
        <td>
          <div class="emp-info">{{ $d['employee'] }}</div>
          <div class="emp-email">{{ $d['email'] }}</div>
        </td>
        <td style="white-space:nowrap">{{ $d['date'] }}</td>
        <td style="white-space:nowrap">{{ $d['start'] }} - {{ $d['end'] }}</td>
        <td style="font-weight:bold;color:#10b981">{{ $d['duration'] }}</td>
        <td><div class="pulse-desc">{{ $d['pulse'] }}</div></td>
        <td style="font-weight:bold;color:#4F7EFF">{{ $d['approver'] }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">
    Detailed log of all work sessions within the specified period. Generated from Loops.
  </div>
</body>
</html>
