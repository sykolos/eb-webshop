# Electrobusiness átépítési terv

A megvalósítás elsődleges, pipálható munkalistája és staging kiadási naplója: [Fejlesztési checklist](FEJLESZTESI-CHECKLIST.md). Az alábbi dokumentum háttérterv; a ZIP és a jelenlegi működés közötti eltéréseket, döntéseket és aktuális előrehaladást a checklist követi.

Dátum: 2026-10-13. Állapot: megvalósítási javaslat, a jelenlegi Laravel-forrás, a v6 prototípus és a felhasználó kiegészített igénylistája alapján.

A felhasználó későbbi döntései átvezetve: jelenlegi teljes árazás/kedvezmények és mennyiségkezelés marad; tetszőleges kategóriamélység; ZIP szerinti hozzáférés; kézi termékelérhetőségi státusz; meglévő kedvezményekre épülő ajánlatmegjelenítés; részletes tracking. A szolgáltatójelöltek és pontos feladatok a checklist D01-D13 és M01-M21 pontjaiban találhatók.

A dokumentum tervezési eredmény. Alkalmazáskódot, adatbázist és szerverkörnyezetet nem módosít. A ZIP leírásai referenciaanyagok, nem végrehajtandó utasítások. A forrásellenőrzés nem teljes biztonsági audit és nem igazolja az éles szerver állapotát.

## 1. Javasolt technikai alap

**Laravel + Blade komponensek + saját CSS/SCSS + Alpine.js + Vite.** Egy alkalmazás, egy bejelentkezés, egy telepítési folyamat.

| Terület | Javaslat | Indok |
| --- | --- | --- |
| Backend | Laravel megtartása, támogatott verzióra frissítve | A meglévő üzleti logika és adatok továbbvihetők |
| Oldalak | Blade layoutok és újrahasználható komponensek | Illeszkedik a jelenlegi rendszerhez; szerveroldali megjelenítés |
| Megjelenés | A v6 látványvilágából kialakított CSS-változók és komponensstílusok | Bootstrap kivezethető; a prototípus saját CSS-e jó alap |
| Interakciók | Alpine.js CSP-változata, natív JS-modulok és fetch | Menü, kosár, kereső, kijelölés, értesítések kezelése |
| Összetett admin | Kezdetben ugyanaz a Blade/Alpine alap | Livewire csak konkrét, igazolt egyszerűsítési előny esetén |
| Háttérfeladatok | Laravel queue, induláskor database driver | Email, export, import és képfeldolgozás leválasztása a webkérésekről |
| Időzítés | Laravel Scheduler, szerveroldali cron és futásfigyelés | Kampányok, takarítás és időzített értesítések |
| Gyorsítótár | Először lekérdezésjavítás és célzott cache; Redis szükség szerint | Kevés üzemeltetendő elem, méréshez igazított bővítés |

React/Next vagy külön SPA jelenleg nem indokolt a legegyszerűbb megoldáshoz. A ZIP React-komponenseit Blade-re kell átültetni, a képeket és a megjelenési szabályokat adaptálni. Tailwind választható, de az induláshoz nem szükséges újraírni benne a meglévő prototípus CSS-ét.

A Bootstrap és jQuery kivezetése oldalanként történjen külön új layouttal és assetcsomaggal. A végén függőségek, CDN-hivatkozások, pluginek és inline kódok ellenőrzésével záruljon. A `resources/js/bootstrap.js` fájlnév önmagában nem bizonyít Bootstrap UI-függőséget.

### Verziófrissítés

A composer.json jelenleg Laravel 9-et és PHP 8.2.31 platformot ír elő. A Laravel 9 biztonsági támogatása 2024-02-06-án véget ért. A javasolt cél Laravel 13, amely PHP 8.3-at vagy újabbat igényel. A tényleges PHP-verziót külön kell ellenőrizni a webes és CLI-környezetben, a csomagok kompatibilitásával együtt.

Frissítés: regressziós tesztek, majd főverziónként ellenőrzött lépések stagingen. A Laravel 12 legfeljebb átmeneti állomás legyen, ha csomag vagy tárhely korlátoz; biztonsági támogatása 2027-02-24-ig tart.

Források: [Laravel 9 támogatás](https://laravel.com/docs/9.x/releases), [Laravel 13 támogatás és PHP-követelmény](https://laravel.com/docs/13.x/releases), [Alpine CSP-változat](https://alpinejs.dev/advanced/csp).

## 2. A kódban látott elsődleges problémák

| Prioritás | Megfigyelés | Teendő |
| --- | --- | --- |
| P0 | `AccountpageController::details()` és `getpdf()` azonosítóval kér le rendelést tulajdonosszűrés nélkül; az útvonalon csak auth védelem látszik | Saját rendelésre szűrés és OrderPolicy; másik felhasználó rendelésének/PDF-jének tiltását tesztelni |
| P0 | `routes/web.php` nyilvános `/clear` útvonala cache-törlést hív | Üzemeltetési művelet áthelyezése CLI/deploy folyamatba |
| P0 | A kosárba helyezés kliensoldali mennyiséget és URL-ben kapott szorzót tárol megfelelő validáció nélkül | Pozitív mennyiség, felső korlát, szerveroldali kiszerelés és egységes szabályok |
| P0 | `Checkout::checkout()` külön menti a rendelést és tételeit tranzakció nélkül | Tranzakció, ismételt beküldés elleni védelem, konzisztens árpillanatkép |
| P1 | Laravel 9 és régi csomagverziók | Támogatott verzióra frissítés, Composer/npm audit és kompatibilitási ellenőrzés |
| P1 | `OrderController::updateStatus()` tetszőleges beküldött státuszt ment | Engedélyezett átmenetek, jogosultság, naplózás |
| P1 | Egyetlen `is_admin` jelző és üres policy mapping | Szerepkörök, műveleti és rekordhoz kötött jogosultságok |
| P1 | A saját login útvonalnál nem látszik alkalmazásszintű próbálkozáskorlát | Login, jelszóreset, regisztrációkérés és email-végpontok limitálása; admin MFA |
| P1 | Checkout közvetlenül küld emailt; a queue konfiguráció alapértéke sync | Valódi háttérfeldolgozás, commit utáni indítás, hibajelzés és újrapróbálás |
| P1 | `Products::getPriceForUser()` termékenként lekérdezi a kategóriakedvezményt; minden speciális ár automatikusan betöltődik | Árkontextus egyszeri betöltése, csak releváns partnerárak, N+1 lekérdezések megszüntetése |
| P2 | Duplikált `/cart/count` útvonal; régi és új kosárvégpontok keverednek | Egységes kosár API és route-névjegyzék |

A forrás alapján fennálló kockázatokat a megvalósítás elején tesztekkel kell rögzíteni. Külső szervervédelmet vagy éles adat-hozzáférést ez az ellenőrzés nem vizsgált.

## 3. Backend felelősségek

A controller a bemenetet veszi át és választ ad. Form Request validál, Policy/Gate jogosultságot ellenőriz, a service a több lépésből álló üzleti folyamatot kezeli. Egyszerű CRUD-műveletekhez ne készüljön szükségtelen köztes réteg.

- `PricingService`: a jelenlegi alapár, kategória- és extra kedvezmény, speciális partnerár és minden meglévő szerkesztési lehetőség megőrzése; egyetlen közös számítás a listában, kosárban és rendelésben. A fixár-elsőbbség, összeadott kedvezmény és kerekítés regressziós teszttel rögzítendő. Új, ezt felülíró akciómotor nem készül.
- `CartService`: adatbázisban mentett kosár, tételösszevonás, kiszerelés, verzióütközések és párhuzamos módosítások.
- `CheckoutService`: jogosult partner, címek, aktuális termékek/árak, tranzakció, egyedi beküldési azonosító, rendelés és tételek.
- `OrderStatusService`: állapotváltások, tömeges műveletek, napló, kapcsolódó értesítések.
- `CatalogQuery`: keresés, kategóriafa, szűrők, rendezés és lapozás közös szabályai.
- `Import/Export` feladatok: soronkénti validáció, jogosultság, háttérfeldolgozás, eredmény és hibajegyzék.
- `Notification` és kampányfeladatok: külön tranzakciós és marketingküldés, újrapróbálás, küldési napló, ismétlésvédelem.

A rendelés őrizze meg a vásárláskori nevet, cikkszámot, egységet, kiszerelést, kedvezményt, nettó árat, áfát, végösszeget és címadatokat. A történeti dokumentum nem függhet a később módosított termék- vagy partneradatoktól. A pénzkezeléshez rögzített pontosság és egységes kerekítés szükséges.

Az árakat a szerver számolja. Partnerenkénti árakat és jogosultsághoz kötött adatokat közös publikus cache-be tenni nem szabad. A kliens csak a megjelenítéséhez szükséges mezőket kapja meg.

## 4. Funkciólista és függőségek

A kiadások sorrendje: A = alapok, B = új webshop, C = admin, D = automatizmusok és kommunikáció. Az E külön specifikálandó bővítés.

| Terület | Tartalom és elfogadási feltétel | Kiadás / függőség |
| --- | --- | --- |
| Biztonság és backend | Fenti P0 javítások, támogatott Laravel, service-ek, policyk, validáció, strukturált hibák és auditnapló | A |
| Staging és deploy | Elkülönített környezet, automatikus tesztek, ellenőrzött kiadás, visszaállítási próba | A |
| Queue és Scheduler | Működő worker és cron, retry/backoff, sikertelen feladat jelzése, ütköző futások kizárása | A; tárhely képességei |
| Design és Figma | A ZIP alapján oldal- és állapotlista; hiányzó képernyők, mobil/tablet, hibás/üres/töltő állapotok tervezése | B előtt; Figma-dokumentálás külön átadandó |
| Közös frontend | Új fejléc, footer, navigáció, űrlapok, táblák, ikonok, értesítések; Bootstrap/jQuery teljes kivezetése az átállás végére | B és C |
| Hero és úszó banner | Asztali/tablet/mobil kép, link, cím, alt szöveg, sorrend, időzítés, publikálás és előnézet | B; médiakezelés |
| Bannerútmutató PPT | Jóváhagyott képarányok, biztonságos szövegzónák, képméretek, fájlformátumok és minták | B végleges bannerkomponense után |
| Kategóriák | Tetszőleges mélységű szülő/gyerek fa, sorrend, láthatóság, útvonal és öröklődő szűrők; ciklusvédelem, törlés/áthelyezés | B; adatmodell, változatlan árazási szabályok |
| Termékkatalógus | Lista/rács, termékrészlet, kategóriák és alkategóriák, tulajdonságok és szűrők, mobil szűrőpanel | B |
| Keresés | Főmenü autocomplete cikkszámra és névre; késleltetés, elavult válaszok eldobása, billentyűzetes használat | B; indexelt lekérdezések |
| Megtartott állapot | Keresés, szűrők, rendezés és oldal az URL-ben; visszalépéskor megmaradó állapot | B |
| Kosár | Adatbázisban tárolás, gyors hozzáadás, kiszerelési lépések, visszajelzés, eltűnő/hibás termék kezelése | B; Cart/PricingService |
| Vásárlási folyamat | Tovább vásárlok modal eltávolítása; nem blokkoló értesítés és frissülő kosárjelző; dupla rendelés kizárása | B |
| Partnerfiók | Regisztrációkérés és jóváhagyás, rendeléstörténet, dokumentumok, címek, cégadatok, jelszó és értesítések | B; jogosultság |
| Admin felhasználók | Üzletileg szerkeszthető adatok; szerepkör külön jogosultsággal; aktivitás, jóváhagyás, tiltás és rendelési viselkedés szerinti szűrők | C; jelszó helyett biztonságos visszaállítás |
| Admin rendelések | Listabeli státuszváltás, kijelölés és tömeges módosítás; részleges hibák és audit látható | C; állapotgép |
| Árazás | Partnerárak és kategóriakedvezmények adminja, előnézet, ellenőrzött tömeges szerkesztés | C; PricingService |
| Dashboard és top termékek | Definiált forgalom, rendelésszám, top termék és aktivitás; időszak és kizárt státuszok egységesek | C; összesített lekérdezések |
| Kosárfigyelés | Jogosult admin lássa a mentett kosarat, utolsó aktivitást és állapotot; adminmegtekintés ne módosítsa | C; adatbázisos kosár |
| Excel export | Minden érintett üzleti tábla aktuális szűrésének exportja, oszlopjogosultságok, nagy export háttérben, lejáró privát letöltés | C; táblanévjegyzék, képletinjektálás elleni védelem |
| Excel import | A v6-ból átvett importigény: minta, próbaellenőrzés, hibás sorok, duplikációs szabály és audit | C; termék/ár adatmodell |
| Email menüpont | Sablonok, címzettkör, tesztküldés, előnézet, küldési állapot; tömeges küldés queue-ban | D; email-szolgáltató |
| Hírlevél | Kampány, szegmens, időzítés, leiratkozás és küldésből kizárás; kézbesítési hibák kezelése | D; címzett- és hozzájárulási adatok |
| Automatizmusok | Kosárelhagyási emlékeztető, eseményhez kötött és időzített értesítések; küldési limit és ismétlésvédelem | D; vásárláskor emlékeztető törlése |
| Ticket és fejlesztői üzenőfal | Közös ticketalap, ügyfélnek látható és belső hozzászólás, felelős, prioritás, melléklet, státusz | D; belső tartalomhoz külön hozzáférés |
| Chat | Meglévő megoldás felmérése; a közös üzenetkezelésre építés; valósidejűség külön indoklással | D/E |
| Live user tracking | Részletes látogatói/esemény- és vásárlási útvonal mérés; GA4 + Clarity javaslat, Matomo alternatíva; szolgáltató később választandó | C/M15; consent és tájékoztatás indulás előtt |
| Termékelérhetőség | Adminból állítható Készleten / Beszállítói készleten / Beszerzés alatt és további státuszok; mennyiségi készlet/foglalás/minimumfigyelés későbbre | B/C; M06/M19 |
| Akciók és ajánlatok | Aktuális ajánlatok és bannerek megjelenítése a meglévő árazásból; külön kedvezményprioritás vagy akciómotor nélkül | M14/M20 |

## 5. Gyors működés: mérhető célok

Az alábbiak javasolt elfogadási célok, nem jelenlegi mérési eredmények vagy szerződéses vállalások. A termékszám, partnerszám, forgalom és tárhely ismeretében véglegesítendők.

- Reprezentatív staging adatokkal és rögzített egyidejű terheléssel a katalógus/kosár szerverválasza p95-ben legfeljebb 500 ms; autocomplete legfeljebb 300 ms, a hálózati késleltetés nélkül.
- Oldalanként rögzített lekérdezési keret; ne legyen termékszámmal arányosan növekvő árlekérdezés.
- Lapozott listák, ellenőrzött rendezési mezők, indexek a tényleges keresési és szűrési mintákhoz. Részszöveges keresésnél a szokásos B-tree index önmagában nem elég; szükség esetén full-text keresés és lekérdezésterv-vizsgálat.
- Méretezett/reszponzív képek, a fő kép prioritása, többi kép késleltetett betöltése, helyi fontok és csak szükséges JavaScript.
- Partnerárak cache-kulcsa tartalmazza a partner- és árazási kontextust; módosításkor célzott érvénytelenítés.
- Mobilon és tableten ellenőrzött navigáció, érintési célok, olvashatóság; billentyűzetes használat és fókuszkezelés.
- Nagy export, PDF, email és képfeldolgozás ne tartsa nyitva a vásárlói kérést. Azonnali kis feladat kivétel lehet méréssel alátámasztva.

## 6. Staging, biztonság és kiadás

A `DEPLOYMENT.md` és `.github/workflows/deploy.yml` alapján már van develop ágra épülő teszttelepítési alap. Ennek tényleges szerveroldali állapota még ellenőrizendő.

1. Elkülönített staging adatbázis, tárhely, alkalmazáskulcs, session/cookie, queue és küldési hitelesítők. Éles adatokból anonimizált tesztadat; staging email csak tesztcímzettekhez. Hozzáférés-védelem és keresőindexelés tiltása.
2. CI: függőségek lockfájlból, frontend build, fontos funkcionális tesztek, függőségaudit. A biztonsági találatokat értékelni kell, nem elrejteni.
3. Minden módosítás stagingen kipróbálva. Ugyanaz a commit és elkészített build kerüljön élesbe, környezetenként külön konfigurációval.
4. Élesítés előtt mentés és visszaállítási terv; lehetőleg visszafelé kompatibilis, bővítő adatbázis-migrációk. Kódbeli rollback nem jelent automatikus adatbázis-visszaállítást.
5. Kiadás utáni belépés/katalógus/kosár/rendelés ellenőrzés, worker újraindítás, ütemező és emailkézbesítés figyelése.

Biztonsági alap: rekordhoz kötött jogosultság, CSRF-védelem, biztonságos session/cookie, egységes jelszópolitika, admin MFA, rate limit, feltöltések típus- és méretellenőrzése, képek újrakódolása, privát mellékletek és exportok. Naplóban ne legyen jelszó, token vagy teljes érzékeny kérés. Éles környezetben debug kikapcsolva; CSP fokozatos bevezetése jelentési móddal és teszteléssel.

Kritikus tesztek: idegen rendelés/PDF/cím/kosár tiltása; partnerár elsőbbség és kerekítés; hibás kiszerelés; dupla checkout; részleges mentés visszagörgetése; rendeléstől független emailhiba; tömeges státuszváltás jogosultsága; export adatszivárgás kizárása. Elérhetőségi státusz nem vezethet be nem kért rendelési korlátot. Trackingnél consent/maszkolás és ismételt esemény teszt is kell.

## 7. Üzemeltetés és infrastruktúra

A folyamatos rendelkezésre állás külön szolgáltatási vállalás, nem egyszeri fejlesztési funkció.

- Heti öt naphoz konkrét munkanapok, idősáv, ünnepnapkezelés, kapcsolattartó és hibabejelentési csatorna szükséges.
- Kritikus hibák aznapi kezelése és magas prioritású hibák egy munkanapon belüli megkezdése: a válaszidőt, munkakezdést, helyreállítást és végleges javítást külön kell meghatározni.
- Havi 1-2 személyes meeting és havi riport: elkészült munkák, incidensek, válaszidők, teljesítmény, mentési próbák és következő prioritások.
- Tervezett Laravel/csomagfrissítések staging ellenőrzéssel; biztonsági hibáknál gyorsított kiadási út.
- Naplók rotációja, hibariasztás, queue/scheduler felügyelet, tárhelyfigyelés, adatbázis-optimalizálás.
- Mentési gyakoriság, megőrzés és szerveren kívüli példány; rendszeres visszaállítási próba. Elveszíthető adatmennyiség és helyreállítási célidő külön rögzítendő.

Tárhelybővítés előtt felmérendő: PHP CLI/web verzió, memória, adatbázis, cron, tartós queue worker vagy felügyelt alternatíva, SSH, mentés és médiafájlok. A database queue is igényel futó feldolgozót. A Redis-csomag jelenléte nem bizonyít működő Redis-szolgáltatást.

A levelezési postafiókok kapacitása és az alkalmazás kampányküldése külön igény. Tömeges küldéshez szolgáltatói limitek, kézbesíthetőség, domainhitelesítés és visszapattanások kezelése is szükséges; önmagában több lemezterület nem oldja meg.

## 8. Üzleti döntések állapota

| Kérdés | Elfogadott irány / fennmaradó részlet |
| --- | --- |
| Ki láthat katalógust, árat és ki rendelhet? | ZIP szerint: nyilvános ár nélküli katalógus, jóváhagyott partnernek saját ár és vásárlás; valódi hitelesítés |
| Egy céghez több felhasználó tartozhat? | Induláskor a jelenlegi partner/felhasználó kapcsolat; többfelhasználós céges modell külön tervezés |
| Mi a készlet hiteles forrása? | Terméken kézzel állítható elérhetőségi státusz; mennyiségi készletrendszer nem része a jelenlegi scope-nak |
| Akció és partnerár közül melyik nyer? | Teljes meglévő ár/kedvezményrendszer marad; az ajánlati kiemelés nem írja át |
| Mit jelent a live tracking? | Valódi részletes események/útvonalak és élő aktivitás; szolgáltatóválasztás később, consent és tájékoztató indulás előtt |
| Mi számít inaktív felhasználónak? | Utolsó rendelés alapján, állítható időhatárral; rendelés nélküli partner külön csoport |
| Mi számít top terméknek? | Nettó termékforgalom a kiválasztott időszakban; egységes törölt/archivált rendeléskezelés |
| Milyen az úszó banner viselkedése? | Hero/úszó képek, aktuális akciók és kategóriák; próbaképek megengedettek; képarány és végleges tartalom a tervezéskor |
| Milyen a meglévő chat? | A felhasználó későbbre hagyta; D13 |
| Hírlevél/elhagyott kosár címzettköre? | A felhasználó későbbre hagyta; D12 |

## 9. Kivitelezési sorrend és becslési keret

1. A: biztonsági javítások, kritikus regressziós tesztek, staging ellenőrzés, Laravel/PHP-frissítés, queue és deploy alap.
2. B: designrendszer és kategória/ár/kosár modellek, majd teljes terméklista -> kosár -> rendelés -> fiók folyamat stagingen, a v6 megjelenésével.
3. C: admin táblák és szerkesztők, jogosultságok, tömeges műveletek, export/import, dashboard és kosárfigyelés.
4. D: emailközpont, hírlevél, automatizmusok, ticket és a pontosított kommunikációs igények.
5. M19/M20: kézi termékelérhetőség és aktuális ajánlatmegjelenítés, a checklist függőségei után akár előrehozva. A mennyiségi készletmotor és új akciómotor külön későbbi bővítés.

Minden kiadás része az érintett adatok migrációja, tesztelés, mobil ellenőrzés és üzemeltetési leírás. Éles váltás előtt a meglévő fiókok, rendelések, partnerárak, címek és feltöltések megőrzését próbamigrációval kell ellenőrizni. A korábbi rendelések hiányzó történeti adatait nem szabad utólag biztos tényként kitalálni.

A korábbi 10-14, majd 12-20 fejlesztői hetes becslések a korábbi scope-ra vonatkozó tájékozódási értékek, nem vállalások. A mostani döntések egyszerűsítik a készlet/akció részt, és bővítik a kategóriafát/trackinget; a részletes felmérés után új becslés szükséges. Külső integrációk, külön design/PPT munka, szolgáltatói átfutás és folyamatos üzemeltetés külön tétel. A frissítési kompatibilitás az A szakaszban derül ki.

Az első konkrét fejlesztési csomag az A szakasz. Ennek átadási feltétele: a kritikus jogosultsági és rendelési tesztek futnak, a staging ellenőrizhetően elkülönül, a támogatott Laravel célhoz vezető frissítés tisztázott, és a háttérfeladat/deploy működése tesztelt.
