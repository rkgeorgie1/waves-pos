<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>{{ $title }}</title>
<style>
  @page { margin: 20px; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
  .header { border-bottom: 2px solid #4f46e5; padding-bottom: 8px; margin-bottom: 12px; }
  .header h1 { font-size: 16px; margin: 0; color: #4f46e5; }
  .header .sub { font-size: 9px; color: #6b7280; margin-top: 2px; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  th { background: #4f46e5; color: #fff; padding: 6px; text-align: left; font-size: 9px; text-transform: uppercase; }
  td { border-bottom: 1px solid #e5e7eb; padding: 5px; }
  tr:nth-child(even) td { background: #f9fafb; }
  .footer { margin-top: 16px; font-size: 8px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 6px; }
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
  <div class="footer">Confidential — for internal use only. Page 1</div>
</body></html>
