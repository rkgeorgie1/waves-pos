<html><head><meta charset="utf-8"><title>{{ $title }}</title>
<style>
  body { font-family: Calibri, Arial, sans-serif; font-size: 10pt; color: #1f2937; }
  .header { border-bottom: 2px solid #4f46e5; padding-bottom: 6px; margin-bottom: 10px; }
  .header h1 { font-size: 14pt; margin: 0; color: #4f46e5; }
  .header .sub { font-size: 9pt; color: #6b7280; margin-top: 2px; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  th { background: #4f46e5; color: #fff; padding: 5px; text-align: left; font-size: 9pt; }
  td { border: 1px solid #d1d5db; padding: 4px; }
  tr:nth-child(even) td { background: #f9fafb; }
  .footer { margin-top: 12px; font-size: 8pt; color: #9ca3af; text-align: center; }
</style>
</head><body>
  <div class="header">
    <h1>Airwaves Wholesalers ERP</h1>
    <div class="sub">{{ $title }} &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}</div>
  </div>
  <table>
    <thead><tr>@foreach($headings as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
    <tbody>
    @foreach($rows as $r)
        <tr>@foreach($r as $c)<td>{{ $c }}</td>@endforeach</tr>
    @endforeach
    </tbody>
  </table>
  <div class="footer">Confidential — for internal use only.</div>
</body></html>
