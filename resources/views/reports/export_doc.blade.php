<html><head><meta charset="utf-8"><title>{{ $title }}</title>
<style>
  body { font-family: Calibri, Arial, sans-serif; font-size: 10pt; color: #1f2937; }
  .header { border-bottom: 2px solid #3b82f6; padding-bottom: 8px; margin-bottom: 10px; }
  .header-table { width: 100%; border-collapse: collapse; margin: 0; }
  .header-table td { border: 0; padding: 0; background: transparent; vertical-align: middle; }
  .header-brand { width: 55%; }
  .header-brand img { display: block; max-height: 48px; max-width: 190px; }
  .brand-name { font-size: 14pt; font-weight: bold; color: #1d4ed8; }
  .header-meta { text-align: right; }
  .report-title { font-size: 11pt; font-weight: bold; color: #111827; }
  .header .sub { font-size: 9pt; color: #6b7280; margin-top: 2px; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  th { background: #3b82f6; color: #fff; padding: 5px; text-align: left; font-size: 9pt; }
  td { border: 1px solid #d1d5db; padding: 4px; }
  tr:nth-child(even) td { background: #f9fafb; }
  .footer { margin-top: 12px; font-size: 8pt; color: #9ca3af; text-align: center; }
</style>
</head><body>
  <div class="header">
    <table class="header-table">
      <tr>
        <td class="header-brand">
          @if(!empty($logoDataUri))
            <img src="{{ $logoDataUri }}" alt="{{ $businessName }}">
          @else
            <div class="brand-name">{{ $businessName }}</div>
          @endif
        </td>
        <td class="header-meta">
          <div class="report-title">{{ $title }}</div>
          <div class="sub">Generated: {{ now()->format('d M Y H:i') }}</div>
        </td>
      </tr>
    </table>
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
