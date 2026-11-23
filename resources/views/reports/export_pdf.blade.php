<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>{{ $title }}</title>
<style>
  @page { margin: 20px; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
  .header { border-bottom: 2px solid #3b82f6; padding-bottom: 10px; margin-bottom: 12px; }
  .header-table { width: 100%; border-collapse: collapse; margin: 0; }
  .header-table td { border: 0; padding: 0; background: transparent; vertical-align: middle; }
  .header-brand { width: 55%; }
  .header-brand img { display: block; max-height: 52px; max-width: 200px; }
  .brand-name { font-size: 16px; font-weight: bold; color: #1d4ed8; }
  .header-meta { text-align: right; }
  .report-title { font-size: 13px; font-weight: bold; color: #111827; }
  .header .sub { font-size: 9px; color: #6b7280; margin-top: 3px; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  th { background: #3b82f6; color: #fff; padding: 6px; text-align: left; font-size: 9px; text-transform: uppercase; }
  td { border-bottom: 1px solid #e5e7eb; padding: 5px; }
  tr:nth-child(even) td { background: #f9fafb; }
  .footer { margin-top: 16px; font-size: 8px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 6px; }
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
  <div class="footer">Confidential — for internal use only. Page 1</div>
</body></html>
