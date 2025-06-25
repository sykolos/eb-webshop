@component('mail::message')
<h1>Üzenet a kapcsolat oldalon keresztül:</h1>

<br>
<h3>Név: {{ $cname }}</h3>
<br>
<h3>Tárgy: {{ $csubject }}</h3>
<br>
<h3>Email cím: {{ $cemail }}</h3>
<br>
<h3>Ünezet: {{ $cmessage }}</h3>
@endcomponent
