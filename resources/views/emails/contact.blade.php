<h2>Új kapcsolatfelvételi üzenet érkezett</h2>

<p><strong>Név:</strong> {{ $data['name'] }}</p>
<p><strong>Email:</strong> {{ $data['email'] }}</p>

@if(!empty($data['phone']))
    <p><strong>Telefonszám:</strong> {{ $data['phone'] }}</p>
@endif

<p><strong>Üzenet:</strong></p>
<p style="white-space: pre-line;">{{ $data['message'] }}</p>

<hr>
<p>Ez az üzenet automatikusan generálódott a weboldal kapcsolatfelvételi űrlapjáról.</p>
