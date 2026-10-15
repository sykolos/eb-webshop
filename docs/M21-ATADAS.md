# M21 átadás és üzemeltetési rend

Ez a dokumentum az M01-M20 stagingen ellenőrzött Laravel webshop átadási
kerete. A staging azonosítója: `https://ebtest.dekor26.hu`. Éles kiadás csak
külön jóváhagyott eseményként indítható; a `develop` push kizárólag staginget
érint.

## Ellenőrzési belépési pontok

- Élő állapot: `GET /health/live`.
- Függőségek állapota: `GET /health/ready`.
- Útvonaljegyzék: `php artisan route:list --except-vendor`.
- Üzleti adat-egyeztetés: `php artisan data:integrity-report --json`.
- Kapacitásjelentés: `php artisan system:capacity-report --json`.
- Scheduler állapot: `php artisan system:heartbeat` és a `scheduler_heartbeats` rekord.
- Queue próba: `php artisan system:queue-smoke`, majd `php artisan queue:work --once`.

## Oldal- és igénylefedettség

Az elfogadott fő felületek a Laravel útvonalakon működnek: főoldal,
katalógus/keresés, termékadatlap, gyorsrendelés, kosár, checkout, belépés és
partnerregisztráció, fiók/rendelések/címek/kedvencek, valamint az admin
dashboard, rendelések, termékek, kategóriák, egységek, felhasználók,
speciális árak, exportok, bannerek, ajánlatok, email, automatizmusok,
aktivitás, kosárfigyelés és ticketek. A ZIP bemutató- és demoútvonalai nem
éles funkciók; a komponensgaléria admin jogosultság mögött marad.

Minden kiadás előtt a route-lista és a staging browser-átvétel együtt adja az
elfogadási bizonyítékot. A publikus ármentes katalógus és a partneri
árképzés külön ellenőrzendő; a partneri/special-price logika nem kerülhet
megosztott cache-be vagy vendég HTML-be.

A ZIP referencia `/orderpage` útvonala Laravelben `/shop` néven létezik; a
`/quick-order` régi belépési pont a webshopra vezet. Belső linkek a névvel
rendelkező Laravel route-okat használják, ezért a referenciaútvonal hiánya nem
jelent működő belső hivatkozást.

## Frontend átállás

Az M21.02 után a frontend runtime natív DOM/fetch és Alpine.js alapú. A
Bootstrap, jQuery, Popper, Select2, Bootstrap Icons és külső Swiper betöltés
megszűnt. A régi Blade osztályokhoz csak célzott, helyi kompatibilitási CSS
maradt, hogy a még át nem nevezett legacy nézetek működése ne törjön.

## Biztonsági alapok

- `SecurityHeaders` middleware: CSP, `nosniff`, frame- és referrer-védelem,
  Permissions-Policy és same-origin erőforrás-védelem.
- Session cookie: HttpOnly, SameSite=Lax, HTTPS esetén Secure; eltérő domain
  csak külön konfigurált `SESSION_DOMAIN` mellett használható.
- Minden állapotváltoztató webkérés CSRF-védett és a kritikus végpontok
  rate-limitedek.
- Privát feltöltés UUID-s fájlnévvel, kiterjesztés/MIME/méret-ellenőrzéssel,
  publikus documentum csak publikált termékhez, admin és partner jogosultság
  alapján tölthető le.
- Auditnapló nem tárol jelszót, tokent, cookie-t, kétfaktoros titkot vagy
  teljes levéltörzset.
- A külső tracking szolgáltató nincs aktiválva. Első fél aktivitás csak
  elfogadott mérési hozzájárulás után rögzül.

## Teljesítmény és kapacitás

Alap célok a staging méréshez: publikus HTML oldalak p95 <= 800 ms üres vagy
reprezentatív adatkészleten, admin lista p95 <= 1200 ms, kereső/autocomplete
p95 <= 500 ms, 390/768/1440 px-en nulla horizontális overflow. A tényleges
értékeket a kiadási naplóban kell rögzíteni; a cél nem helyettesíti az üzleti
adatbázis és a tárhely mérését.

Mérési parancsok:

```powershell
php artisan data:integrity-report --json
php artisan system:capacity-report --json
Invoke-WebRequest https://ebtest.dekor26.hu/health/live
Invoke-WebRequest https://ebtest.dekor26.hu/health/ready
```

Képméretnél a banner három rögzített változata marad az elfogadott forrás;
új képet publikálás előtt méret, MIME, fájlméret és mobil crop alapján kell
ellenőrizni.

## Adat-egyeztetés és visszaállítás

Az éles próbamigráció előtt az alábbi riportot kell elmenteni a kiadási
azonosító mellé:

```powershell
php artisan data:integrity-report --json > storage/logs/data-integrity.json
php artisan system:capacity-report --json > storage/logs/capacity-report.json
```

Egyeztetendő: felhasználók, publikált termékek, rendelések darabszáma és
nettó/bruttó összege, rendelési tételek, speciális ár sorok és érintett
felhasználó/termék darabszám, feltöltött média, kosarak és ticketek.
Eltérés esetén nincs végső adatátadás; a korábbi kód és adatmentés marad az
aktív visszaállítási pont.

Visszaállítási sorrend: forgalom korlátozása, kiadási csomag kódrollbackje,
alkalmazás cache újraépítése, adatbázis-mentés visszaállítása csak jóváhagyott
ablakban, fájlmédiák ellenőrzése, readiness és üzleti smoke teszt.
Adatrestore és kódrollback külön esemény; egyik sem jelent automatikus éles
deployt.

## Staging -> éles folyamat

1. A funkciós commit `develop` ágra kerül, a meglévő verify/deploy workflow
   stagingre telepít és a stagingen azonos SHA-t kell ellenőrizni.
2. A staging QA után az üzleti felelős külön éles kiadási ablakot hagy jóvá.
3. Kiadás előtt adat-integritási és kapacitásriport, backup-azonosító,
   migrációs rollback-terv, queue/scheduler állapot és readiness ellenőrzés
   kerül a kiadási bejegyzésbe.
4. Éles kiadás után `/health/live`, `/health/ready`, belépés, katalógus,
   partnerár, kosár és admin dashboard smoke teszt szükséges.

## SLA és kommunikáció

Operatív alapértelmezett idősáv: hétfő-péntek 08:00-17:00 Europe/Budapest.
Ez a munkafolyamat alapja, a szerződéses véglegesítést külön üzleti
jóváhagyásban kell rögzíteni.

- Kritikus hiba: aznap megkezdés, enyhítés/helyreállítás folyamatos követése.
- Magas prioritás: megkezdés legkésőbb egy munkanapon belül.
- Közepes/alacsony: tervezett backlog és következő staging kiadás.
- Reakció, helyreállítás és végleges javítás külön időpontként naplózandó.
- Havi 1-2 személyes meeting: döntések, nyitott hibák, kapacitás és következő
  staging pontok.
- Havi teljesítési riport: kiadások, hibák, SLA, tesztek, válaszidők,
  tárhely/email/queue állapot és következő kockázatok.

## Ismétlődő karbantartás

Hetente: log, queue, scheduler, readiness, tárhely és backup állapot.
Havonta: Laravel/csomagaudit, biztonsági ellenőrzés, teljesítménymérés,
backup-visszaállítás próba és riport. Minden frissítés előbb stagingre kerül,
és csak sikeres verify/staging QA után válhat éles kiadási jelöltté.

## Átadott és halasztott tételek

Átadott: Laravel alap, partneri és termékköri speciális árak, kosár/rendelés,
termék- és banneradmin, ajánlatmegjelenítés, export/import, email/automatizmus
alapok, ticket kommunikáció, elérhetőségi státuszok és natív frontend runtime.

Halasztott vagy külön üzleti jóváhagyást igényel: külső email provider és
domainhitelesítés, külső GA4/Clarity aktiválás, mennyiségi készlet/foglalás,
új akciós árprioritási motor, végleges katalógus/jogi tartalmak, konkrét
helyettesítési SLA-személyek és éles szolgáltatói hozzáférések.
