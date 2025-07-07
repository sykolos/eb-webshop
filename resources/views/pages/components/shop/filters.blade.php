@if(request('category') == 11)
    <label class="form-label d-block">Kapcsoló:</label>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="kapcsolo[]" value="van" id="kapcsolo_van" {{ collect(request('kapcsolo'))->contains('van') ? 'checked' : '' }}>
        <label class="form-check-label" for="kapcsolo_van">Kapcsolóval</label>
    </div>
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" name="kapcsolo[]" value="nincs" id="kapcsolo_nincs" {{ collect(request('kapcsolo'))->contains('nincs') ? 'checked' : '' }}>
        <label class="form-check-label" for="kapcsolo_nincs">Kapcsoló nélkül</label>
    </div>
@endif

@if(request('category') == 7)
    <label class="form-label d-block">Kivitel:</label>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="ontapados[]" value="igen" id="ontapados_igen" {{ collect(request('ontapados'))->contains('igen') ? 'checked' : '' }}>
        <label class="form-check-label" for="ontapados_igen">Öntapadós</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="ontapados[]" value="nem" id="ontapados_nem" {{ collect(request('ontapados'))->contains('nem') ? 'checked' : '' }}>
        <label class="form-check-label" for="ontapados_nem">Nem öntapadós</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="ontapados[]" value="perforalt" id="ontapados_perforalt" {{ collect(request('ontapados'))->contains('perforalt') ? 'checked' : '' }}>
        <label class="form-check-label" for="ontapados_perforalt">Perforált</label>
    </div>
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" name="ontapados[]" value="toldo" id="ontapados_toldo" {{ collect(request('ontapados'))->contains('toldo') ? 'checked' : '' }}>
        <label class="form-check-label" for="ontapados_toldo">Toldó, végzáró</label>
    </div>
@endif

@if(request('category') == 14)
    <label class="form-label d-block">Oldhatóság:</label>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="oldhato[]" value="igen" id="oldhato_igen" {{ collect(request('oldhato'))->contains('igen') ? 'checked' : '' }}>
        <label class="form-check-label" for="oldhato_igen">Oldható</label>
    </div>
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" name="oldhato[]" value="nem" id="oldhato_nem" {{ collect(request('oldhato'))->contains('nem') ? 'checked' : '' }}>
        <label class="form-check-label" for="oldhato_nem">Nem oldható</label>
    </div>
@endif

@if(request('category') == 15)
    <label class="form-label d-block">Lépésállóság:</label>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="lepesallo[]" value="igen" id="lepesallo_igen" {{ collect(request('lepesallo'))->contains('igen') ? 'checked' : '' }}>
        <label class="form-check-label" for="lepesallo_igen">Lépésálló</label>
    </div>
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" name="lepesallo[]" value="nem" id="lepesallo_nem" {{ collect(request('lepesallo'))->contains('nem') ? 'checked' : '' }}>
        <label class="form-check-label" for="lepesallo_nem">Nem lépésálló</label>
    </div>
@endif

@if(request('category') == 8)
    <label class="form-label d-block">Doboz típus:</label>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="doboz[]" value="sullyesztett" id="doboz_sullyesztett" {{ collect(request('doboz'))->contains('sullyesztett') ? 'checked' : '' }}>
        <label class="form-check-label" for="doboz_sullyesztett">Süllyesztett</label>
    </div>
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" name="doboz[]" value="falonkivuli" id="doboz_falonkivuli" {{ collect(request('doboz'))->contains('falonkivuli') ? 'checked' : '' }}>
        <label class="form-check-label" for="doboz_falonkivuli">Falon kívüli</label>
    </div>
@endif

@if(request('category') == 12)
    <label class="form-label d-block">Hossz:</label>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="hossz[]" value="1,5m" id="hossz_15m" {{ collect(request('hossz'))->contains('1,5m') ? 'checked' : '' }}>
        <label class="form-check-label" for="hossz_15m">1,5m</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="hossz[]" value="2m" id="hossz_2m" {{ collect(request('hossz'))->contains('2m') ? 'checked' : '' }}>
        <label class="form-check-label" for="hossz_2m">2m</label>
    </div>
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" name="hossz[]" value="3m" id="hossz_3m" {{ collect(request('hossz'))->contains('3m') ? 'checked' : '' }}>
        <label class="form-check-label" for="hossz_3m">3m</label>
    </div>
@endif
