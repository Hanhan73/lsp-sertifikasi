@props([
    'url',
    'color' => 'primary',
])
<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
{{-- ✅ Gunakan {!! !!} untuk disable HTML encoding --}}
<a href="{!! $url !!}" 
   class="button button-{{ $color }}" 
   target="_blank" 
   rel="noopener"
   style="display: inline-block; padding: 12px 30px; background-color: #2d3748; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
{{ $slot }}
</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>