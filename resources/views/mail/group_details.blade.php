@component('mail::message')

<b>Dear {{ $name }},<b>
    
Please find your CO7210 group details below:

@foreach ($groups as $group)

<b><u>Group ID: {{ $group['id'] }}</b></u>

<b>Topic:</b> {{ $group['topic']['topic_name'] }}

<b>Supervisors:</b>
<ul>
    @foreach ($group['group_supervisors'] as $supervisor)
    <li>{{ $supervisor['supervisor']['first_name'] }} {{ $supervisor['supervisor']['last_name'] }} - {{ $supervisor['supervisor']['email'] }}</li>
    @endforeach
</ul>

<b>Students:</b>
<ul>
    @foreach ($group['group_students'] as $student)
    <li>{{ $student['student']['first_name'] }} {{ $student['student']['last_name'] }} - {{ $student['student']['email'] }}</li>
    @endforeach
</ul>
<br>
@endforeach
Or view on the website...
{{-- Localhost url --}}
@component('mail::button', ['url' => 'http://127.0.0.1:8000/view-group'])
    Group Details
@endcomponent

Regards,<br>
CMSAllocation

@endcomponent