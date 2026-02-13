@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'Laravel')
            <img src="{{ asset('images/logo-lsp.png') }}" class="logo" alt="Laravel Logo" style="width: fit-content;">
            @else
            {!! $slot !!}
            @endif
        </a>
    </td>
</tr>