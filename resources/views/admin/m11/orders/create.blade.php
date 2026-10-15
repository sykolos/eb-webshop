@extends('layouts.v6.admin')

@section('title', 'Új rendelés')
@section('heading', 'Új rendelés rögzítése')
@section('intro', 'Telefonon vagy e-mailben érkezett rendelés felvitele aktív partnernek.')

@section('content')
<div class="v6-section">
    <form class="v6-form" method="POST" action="{{ route('adminpanel.orders.store') }}" id="manual-order-form">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', $idempotencyKey) }}">
        @if($errors->any())<div class="v6-form-error" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

        <section class="v6-panel">
            <div class="v6-section__header"><div><span class="v6-eyebrow">Partner</span><h2>Partner és teljesítés</h2><p>A kézi rendelés a partner meglévő árképzésével készül. Az egyedi cím nem írja felül a partner törzsadatát.</p></div></div>
            <div class="v6-form-grid">
                <label>Aktív partner<select name="partner_id" id="partner-select" required><option value="">Válassz partnert</option>@foreach($partners as $partner)<option value="{{ $partner->id }}" @selected((string) old('partner_id') === (string) $partner->id)>{{ $partner->user_invoice?->company_name ?: $partner->name }} · {{ $partner->email }}</option>@endforeach</select></label>
                <label>Mentett szállítási cím<select name="shipping_address_id" id="shipping-address"><option value="">Egyedi cím megadása</option></select></label>
                <label>Átvevő neve<input name="shipping_receiver" value="{{ old('shipping_receiver') }}" required></label>
                <label>Telefon<input name="shipping_phone" value="{{ old('shipping_phone') }}" required></label>
                <label>Irányítószám<input name="shipping_zipcode" value="{{ old('shipping_zipcode') }}" required></label>
                <label>Város<input name="shipping_city" value="{{ old('shipping_city') }}" required></label>
                <label class="v6-field--full">Szállítási cím<input name="shipping_address" value="{{ old('shipping_address') }}" required></label>
                <label>Fizetési mód<select name="payment_method" required><option value="0" @selected(old('payment_method', '0') === '0')>Átutalás</option><option value="1" @selected(old('payment_method') === '1')>Készpénz</option></select></label>
                <label>Kezdő állapot<select name="initial_status" required>@foreach($states as $state)<option value="{{ $state }}" @selected(old('initial_status', 'függőben') === $state)>{{ ucfirst($state) }}</option>@endforeach</select></label>
                <label class="v6-check"><input type="checkbox" name="is_direct_shipping" value="1" @checked(old('is_direct_shipping'))> Árazatlan közvetlen szállítás</label>
                <label class="v6-field--full">Rendelési megjegyzés<textarea name="note" rows="3" maxlength="5000">{{ old('note') }}</textarea></label>
                <label class="v6-field--full">Belső státuszmegjegyzés<textarea name="status_note" rows="2" maxlength="2000">{{ old('status_note') }}</textarea></label>
            </div>
        </section>

        <section class="v6-panel">
            <div class="v6-section__header"><div><span class="v6-eyebrow">Termékek</span><h2>Rendelési tételek</h2><p>A csomagszámot add meg; az egység- és partnerárat a szerver számolja és pillanatképként menti.</p></div></div>
            <label>Termékkereső<input id="product-search" type="search" placeholder="Név vagy cikkszám"></label>
            <div id="order-lines" style="display:grid;gap:12px;margin-top:16px">
                <div class="v6-form-grid order-line"><label>Termék<select name="lines[0][product_id]" class="product-select" required><option value="">Válassz terméket</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->title }} · {{ $product->serial_number }} · {{ AppModelsProducts::AVAILABILITY_STATUSES[$product->availability_status] ?? $product->availability_status }}</option>@endforeach</select></label><label>Csomagszám<input type="number" name="lines[0][quantity]" value="1" min="1" max="9999" required></label><div class="v6-actions" style="align-self:end"><button type="button" class="v6-button v6-button--outline remove-line" disabled>Tétel törlése</button></div></div>
            </div>
                <div class="v6-actions" style="margin-top:16px"><button type="button" class="v6-button v6-button--outline" id="add-line">+ Tétel hozzáadása</button></div>
            <label class="v6-check" style="margin-top:16px"><input type="checkbox" name="confirm_order" value="1" required @checked(old('confirm_order'))> Ellenőriztem a partnert, a címet, a fizetést és a tételeket.</label>
        </section>

        <div class="v6-actions"><button class="v6-button v6-button--primary" type="submit">Ellenőrzés és rögzítés</button><a class="v6-button v6-button--outline" href="{{ route('adminpanel.orders') }}">Mégse</a></div>
    </form>
</div>
<script>
(() => {
    const partners = @json($partnerAddresses);
    const partner = document.getElementById('partner-select');
    const address = document.getElementById('shipping-address');
    const fields = ['shipping_receiver','shipping_phone','shipping_zipcode','shipping_city','shipping_address'];
    const updateAddresses = () => {
        address.innerHTML = '<option value="">Egyedi cím megadása</option>';
        (partners[partner.value] || []).forEach((item) => { const option = new Option(item.label, item.id); address.add(option); });
    };
    address.addEventListener('change', () => {
        const item = (partners[partner.value] || []).find((entry) => String(entry.id) === address.value);
        fields.forEach((name) => { const input = document.querySelector(`[name="${name}"]`); const key = name.replace('shipping_', ''); input.value = item?.[key] || ''; input.readOnly = Boolean(item); });
    });
    partner.addEventListener('change', updateAddresses);
    updateAddresses();

    const lines = document.getElementById('order-lines');
    const template = lines.querySelector('.order-line');
    let index = 1;
    const updateRemoveButtons = () => lines.querySelectorAll('.remove-line').forEach((button) => { button.disabled = lines.children.length === 1; });
    document.getElementById('add-line').addEventListener('click', () => {
        const row = template.cloneNode(true);
        row.querySelectorAll('select,input').forEach((input) => { input.name = input.name.replace(/lines\[0\]/g, `lines[${index}]`); if (input.tagName === 'SELECT') input.value = ''; if (input.type === 'number') input.value = 1; });
        lines.appendChild(row); index += 1; updateRemoveButtons();
    });
    lines.addEventListener('click', (event) => { if (event.target.matches('.remove-line')) { event.target.closest('.order-line').remove(); updateRemoveButtons(); } });
    document.getElementById('product-search').addEventListener('input', (event) => {
        const query = event.target.value.toLowerCase();
        lines.querySelectorAll('.product-select').forEach((select) => [...select.options].forEach((option, optionIndex) => { option.hidden = optionIndex > 0 && query !== '' && !option.text.toLowerCase().includes(query); }));
    });
})();
</script>
@endsection
