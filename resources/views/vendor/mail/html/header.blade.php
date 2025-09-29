@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'CMSAllocation')
<img src="https://i.imgur.com/GallCyk.png" class="logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
