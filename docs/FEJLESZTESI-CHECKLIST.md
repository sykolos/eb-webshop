# Electrobusiness fejlesztési checklist

Frissítve: 2026-10-16. Ez a megvalósítás és a folytatás elsődleges, Gitben verziózandó munkalistája.
Háttér és indoklás: [Átépítési terv](ATEPITESI-TERV.md). Eltérés esetén a frissebb felhasználói döntés és ez a checklist irányadó.

## Aktuális állapot és folytatás

| Mező | Aktuális érték |
| --- | --- |
| Aktív mérföldkő | M20: Aktuális akciók és partneri ajánlatok, KIADÁSRA KÉSZ |
| Következő fejlesztési feladat | M20.K4: commit, develop push és a staging workflow megfigyelése |
| Alkalmazásfejlesztés ebből a checklistből | M01-M19 stagingen ellenőrzött; M20 ajánlati törzs, termék/kategória-kapcsolat, időzítés, banner-link, export és meglévő árazási integráció lokálisan ellenőrizve; külön chat nem része a rendszernek |
| Utolsó igazolt staging kiadás | `ba9c203`, GitHub Actions [run 38813030720](https://github.com/sykolos/eb-webshop/actions/runs/38813030720): M19 verify/deploy sikeres; staging átvétel megtörtént |
| Aktuális munkaág a felméréskor | codex/m01-staging-foundation |
| Meglévő workflow | D14 szerint működik: develop push -> verify -> deploy a meglévő hat SSH/path titokkal; nincs új kapcsoló vagy environment kapu |
| Dokumentált staging cél | https://ebtest.dekor26.hu; szerveroldali állapot M01-ben ellenőrizendő |
| Függő üzleti döntések | D04 konkrét munkakörök, D08 végleges tartalmak, D11 szolgáltató; D12 későbbre halasztva |
| Mostani változtatás | M20: időzített, kézzel kiválasztott aktuális ajánlatok termék- és kategóriakörrel, M14 bannerkapcsolattal és meglévő PricingService-ármegjelenítéssel; új kedvezmény- vagy árprioritási motor nincs |

### Kezelési szabályok a következő munkamenetekhez

1. Kezdéskor olvasd ezt a fájlt, a folytatási naplót és a Git állapotát. A felhasználó más változtatásait őrizd meg.
2. A következő nyitott, függőségei alapján elkezdhető feladat azonosítójával dolgozz. Egy feladatot csak implementáció és arányos ellenőrzés után pipálj ki.
3. A feladat jelölése `[ ]` = nyitott, `[x]` = teljesült. Folyamatban/blokkolt/halasztott állapot szövegesen szerepeljen; a halasztott feladat nem kész.
4. Minden munkamenet végén frissítsd az aktív pontot, a tényleges ellenőrzéseket, a nyitott hibákat és a következő konkrét feladatot. Sikertelen vagy nem futott tesztet ne jelölj sikeresnek.
5. Mérföldkőállapotok: TERVEZETT -> FOLYAMATBAN -> KÓD KÉSZ -> KIADÁSRA KÉSZ -> STAGINGEN -> STAGINGEN ELLENŐRZÖTT. Ez utóbbihoz futás/commit és helyszíni ellenőrzés kell.
6. Egy elkészült mérföldkő önálló, működő staging kiadás legyen. Félkész modul csak kikapcsolt feature flaggel, működő régi útvonallal kerülhet csomagba; ettől a modul még nem kész.
7. A közvetlenül ráépülő mérföldkő a stagingellenőrzés után folytatódjon. Független dokumentáció/tesztelőkészítés deploy-blokkolás esetén is végezhető.
8. A ZIP és dokumentumai kinézeti/működési referenciák. Demóadat, localStorage és mintaszabály nem válik automatikusan éles üzleti szabállyá.
9. Új igényt stabil új azonosítóval vegyél fel. A már használt azonosítókat ne számozd át; felbontáskor például M09.03a/M09.03b használható.
10. A checklist létrehozása és a skill elkészítése dokumentációs feladat volt. Későbbi „folytasd” vagy mérföldkőre szóló fejlesztési utasítás a megfelelő munka végrehajtását jelenti; a Git/deploy lépéseket a munkamenetben már megadott felhatalmazás szerint kell végigvinni, azt nem szükséges újra bekérni.
11. A projekt folytatási skillje: [electrobusiness-development](../.agents/skills/electrobusiness-development/SKILL.md). A gyökér AGENTS.md is ide irányít; a haladást továbbra is ez a checklist tartja nyilván.

## Referencia és összevetési alap

- ZIP: `C:/Users/kolos/Downloads/electrobusiness-v6-local(1).zip`.
- SHA256: `293EC8E62F8827DA5286FF32F6C997FD009C2214BC39CE9FD62ACD179F83E393`.
- Vizsgált kibontás: `.codex-inspect/electrobusiness-v6-local-1-20261013-213631/electrobusiness-v6/`.
- Az alábbi Z-hivatkozások ehhez a ZIP-gyökérhez képesti forrásfájlok; a ZIP nem szükséges az alkalmazás buildjéhez.
- Z = ZIP-ben látott nézet/működés; L = felhasználói lista; K = jelenlegi Laravel-kód alapján szükséges alapmunka.
- Most forráskód/CSS/átadási leírás alapján történt összevetés. A futó ZIP és Laravel képernyőképes összehasonlítása M05 és az egyes UI-mérföldkövek ellenőrzése.
- A ZIP route-nevei referenciák: a Laravel végleges útvonalai és átirányításai külön mappinget kapnak.

| ZIP-forrás | Mit ad a tervhez? |
| --- | --- |
| DESIGN-HANDOFF.md; app/design-pages.tsx | Funkcióleírás, oldaltérkép, dashboard és nyitott üzleti kérdések |
| app/globals.css; app/design-extension.css; app/admin-workflows.css | Színek, tipográfia, térközök, reszponzív webshop/fiók/admin |
| app/shop-experience.tsx; public/electrobusiness-hero.png | Főoldal, hero, katalógus, kártyák, adatlap, navigáció, footer |
| app/customer-experience.tsx; app/demo-provider.tsx | Belépés, kosár, checkout, fiók, cím, kedvenc és újrarendelés; helyi demóállapot |
| app/admin-experience.tsx; app/admin-workflows.tsx | Adminváz, rendelések, kategóriák/szűrők, partnerek, árak, support |
| app/product-editor.tsx; app/taxonomy.tsx | Füles termékszerkesztő, képelőnézet, kategória/szűrő viselkedés |
| app/admin-create-order.tsx | Admin rendelésrögzítés ellenőrzéssel és helyi pillanatképpel |
| app/admin-transfer.tsx; app/import-validation.ts | XLSX/CSV export és XLSX import szabályai |

## ZIP és igénylista egyesítése

A „van” mindenhol prototípust jelent, nem kész Laravel-funkciót. A „nincs” az átnézett ZIP-ben nem azonosított teljes folyamatot jelenti.

| Terület / felhasználói igény | ZIP-ben látható | Egyesített feladat |
| --- | --- | --- |
| Laravel/service/controller refaktor, jogosultság, biztonság | Backend nincs, csak demóhozzáférés | M02-M04, modulonként service-ek |
| Logging/hibakezelés | UI hibaállapotok, tartós üzemi napló nincs | M04, M05, M21 |
| Scheduler, queue, staging -> éles deploy | Nincs | M01, M03-M04, M21 |
| Bootstrap és jQuery kiváltása | React és saját CSS a referencia | M05, oldalanként átvezetés, teljes kivezetés M21 |
| Új webshopdesign, Figma, mobil | Meglévő arculat és reszponzív nézetek; Figma-fájl nem azonosított | M05, M08; a ZIP kinézete az alap |
| Hero/úszó banner, 3 képfeltöltés, banner-PPT | Hero van; teljes banneradmin/PPT nincs | M14 |
| Kategória/alkategória és szűrők | Két szint, öröklődő szűrők, admin és webshop kapcsolat demóban | M06, M08; D02 szerint tetszőleges mélységre bővítve |
| Termékek fül/termékszerkesztő | Lista és fülek, csomagolás, műszaki adat, helyi képelőnézet | M06, M08 |
| Felhasználók összes adata és sokféle szűrés | Partnerlista, adatlap, jóváhagyás, szerepkör; aktivitási szűrés bővítendő | M10, M12, M15 |
| Rendelés listabeli státusz és tömeges módosítás | Kipróbálható; szűrésváltás törli a kijelölést | M11 |
| Admin kézi rendelésfelvitel | V6 többlet, a listában nem külön szerepel | M11 |
| Ármátrix és globális kedvezmény | Partner/terméknézet, előnézet; webshopárral nem közös | M07; D01 szerint a jelenlegi Laravel-szabályok és szerkesztési lehetőségek maradnak |
| Dashboard/top termékek | Mutatók definíciói és minták, nem valódi statisztika | M15 |
| Aktivitás és live tracking | Teljes mérőrendszer nincs | M12, M15; D10-D11 |
| Kosármentés/figyelés, mennyiség, gyors hozzáadás | Helyi demókosár és csomagszám; DB/adminfigyelés nincs | M09, M15 |
| Tovább vásárlok modal megszüntetése, kosárértesítés | Új kosárkezelési minta | M09; régi modal kivezetése |
| Keresés/autocomplete, megtartott keresés | Katalógus keres/szűr; teljes szerveroldali autocomplete/persist bővítés | M08 |
| Fiók és regisztrációkérés | Belépő/partnerkérelem/jelszó/fiók demo | M10, M12 |
| Kedvencek, újrarendelés, gyorsrendelés, alapértelmezett cím | ZIP-többlet | M08-M10 |
| Checkout/árazatlan szállítólevél/megjegyzés/visszaigazolás | ZIP-többlet; éles rendelés és küldés nincs | M09, M16 |
| Excel export minden táblához | XLSX + CSV, összes szűrt sor | M13, minden későbbi táblás modul exportja |
| Excel import | ZIP-többlet; beolvasás/előnézet/hibák, helyi mentés | M13 |
| Email menüpont/admin küldés/hírlevél | Csak email-látvány és preferenciaminta | M16 |
| Rendszeres hírlevél/automatikus és időzített értesítés | Teljes folyamat nincs | M16-M17 |
| Kosárelhagyás emlékeztető | Nincs | M17 |
| Ticket/fejlesztői üzenőfal | Belső support demo van | M18 |
| Chat továbbfejlesztése | Nem szükséges a jelenlegi scope-ban | D13 elengedve; a ticketrendszer marad az ügyfélszolgálati felület |
| Készletkezelés?/alacsony készlet? | Mező és jelzés van, mozgás/foglalás/szinkron nincs | D06: termékenként állítható elérhetőség; M06/M19. Mennyiségi készlet és minimumfigyelés későbbi bővítés |
| Akció? | Oldal üres állapota, teljes árazási modul nincs | D07: meglévő kedvezményrendszer megőrzése; M14/M20 ajánlatmegjelenítés új árprioritás nélkül |
| Kapcsolat, GYIK, szállítás/fizetés, katalógus, jogi oldalak | ZIP-többlet, részben helykitöltő tartalom | M14 |
| Üres/hiba/töltő/404 és emailnézet | ZIP-többlet | M05, minden UI-csomag, M16 |
| Heti 5 nap, havi meeting/riport, SLA, hibaprioritások | Nincs, folyamatos szolgáltatási keret | M21 és üzemeltetési napló |
| Frissítések/biztonság/backup/log/teljesítmény ellenőrzés | Üzemi folyamat nincs | M01-M04, M21 és rendszeres napló |
| Tárhelybővítés emailek/adatok miatt | Nincs | M01 felmérés, M16 email, M21 kapacitás |

## Eltérések és döntési napló

A „nyitott” nem engedély automatikus üzleti szabályváltoztatásra. Technikai előkészítés folytatható, a döntést igénylő viselkedés addig nem aktiválható.

| ID | Eltérés / döntés | Kiindulás és érintett pont | Állapot |
| --- | --- | --- | --- |
| D01 | A jelenlegi teljes árazási/kedvezményezési rendszer megmarad | Felhasználó/termék/termékkör speciális árak, kategória- és extra kedvezmények, globális/tömeges szerkesztés megőrzése. A mostani prioritás, összeadott kedvezmény és kerekítés regressziós szerződés; M07 | ELDÖNTVE |
| D02 | Tetszőleges mélységű kategóriafa | Nincs üzleti szintkorlát; ciklusvédelem, öröklődő szűrők és mély fa mellett is használható admin; kategóriamélység önmagában nem változtat árazást; M06/M08 | ELDÖNTVE |
| D03 | Hozzáférés a ZIP szerint | Nyilvános ár nélküli katalógus, belépett/jóváhagyott partnernek saját ár és vásárlás, regisztrációkérés; demóbelépés helyett valódi hitelesítés. Többfelhasználós céges modell nem része ennek; M08-M10 | ELDÖNTVE |
| D04 | Jogosultságok tényleges munkakörökhöz | Munkakörök és műveletek felmérése M04-ben; a korábban felsorolt szerepkörök példák, nem elfogadott végleges lista; M04/M12 | IRÁNY ELDÖNTVE, NÉVSOR KÉSŐBB |
| D05 | Mostani működés megőrzése | Jelenlegi mennyiség/kiszerelés, fizetési és szállítási működés felmérése és regressziós tesztje; a ZIP 1-9999 korlátja nem írja felül. Bemenetvalidáció és szerveroldali számítás ettől még szükséges; M06/M09/M11 | ELDÖNTVE |
| D06 | Termékenként szerkeszthető elérhetőségi státusz | Készleten / Beszállítói készleten / Beszerzés alatt, szükség szerint további állapotok. Mennyiségi raktárkönyvelés, foglalás és minimumküszöb nincs ebben a körben; M06/M19 | ELDÖNTVE |
| D07 | Akciós árazásra is D01 érvényes | Meglévő kedvezményes/speciális árak maradnak; új, azokat felülíró akciómotor nem készül. Aktuális ajánlatok kiemelése/bannere és oldala M14/M20, mindig a meglévő árképzésből | ELDÖNTVE |
| D08 | Hero, úszó képek, aktuális akciók és termékkategóriák kellenek | Stagingen próbaképekkel is készülhet; eszközönkénti képfeltöltés és PPT megmarad. Cégcím/jogi tartalom és végleges képarány külön véglegesítendő; M14 | FUNKCIÓ ELDÖNTVE, TARTALOM KÉSŐBB |
| D09 | A ZIP egyszerű törlési felülete a felhasználó által felvetett irány | Javaslat: Törlés gomb + megerősítés + eltűnés az aktív listából, háttérben visszaállítható törlés és audit. Fizikai végleges adattörlés nincs jóváhagyva; M11 | FELÜLETI IRÁNY JELÖLVE, TECHNIKAI JAVASLAT |
| D10 | Top termék: nettó forgalom; inaktivitás: utolsó rendelés | Felhasználó pontosította: mindkettő. Időszak állítható, törölt/visszaállíthatóan törölt rendelések riportkezelése egységes. Rendelés nélküli partner külön csoport, inaktivitási időhatár állítható; M12/M15 | ELDÖNTVE |
| D11 | Részletes valódi tracking kell, külső szolgáltató is lehet | Látogatások, események, keresés, termék/kosár/checkout tölcsér, kampányok, élő aktivitás; hőtérkép/visszajátszás jelölt funkció. Szolgáltató és adatmegőrzés kiválasztása M15; hozzájárulás és tájékoztatás az éles indulás előtt | TERJEDELEM ELDÖNTVE, SZOLGÁLTATÓ KÉSŐBB |
| D12 | Küldési szolgáltató, feliratkozások és automatizmusok | A felhasználó későbbre hagyta; az érintett M16/M17 előtt véglegesítendő | KÉSŐBB |
| D13 | Chat elengedve, nem része a jelenlegi rendszernek | A ticketrendszer marad az ügyfélszolgálati kommunikáció felülete. Külön chat, real-time/polling, reconnect és külső provider nem készül; új igény esetén külön döntéssel vehető fel | ELENGEDVE |
| D14 | A már működő deploy-folyamatot kell használni új előfeltételek nélkül | Felhasználó: „nem kell bevezetni uj dolgokat ... ugy ahogy mar mukodott”. Meglévő hat SSH/path titok és opcionális ENV_DEVELOP; nincs új deploykapcsoló, environment-jóváhagyás, hostkulcstitok vagy Basic-belépő. A szerverenv nem módosul. A CI, csomagellenőrzés és adatmegőrző kódmentés megmarad; az új infrastruktúra/biztonsági előfeltételek halasztottak, nem késznek jelöltek | ELDÖNTVE, korábbi M01 aktiválási feltételeket felülírja |

További kötelező különbségek: a ZIP admin- és webshopadatai részben külön demótárak; az éles rendszer közös adatbázist/árszámítást használ. A termékimport eredménye a webshopban is megjelenik. A helyi képelőnézetből tényleges feltöltés lesz. A prototípus 27%-os áfája nem igazolja minden termék adóbesorolását.

## Tracking javaslat D11-hez

2026-10-13-án ellenőrzött szolgáltatói dokumentáció alapján, még nem kiválasztott vagy bekötött szolgáltatások. Első javaslat: GA4 + Microsoft Clarity, közös hozzájárulás-kezeléssel. Az admin üzleti számai továbbra is a Laravel rendelésadatbázisából jönnek.

| Jelölt | Szerep | Megvalósítási szempont |
| --- | --- | --- |
| Google Analytics 4 | Forgalmi források és ecommerce események/riportok | Egyedi Laravel-oldalon a termék/kosár/checkout/vásárlás eseményeket külön be kell kötni; [GA4 ecommerce](https://support.google.com/analytics/answer/14430645?hl=en) |
| Microsoft Clarity | Hőtérképek, munkamenet-visszajátszás és élő felvételek | Consent API/CMP és maszkolás szükséges a terv szerint; [funkciók](https://clarity.microsoft.com/), [élő felvételek](https://learn.microsoft.com/en-us/clarity/session-recordings/clarity-real-time), [maszkolás](https://learn.microsoft.com/en-us/clarity/setup-and-installation/clarity-masking) |
| Matomo | Alternatív analitikai alap ecommerce eseményekkel és konfigurálható tracking-hozzájárulással | Saját üzemeltetés vagy szolgáltatói csomag felmérendő; [tracking API](https://developer.matomo.org/guides/tracking-javascript), [hozzájárulás](https://developer.matomo.org/guides/tracking-consent) |

A részletes méréshez eseményszótár, szolgáltatói fiók, megőrzési idő és jogosultságok szükségesek. M15 tartalmazza az éles bekapcsolás előtti consent-ellenőrzést és tájékoztatást; az adatkezelési szöveg utólagos hozzáadása önmagában nem készíti el ezt az integrációt. A Clarity is külön hozzájárulási jelek kezelését dokumentálja: [szolgáltatói tájékoztató](https://clarity.microsoft.com/blog/clarity-cookie-consent-update/).

A vásárlás backendben rögzített üzleti esemény és a külső viselkedési analitika külön adatfolyam. Külső eseménybe nem kerül jelszó, token, email/cím vagy tetszőleges űrlapszöveg; partnerárak és fiókadatok a replay-ben maszkolandók. A hozzájárulást megtagadó és blokkoló böngészők miatt a részletes analitika nem azonos az összes szerveroldali rendelés számával. A szükséges működési naplózás megmarad.

## Kiadási rend minden nagyobb ponthoz

**Fejlesztés -> ellenőrzés -> önálló commit -> develop integráció/push -> staging deploy -> staging próba -> checklist státuszfrissítés -> következő pont.**

- A munkának egy új checkoutból telepíthető állapotban kell lezárulnia. Migráció, asset, lockfájl, környezeti előfeltétel és dokumentáció ugyanahhoz a kiadáshoz tartozik.
- Az M01 előtti workflow már develop pushra deployol, de nincs külön tesztkapuja, SSH hostellenőrzése ki van kapcsolva, és nem igazolt a rollback. Ezek M01 feladatok, nem kész képességek.
- Csak a mérföldkő fájljait commitoljuk; .env, mentés, ZIP és .codex-inspect nem kerülhet véletlenül a commitba.
- A K1-K3 kapuk után a pont KIADÁSRA KÉSZ. K4 után STAGINGEN. K5-K6 után STAGINGEN ELLENŐRZÖTT.
- A commit nem egyenlő a deployjal; a sikeres workflow nem egyenlő a működő felhasználói folyamattal.
- A következő mérföldkő ne igényelje nem commitolt helyi fájl jelenlétét. Hiányzó titok/szerverhozzáférés esetén pontos blokk és folytatási pont rögzítendő.
- Éles kiadás külön állapot és esemény. Egy stagingre kész csomag nem automatikus élesítési felhatalmazás.
- A checklist státuszát a következő tényleges fejlesztési commit részeként tartjuk naprakészen; külön dokumentációs ág, docs-only commit vagy kiadási napló nem készül.
- A M00 dokumentációs pont, nincs hozzá alkalmazásdeploy. M19 termékelérhetőség és M20 ajánlatmegjelenítés a D06-D08 döntésekkel az elfogadott scope része. A korábban felvetett mennyiségi készletmotor és új akció-árazómotor későbbi bővítés; most nem készítendő.

## Mérföldkőjegyzék

| Pont | Téma | Állapot |
| --- | --- | --- |
| M00 | ZIP/lista összevetés és követhető munkalista | DOKUMENTÁCIÓ ELKÉSZÜLT |
| M01 | Staging és Git kiadási alap | STAGINGEN; nyilvános és partner/admin próba sikeres, D14 szerinti halasztások nyitva |
| M02 | Kritikus biztonsági és rendelési javítások | STAGINGEN ELLENŐRZÖTT; M02.01-M02.08 kész |
| M03 | Laravel és futtatókörnyezet korszerűsítése | STAGINGEN ELLENŐRZÖTT |
| M04 | Közös backend, jogosultság és háttérfeldolgozás | STAGINGEN ELLENŐRZÖTT |
| M05 | ZIP-alapú designrendszer és három layout | STAGINGEN ELLENŐRZÖTT |
| M06 | Terméktörzs, kategóriafa és webshopos szűrők adminja | STAGINGEN ELLENŐRZÖTT |
| M07 | Egységes árképzés és admin ármátrix | STAGINGEN ELLENŐRZÖTT |
| M08 | Webshop, katalógus és keresés a v6 kinézetével | STAGINGEN ELLENŐRZÖTT |
| M09 | Adatbázisos kosár és teljes rendelési folyamat | STAGINGEN ELLENŐRZÖTT |
| M10 | Belépés, regisztrációkérés és partnerfiók | STAGINGEN ELLENŐRZÖTT |
| M11 | Admin rendeléskezelés és kézi rendelésfelvitel | STAGINGEN ELLENŐRZÖTT |
| M12 | Admin partnerek és felhasználók teljes kezelése | STAGINGEN ELLENŐRZÖTT |
| M13 | Excel-import és minden üzleti táblázat exportja | STAGINGEN ELLENŐRZÖTT |
| M14 | Hero, úszó banner, tartalmi oldalak és PPT | STAGINGEN ELLENŐRZÖTT |
| M15 | Dashboard, top termékek, kosár- és aktivitásfigyelés | STAGINGEN ELLENŐRZÖTT |
| M16 | Emailközpont és hírlevél | STAGINGEN ELLENŐRZÖTT |
| M17 | Kosárelhagyás és időzített automatizmusok | STAGINGEN ELLENŐRZÖTT |
| M18 | Ticket és belső üzenőfal | STAGINGEN ELLENŐRZÖTT |
| M19 | Termékelérhetőségi státuszok | TERVEZETT |
| M20 | Aktuális akciók és partneri ajánlatok megjelenítése | TERVEZETT |
| M21 | Teljes átállás, teljesítmény és üzemeltetési átadás | TERVEZETT |

## M00. Összevetés és folytatható munkalista

- [x] M00.01 ZIP képernyői, CSS-e, működése és korlátai összevetve a felhasználói listával.
- [x] M00.02 ZIP-többletek, lista-többletek és üzleti eltérések rögzítve.
- [x] M00.03 Stabil azonosítós mérföldkövek, kiadási kapuk és folytatási napló létrehozva.
- [x] M00.04 Checklist belső hivatkozásainak, feladatazonosítóinak és lefedettségének ellenőrzése: 328 egyedi checkbox, 176 fejlesztési feladat, 126 kiadási kapu, 21 vizuális csoport; nincs duplikált azonosító vagy feloldatlan feladathivatkozás.
- [x] M00.05 Dokumentációs commit rögzítve a következő engedélyezett Git-műveletkor; alkalmazásdeploy nem szükséges.

## M01. Staging és Git kiadási alap

Állapot: STAGINGEN (D14 szerinti kiadás). Függőség: M00. Nyilvános HTTP-próba sikeres, a teljes bejelentkezett próba még nyitott.
Forrás: L: staging, deploy, infrastruktúra; K: meglévő develop workflow.
Stagingen bemutatható eredmény: Az aktuális Laravel-verzió tiszta checkoutból ellenőrizhetően stagingre települ.

D14 (2026-10-14) a korábbi aktiválási kaput felülírja: a már működő stagingre a meglévő beállításokkal szabad kiadni. M01.02 új izoláció/védelem, M01.04 rögzített hostkulcs és M01.09 valódi DB/média-visszaállítás külön halasztott munka, nem előfeltétele ennek a kiadásnak. Nem pipálhatók ki pusztán a sikeres deploy miatt. A kiadási kapuk ellenőrzési köre a mostani, D14 szerinti csomag; a halasztott munkák nem vezethetők be ismét automatikus blokkolóként.

- [ ] M01.01 A jelenlegi develop deploy célkörnyezetének ellenőrzése: domain, public/app könyvtár, PHP CLI/web, adatbázis, build és titokkezelés; az éles környezet elkülönítése.
- [ ] M01.02 Staging külön adatbázis, kulcs, session/cookie, média, queue; anonimizált tesztadat, tesztcímzettes email, hozzáférés-védelem és noindex.
- [x] M01.03 CI ellenőrző job létrehozása dev függőségekkel és tesztekkel a deploy előtt; produkciós csomag külön, lockfájlokból építve.
- [ ] M01.04 A workflow SSH hostkulcs-ellenőrzésének bekapcsolása rögzített megbízható hostkulccsal; deploy titkok ne kerüljenek logba.
- [ ] M01.05 Deploy concurrency, félbeszakadt kiadás kezelése, előző build megőrzése és visszaállítási leírás; feltöltések és APP_KEY megőrzése.
- [x] M01.06 Git/release folyamat rögzítése: codex/m01-... munkaág, ellenőrzés, develop integráció, staging deploy; védett éles kiadási út előkészítése.
- [x] M01.07 Deploycsomagból ZIP-ek, .codex-inspect, lokális referenciák és nem szükséges fejlesztési anyagok kizárása; csak célzott fájlok stage-elése.
- [x] M01.08 Build/commit azonosító ellenőrizhető rögzítése; HTTP smoke tesztek mellett bejelentkezett tesztpartner/admin útvonalak ellenőrzése.
- [ ] M01.09 Adatbázis- és feltöltésmentés visszaállításának próbaköre külön tesztkörnyezetben; a siker dokumentálása.

Ellenőrzés: Üres checkout build; kontrollált hibás teszt megállítja a deployt; staging nem küld ügyfélnek; mentés/visszaállítás és HTTP smoke ellenőrzés.
Adat/deploy: Csak staging próbaadat. Destruktív adatbázis-változás nincs.

2026-10-13 helyi részállapot: M01.03 és M01.06 implementálva/dokumentálva. M01.02 védelem és email-sink tesztelt; M01.04 hostellenőrzés, M01.05 zárolás/kódmentés, M01.07 csomagkizárás és M01.08 SHA/HTTP smoke kódja megvan, de a teljes feladatok távoli ellenőrzése/stage-elése még hátravan. A deploy új engedélyező változója alapból kikapcsolt. Részletes beállítások és helyreállítás: [DEPLOYMENT.md](../DEPLOYMENT.md).

### M01 kiadási kapuk

- [ ] M01.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [ ] M01.K2 Migráció, környezeti előfeltétel, feature flag, visszaállítás és használati változás dokumentálva; adat megőrzése ellenőrizve.
- [x] M01.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M01.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [ ] M01.K5 A kiadott SHA stagingen ellenőrizve a fenti folyamattal; UI esetén mobil/tablet/desktop is; nincs nyitott kiadást blokkoló hiba.
- [ ] M01.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, következő feladat és ismert korlát megadva.

Kiadási rekord: commit = `74d54f5296531370873f9182d10ead0b003d86ea`; workflow = [#36](https://github.com/sykolos/eb-webshop/actions/runs/38574327623); staging próba = három nyilvános GET HTTP 200; bejelentkezett próba és külön HTTP SHA-fejléc nincs igazolva.

## M02. Kritikus biztonsági és rendelési javítások

Állapot: FOLYAMATBAN. Függőség: M01; M02.01-M02.04 stagingen, további M02 pontok nyitva.
Forrás: L: biztonság; K: konkrét kódhiányok.
Stagingen bemutatható eredmény: A régi felület a javított hozzáférésekkel és rendelésmentéssel tovább használható.

- [x] M02.01 OrderPolicy és tulajdonosszűrés a fiókbeli rendelésrészletnél és PDF-letöltésnél; cím- és kosárhozzáférések átvizsgálása.
- [x] M02.02 Nyilvános /clear eltávolítása; üzemeltetési cache-törlés csak CLI/deploy folyamatból.
- [x] M02.03 Kosármennyiség, termékazonosító és kiszerelés validációja; a kliens q/m paramétere ne dönthesse el az üzleti mennyiséget.
- [x] M02.04 Checkout tranzakció és ismételt beküldés elleni védelem; hiányzó számlázási adatok és megszűnt termék kezelése.
- [x] M02.05 Státuszmódosítás engedélyezett értékeinek és átmeneteinek validálása; a teljes új munkafolyamat M11-ben készül.
- [x] M02.06 Login, regisztrációkérés, jelszóreset, email és kapcsolat végpontok próbálkozáskorlátja; logout session-érvénytelenítés és egységes jelszóvalidáció.
- [x] M02.07 Duplikált cart.count és hibás/hiányzó kosár route-controller összerendelések rendezése a hívó felületekkel együtt.
- [x] M02.08 Kritikus biztonsági regressziós tesztek; publikus termékfeedek ár- és `special_prices` szivárgásának javítása, admin- és címjogosultsági regressziók.

Ellenőrzés: A partner nem érheti el B rendelését/PDF-jét; hibás mennyiség elutasítva; dupla beküldés egy rendelés; részleges mentés visszagörgetve.
Adat/deploy: Új egyedi beküldési kulcs esetén kompatibilis migráció; meglévő rendelések megőrzése.

### M02 kiadási kapuk

- [x] M02.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek.
- [x] M02.K2 Nincs migráció, új környezeti előfeltétel vagy feature flag; a meglévő adatok megőrzése változatlan.
- [x] M02.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M02.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; SHA és futásazonosító ellenőrizve.
- [x] M02.K5 A kiadott SHA stagingen ellenőrizve; publikus smoke és feed-válaszok zöldek, nincs kiadást blokkoló hiba.
- [x] M02.K6 A checklist státusza frissítve; külön kiadási napló nem készül; következő feladat M03.01.

Kiadási rekord M02.03 részkiadás: commit = `ee86f8ebe8c5b2e947ea2fc8c3fd7ab39a63394f`; feature workflow = [#48](https://github.com/sykolos/eb-webshop/actions/runs/38588072011), verify sikeres; develop workflow = [#49](https://github.com/sykolos/eb-webshop/actions/runs/38588688231), verify/deploy sikeres; staging smoke = `/`, `/contact`, `/login` HTTP 200. A helyi PHPUnit a hiányzó PDO SQLite-driver miatt nem futott le, a CI izolált SQLite ellenőrzése volt az alkalmazásellenőrzés. M02 közös K1-K6 kapui a további M02 pontok miatt nyitva maradnak.

Kiadási rekord M02.04: commit = `a54ac7aceb605924e994874f754697b1801306b9`; feature workflow = [#54](https://github.com/sykolos/eb-webshop/actions/runs/38591732597), sikeres; develop/staging workflow = [#55](https://github.com/sykolos/eb-webshop/actions/runs/38591973104), verify és deploy sikeres; staging smoke = `/`, `/contact`, `/login` HTTP 200. A migráció meglévő rendeléseket megőriz, új környezeti változó vagy deploy-kapcsoló nem kell. A helyi PHPUnit a hiányzó PDO SQLite-driver miatt nem futott le, a CI ellenőrizte a checkout regressziókat és a migrációt. M02 közös K1-K6 kapui M02.05-M02.08 miatt továbbra nyitva maradnak.

Kiadási rekord M02.05: commit = `c641a9043ae825c3d178adc97afc7b904845dcc7`; feature workflow = [#58](https://github.com/sykolos/eb-webshop/actions/runs/38614946203), sikeres; develop/staging workflow = [#59](https://github.com/sykolos/eb-webshop/actions/runs/38615137385), verify és deploy sikeres; staging smoke = `/`, `/contact`, `/login` HTTP 200. A státuszgráf nem módosít adatbázissémát vagy környezeti beállítást. A helyi célzott PHPUnit a hiányzó PDO SQLite-driver miatt nem tudott adatbázist indítani, a CI ellenőrzése sikeres. M01.08 élő partner/admin staging-belépési próba továbbra külön nyitott átadási pont.

Kiadási rekord M02.06: commit = `cd0b7e316364f73fecf6722c12a25710602237e7`; feature workflow = [#62](https://github.com/sykolos/eb-webshop/actions/runs/38616756365), sikeres; develop/staging workflow = [#63](https://github.com/sykolos/eb-webshop/actions/runs/38616911958), verify és deploy sikeres; staging smoke = `/`, `/contact`, `/login` HTTP 200. Nincs migráció, új környezeti változó vagy üzleti adatváltozás. A célzott helyi PHPUnit a hiányzó PDO SQLite-driver miatt nem tudott adatbázist indítani, a CI ellenőrzése sikeres. M01.08 élő partner/admin staging-belépési próba továbbra külön nyitott átadási pont.

Kiadási rekord M02.07: commit = `e1a6780ffe65118581a328672b372dbcf77006c6`; feature workflow = [#71](https://github.com/sykolos/eb-webshop/actions/runs/38619870845), sikeres; develop/staging workflow = [#72](https://github.com/sykolos/eb-webshop/actions/runs/38620052459), verify és deploy sikeres; staging smoke = `/`, `/contact`, `/login` HTTP 200. Nincs migráció, új környezeti változó vagy üzleti adatváltozás. A Vite production build és a route-list sikeres; a célzott helyi PHPUnit a hiányzó PDO SQLite-driver miatt nem tudott adatbázist indítani, a CI ellenőrzése sikeres. M01.08 élő partner/admin staging-belépési próba továbbra külön nyitott átadási pont.

M02.05 stagingen: az admin rendelés-státusz végpontja csak a négy meglévő magyar státuszt és a régi `pending` adatértéket fogadja el. Az engedélyezett átmenetek: `függőben` -> `feldolgozás` vagy `törölve`; `feldolgozás` -> `kiszállítva` vagy `törölve`; a lezárt állapotokból nincs visszalépés. Az aktuális státusz változatlanra küldése idempotens marad. Az admin nézet csak a megengedett következő státuszokat kínálja, de a végső ellenőrzés a controllerben történik.

Módosított fájlok: `app/Models/Order.php`, `app/Http/Controllers/OrderController.php`, `tests/Feature/ExampleTest.php`, ez a checklist. Nincs migráció, környezeti változás vagy új üzleti státusz; a teljes rendeléskezelési workflow továbbra M11 feladata. PHP lint és `git diff --check` sikeres. A célzott PHPUnit a gépen hiányzó PDO SQLite-driver miatt adatbázis-inicializálásnál megállt; a feature és staging CI ellenőrzése sikeres.

M02.04 kód kész, kiadás előtt: a `CheckoutService` a rendelés- és tételmentést egy adatbázis-tranzakcióban végzi, a friss termékből és a meglévő felhasználói/termékköri árlogikából számol. A checkout űrlap UUID alapú, egyedi beküldési kulcsot kapott; ismételt beküldés ugyanazt a rendelést adja vissza, új email/PDF értesítés nélkül. A számlázási adatok és a szállítási cím felhasználói tulajdonosa kötelező; hiányzó számlázási adat vagy törölt termék validációs hibát ad rendelés létrehozása nélkül. A kiszerelés továbbra is a termék adatbázisából származik, nem a session/kliens q/m értékeiből.

Módosított fájlok: `app/Services/CheckoutService.php`, `app/Http/Controllers/Checkout.php`, `app/Http/Controllers/PagesController.php`, `resources/views/pages/checkout.blade.php`, `database/migrations/2026_10_14_160000_add_checkout_token_to_orders_table.php`, `tests/Feature/ExampleTest.php`. A migráció nullable, egyedi `orders.checkout_token` mezőt ad hozzá, így meglévő rendelések megmaradnak; új környezeti változó és deploy-kapcsoló nem szükséges. PHP lint és `git diff --check` sikeres. A célzott helyi PHPUnit nem futott: a gépen hiányzik a PDO SQLite-driver; CI-ellenőrzés szükséges.

### 2026-10-14: M02.06 auth- és email-végpontok védelme staging kiadás

- A `RouteServiceProvider` név szerinti limiteket ad a loginhoz, admin regisztrációhoz, email-verifikáció újraküldéséhez, jelszó-visszaállítás kéréséhez és végrehajtásához, belső jelszócseréhez és a kapcsolatűrlaphoz. A kulcs email + IP, illetve felhasználó + IP kombináció, ezért a limit nem csak egy globális számláló.
- Az új `PasswordRules` osztály egységesen legalább 8 karakteres, megerősített jelszót ír elő a regisztrációhoz, jelszó-visszaállításhoz és jelszócseréhez. A regisztrációs űrlap a Laravel `confirmed` konvencióját használja.
- Logoutkor az authentikáció megszűnik, a session teljesen érvénytelenül, és új CSRF-token készül. A valódi partner-regisztrációs kérelem workflow továbbra M10, itt csak a jelenlegi admin regisztrációs út védelme változik.
- Új Feature tesztek fedik le az öt hibás login utáni 429 választ, a logout utáni vendégállapotot és a rövid regisztrációs jelszó elutasítását. PHP lint, route-list és `git diff --check` sikeres; a helyi PHPUnit a hiányzó PDO SQLite-driver miatt adatbázis-inicializálásnál megállt.
- Módosított fájlok: `app/Support/PasswordRules.php`, `app/Providers/RouteServiceProvider.php`, `app/Http/Controllers/AuthController.php`, `routes/web.php`, `resources/views/pages/register.blade.php`, `tests/Feature/ExampleTest.php`, ez a checklist. Nincs migráció, új környezeti változó vagy üzleti adatváltozás.
- A feature [#62](https://github.com/sykolos/eb-webshop/actions/runs/38616756365) és a develop/staging [#63](https://github.com/sykolos/eb-webshop/actions/runs/38616911958) workflow sikeres. A staging `/`, `/contact`, `/login` végpontjai HTTP 200-at adtak.
- Állapot: M02.06 stagingen ellenőrzött; M02 közös K1-K6 kapui a további M02 pontok miatt nyitva. Következő konkrét fejlesztési feladat: M02.07 duplikált cart.count és hibás/hiányzó kosár route-controller összerendelések. M01.08 élő partner/admin login-próba dedikált tesztadatokkal továbbra nyitott.

### 2026-10-14: M02.07 kosár route-controller tisztítás staging kiadás

- A duplikált `/cart/count` route megszűnt; a név most kizárólag a `CartController::cartCount` metódusra mutat. A régi `remove-from-cart/{key}` paraméterneve összhangba került a controllerrel.
- A kosár és gyorskosár JavaScriptjei a layout által kiadott route-adatokat használják, a gyorskosár hozzáadás pedig a Blade-ben generált named route URL-jét kapja. A jelenlegi URL-formátumok kompatibilitás miatt megmaradtak.
- Új Feature teszt ellenőrzi az egyetlen controlleres `cart.count` route-ot és a sessionben tárolt mennyiségek összegét. PHP lint, route-list, `git diff --check` és Vite production build sikeres. A helyi PHPUnit a hiányzó PDO SQLite-driver miatt nem tudott adatbázist indítani.
- A feature CI [#71](https://github.com/sykolos/eb-webshop/actions/runs/38619870845) sikeres; az első #70 futás a Laravel 9 route-gyűjtemény tesztben hibázott, javítás után újra zöld lett. A develop/staging [#72](https://github.com/sykolos/eb-webshop/actions/runs/38620052459) verify/deploy sikeres, a három smoke URL HTTP 200.
- Állapot: M02.07 stagingen ellenőrzött; M02 közös K1-K6 kapui a további M02.08 miatt nyitva. Következő konkrét fejlesztési feladat: M02.08 kritikus biztonsági regressziós tesztek és további kockázatok felvétele. M01.08 élő partner/admin login-próba dedikált tesztadatokkal továbbra nyitott.

## M03. Laravel és futtatókörnyezet korszerűsítése

Állapot: KIADÁSRA KÉSZ. Függőség: M02.
Forrás: L: Laravel refaktor, frissítések; K: Laravel 9.
Stagingen bemutatható eredmény: A meglévő webshop támogatott Laravel/PHP alapon működik stagingen.

- [x] M03.01 PHP, adatbázis, Composer és npm csomagkompatibilitási lista; célverzió Laravel 12.70/PHP 8.2.31, Node 20/Vite 8.3.4.
- [x] M03.02 Laravel 9 -> 12 frissítés, Composer platform és CI PHP összhangja; a futtatókörnyezet-váltás egy staging kiadásba rendezve.
- [x] M03.03 Dompdf 3, Intervention Image 3 + Laravel integráció, Sanctum 4 és PHPUnit/hibakezelési csomagok kompatibilitása; a régi imagecache és nem használt cookie-consent függőség kikerült, az API-hívások átvezetve.
- [x] M03.04 Vite 8/Node build kompatibilitás, npm lockfájl és függőségaudit; Bootstrap/jQuery teljes eltávolítása későbbi M05/M21 feladat, a legacy képernyők működése megmarad.
- [x] M03.05 Belépés, partnerár, kosár, checkout, PDF-komponens, admin route-ok, Blade-cache és deploy-csomag ellenőrzése; PHPUnit 11-re a tesztmetadata átvezetve. A helyi image-manager próba a gépen hiányzó GD miatt nem futott, a CI GD-vel épít és tesztel.
- [x] M03.06 Upgrade és visszaállítás dokumentálva: nincs migráció vagy új környezeti előfeltétel, rollback az előző kódcsomaggal azonos adatbázissémán végezhető; külön kiadási napló nem készül.

Ellenőrzés: A kritikus folyamatok a frissített stacken futnak; Composer/npm telepítés lockfájlból reprodukálható.
Adat/deploy: Migrációs hatás külön felmérve; framework rollbackhez a kompatibilis adatbázis-állapot megadva.

### M03 kiadási kapuk

- [x] M03.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek: PHPUnit 46 teszt/215 assertion, Vite build, npm audit 0, Composer validate, route-list, Blade-cache és diff-check.
- [x] M03.K2 Nincs migráció, új környezeti előfeltétel vagy feature flag; a lockfájlok reprodukálhatók, a meglévő üzleti ár-/kosár-/rendelésadatok és session-viselkedés megőrzendő; rollback az előző kódcsomaggal kompatibilis.
- [x] M03.K3 Diff ellenőrizve, célzott alkalmazás-, konfiguráció-, teszt- és lockfájlok; reprodukálható, önállóan commitolható állapot.
- [x] M03.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [x] M03.K5 A kiadott SHA stagingen ellenőrizve a fenti folyamattal; a publikus smoke útvonalak és a build assetek betöltése sikeres, nincs nyitott kiadást blokkoló hiba.
- [x] M03.K6 A checklist státusza STAGINGEN ELLENŐRZÖTT; külön kiadási napló nem készült.

Kiadási rekord: commit = `c82965abf24753c309450710534fcdfae1e2abcc`; feature workflow = [#79](https://github.com/sykolos/eb-webshop/actions/runs/38627176724), develop/staging workflow = [#80](https://github.com/sykolos/eb-webshop/actions/runs/38627334532). A staging `/`, `/contact`, `/login` útvonalai HTTP 200-at adtak; a főoldalról hivatkozott CSS és JavaScript build assetek HTTP 200-at adtak. Az admin munkamenet a `/adminpanel` oldalon betöltött, a dashboard és admin navigáció látható.

## M04. Közös backend, jogosultság és háttérfeldolgozás

Állapot: STAGINGEN ELLENŐRZÖTT. Függőség: M03.
Forrás: L: service layer, logging, queue, scheduler, jogosultság.
Stagingen bemutatható eredmény: Működő szerepkör-ellenőrzés, háttérben küldött tesztértesítés és ellenőrzött időzítő.

- [x] M04.01 Form Request, Policy/Gate, service és controller felelősségek mintája; üzleti modulok saját mérföldkövükben költöznek át.
- [x] M04.02 Technikai szerepkör/művelet jogosultsági mátrix D04 szerint; az `administrator`/`partner` értékek kompatibilitási leképezések, a konkrét munkakörnevek később konfigurálhatók.
- [x] M04.03 Admin kétlépcsős belépés, helyreállítás és jogosultságváltozás audit; partnerjóváhagyás önmagában nem ad adminjogot.
- [x] M04.04 Auditnapló fontos módosításokról; strukturált hibalog request azonosítóval; érzékeny mezők maszkolása, logrotáció és hibariasztás.
- [x] M04.05 Database queue előkészítés, felügyelt worker újraindítási jelzés, timeout/retry/backoff és sikertelen feladatok kezelése.
- [x] M04.06 Email/PDF háttérmunka commit után; sikertelen küldés ne tegye sikertelenné a már mentett rendelést; ismétlésvédelem.
- [x] M04.07 Scheduler cron, átfedő futások kizárása és heartbeat; környezetenként külön heartbeat.
- [x] M04.08 Egységes privát feltöltés- és letöltéskezelés: fájltípus, MIME, méret, generált név, bejelentkezéshez kötött aláírt URL és lejárat.
- [x] M04.09 Deploy után worker restart jelzés; queue és migráció verziókompatibilis release-sorrend.

Ellenőrzés: Tiltott szerepkör 403/404; tesztjob lefut és hibából újrapróbálható; cron futása mérhető; emailhiba mellett rendelés megmarad.
Adat/deploy: Jogosultságok és audit/queue táblák bővítő migrációi, meglévő adminok ellenőrzött átvitele.

### M04 kiadási kapuk

- [x] M04.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [x] M04.K2 Migráció, környezeti előfeltétel, feature flag, visszaállítás és használati változás dokumentálva; adat megőrzése ellenőrizve.
- [x] M04.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M04.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [x] M04.K5 A kiadott SHA stagingen ellenőrizve a fenti folyamattal; a publikus smoke útvonalak és az admin biztonsági felület betöltött, nincs nyitott kiadást blokkoló hiba.
- [x] M04.K6 A checklist státusza STAGINGEN ELLENŐRZÖTT; külön kiadási napló nem készült, következő feladat M05.01.

Kiadási rekord: a korábbi M04 staging-próbák közben a deploy recovery és a legacy migrációs kompatibilitás javítása több lépésben történt (`94ccb84`, `3900620`, `7e69fa0`, `9cc6ffa`, `55b737a`, `b5e40ff`, `5772047`, `e23b9fe`). A végleges M04 kiadás commitja = `ec5da27f95a1d3832231d5d9402354c1ce934aa6`; develop/staging workflow = [#99](https://github.com/sykolos/eb-webshop/actions/runs/38636655012), verify és deploy sikeres. Helyi ellenőrzés: PHPUnit 51 teszt / 225 assertion, Vite build, PHP lint és diff-check sikeres. A staging `/`, `/contact`, `/login` útvonalai HTTP 200-at adtak; a bejelentkezett `/adminpanel` dashboard, admin navigáció és `/adminpanel/security/2fa` oldal betöltött. A legacy `audit_logs` és `order_notification_deliveries` migrációk meglévő/eltérő sémára kompatibilisek lettek; a meglévő rendelési adatok megőrzése és a staging szolgáltatás helyreállása ellenőrzött. Következő konkrét feladat: M05.01 ZIP-alapú designrendszer és három layout.

## M05. ZIP-alapú designrendszer és három layout

Állapot: STAGINGEN ELLENŐRZÖTT. Függőség: M04, M03.
Forrás: Z: CSS, shop/admin/customer komponensek; L: új design, Figma, mobil, Bootstrap/jQuery kivezetés.
Stagingen bemutatható eredmény: Stagingen védett komponensbemutató és webshop/fiók/admin váz látható a v6 stílusában.

- [x] M05.01 A ZIP referencia és a staging preview kulcsképernyői ellenőrizve; desktop képernyőképek készültek, a reszponzív töréspontok és eltérések a [M05 design handoffban](M05-DESIGN-HANDOFF.md) rögzítve.
- [x] M05.02 Piros-grafit-fehér arculat, Poppins, ipari hero, termékkártyák, admin oldalsáv és világos munkaterület átültetve.
- [x] M05.03 Webshop 1240 px körüli keret, térközök, tipográfia, kártyák, gombok, státuszok és űrlapállapotok elkészültek.
- [x] M05.04 Blade komponensminta elkészült: fejléc, footer, termékkártya/árállapot, mező, tábla, státusz, valamint üres/hiba/töltés állapot.
- [x] M05.05 Alpine CSP build és natív Vite belépési pont elkészült; az új layoutok Bootstrap/jQuery/CDN nélkül működnek.
- [x] M05.06 Webshop, partnerfiók és admin layout, mobil menü, oldalsáv, fókuszállapot és érintési célok elkészültek.
- [x] M05.07 Figma-ready komponens-, token-, képernyő- és variáns-dokumentáció elkészült; az implementációval egyező mobil és hibás állapotok rögzítve.
- [x] M05.08 A preview admin middleware mögött érhető el; mintaadat és partnerár nem került a publikus vásárlói felületre.
- [x] M05.09 Az új layoutok izolált route-okon működnek; a régi, még nem portolt oldalak és üzleti logika megmaradtak.

Ellenőrzés: Referencia és Laravel képernyőképek összevetése; nincs átfedés, túlcsordulás vagy hiányzó kép; fókusz és reszponzivitás ellenőrzött.
Adat/deploy: Adatmigráció nem szükséges; feature flag/route visszaállításával az új váz lekapcsolható.

### M05 kiadási kapuk

- [x] M05.K1 A PHPUnit, Vite build, PHP lint, Blade cache, route-list és diff-check sikeres; a staging UI previewk betöltöttek.
- [x] M05.K2 Nincs migráció vagy új környezeti előfeltétel; a feature route-ok admin middleware mögött vannak, visszaállításuk a legacy layout érintetlenül hagyásával lehetséges.
- [x] M05.K3 A diff célzott, a release reprodukálható, a ZIP és inspect anyag nem került a csomagba.
- [x] M05.K4 `bb4022d` commit developra pusholva; GitHub Actions [#103](https://github.com/sykolos/eb-webshop/actions/runs/38640449773) verify és deploy sikeres.
- [x] M05.K5 A kiadott SHA stagingen ellenőrizve: védett komponensgaléria, webshop hero, partnerfiók és admin preview betöltött; a desktop screenshot-próba és a reszponzív CSS/Alpine útvonal ellenőrzött, nincs blokkoló hiba.
- [x] M05.K6 A checklist és a folytatási handoff frissítve; következő feladat M06.01, ismert korlátként a tényleges üzleti termék/kategóriaadatok bekötése marad.

Kiadási rekord: commit = `bb4022dbcdf66b84ab14e74f49fae60a9deea339`; develop/staging workflow = [#103](https://github.com/sykolos/eb-webshop/actions/runs/38640449773), verify és deploy sikeres. Helyi ellenőrzés: `npm run build`, PHPUnit 51 teszt / 225 assertion, PHP lint, route-list, Blade view cache és `git diff --check` sikeres. Stagingen a védett `/adminpanel/design-system`, `/adminpanel/design-system/shop`, `/adminpanel/design-system/account` és `/adminpanel/design-system/admin` previewk betöltöttek; vizuális desktop screenshot-próba készült. Nincs migráció és nincs új környezeti előfeltétel. A legacy Bootstrap/jQuery bundle még megmarad a nem portolt oldalakon; az új V6 layoutokban nincs használatuk. Következő konkrét feladat: M06.01 terméktörzs, kategóriafa és elérhetőségi státuszok.

## M06. Terméktörzs, kategóriafa és webshopos szűrők adminja

Állapot: STAGINGEN ELLENŐRZÖTT. Függőség: M04, M05.
Forrás: Z: product-editor, taxonomy, admin-workflows; L: kategória/alkategória, termékek, szűrők.
Stagingen bemutatható eredmény: Az adminban létrehozott vagy szerkesztett termék és kategória tartósan mentődik, a régi webshop kompatibilis marad.

- [x] M06.01 Termékmodell bővítése: cikkszám, slug, név, leírás, gyártó, kategória, alapegység, csomagszorzó, csomagnév, publikálási állapot.
- [x] M06.02 Tetszőleges mélységű kategóriafa D02 szerint, sorrenddel és aktív állapottal; nincs két szintre korlátozás, mély fában keresés/összecsukás/breadcrumb.
- [x] M06.03 Saját maga vagy leszármazottja alá mozgatás tiltása; terméket vagy gyermeket tartalmazó kategória törlése tiltott; teljes részfa áthelyezése konzisztens.
- [x] M06.04 Szűrődefiníciók, értékek, aktív állapot és kategória-hozzárendelés; minden ős releváns szűrőinek öröklődése, duplikáció nélkül. Ez nem vezet be új öröklődő kedvezményszabályt.
- [x] M06.05 Termékszerkesztő fülek: alapadatok, jelenlegi kiszerelés, műszaki adatok, képek/dokumentumok; külön publikálási állapot és D06 szerinti elérhetőségi státusz, mennyiségi készletmező nélkül.
- [x] M06.06 Valódi kép/PDF feltöltés, sorrend, előnézet, törlés és privát/publikus hozzáférés; a ZIP helyi előnézetének kiváltása.
- [x] M06.07 Mennyiségi egységek adminja; használt egység törlési védelme, db/m és egyéb meglévő egységek migrációs szabálya.
- [x] M06.08 Terméklista keresés/szűrés/lapozás, archiválás; törlés ne bontsa meg a történeti rendeléseket.
- [x] M06.09 Meglévő cikkszámok, kategóriák és csomagolások adatminőség-ellenőrzése; ismeretlen értékből ne legyen kitalált adat.
- [x] M06.10 Admin termék és későbbi webshop ugyanazt a terméktörzset használja; termék- és kategória-service a nagy controllerekből.

Ellenőrzés: Legalább öt szintű kategóriafa, részfamozgatás, ciklusvédelem és szűrőöröklés; tiltott törlés; fájlfeltöltés; elérhetőségi állapot mentése; régi katalógus regresszió.
Adat/deploy: Bővítő migráció, visszakövethető kategória/egység mapping, SKU ütközési riport.

### M06 kiadási kapuk

- [x] M06.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [x] M06.K2 Migráció, környezeti előfeltétel, feature flag, visszaállítás és használati változás dokumentálva; adat megőrzése ellenőrizve.
- [x] M06.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M06.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [x] M06.K5 A kiadott SHA stagingen ellenőrizve: terméklista, kategóriafa, egységadmin és új termékűrlap betöltött; a három termékfül működött; a 390/768/1440px viewportokban nem volt vízszintes túlcsordulás.
- [x] M06.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, következő feladat és ismert korlát megadva.

Kiadási rekord: a kezdeti `06e72b6` / [#106](https://github.com/sykolos/eb-webshop/actions/runs/38643592087) deploy a legacy `products.id` és az új `foreignId` eltérése miatt visszaállt. A kapcsolati oszlopok javítása után `17b6a67` / [#107](https://github.com/sykolos/eb-webshop/actions/runs/38644001602) sikeresen stagingre került, majd a Blade-kompatibilitási javítás `64c9fde` / [#108](https://github.com/sykolos/eb-webshop/actions/runs/38644448761) után a végleges `7dc67714bd621a42af1e970e8df524f1dbf68f7b` / [#109](https://github.com/sykolos/eb-webshop/actions/runs/38644876368) verify és deploy is sikeres lett. Helyi bizonyíték: M06 célteszt 6/6, teljes PHPUnit 57 teszt / 237 assertion, Vite build, PHP lint, route-list, Blade cache és diff-check sikeres. Stagingen a terméklista 280 termékkel, a 20 csomópontos kategóriafa, a 41 egység és az új termékűrlap betöltött; a termékfülek és az elérhetőségi státuszok láthatók, a reszponzív viewport-ellenőrzés nem talált vízszintes túlcsordulást. A legacy kapcsolatokat az alkalmazás explicit kezeli, nincs adatvesztés vagy új környezeti előfeltétel. Következő konkrét feladat: M07.01, a jelenlegi rugalmas partner-/termék-/termékköri árképzés regressziós rögzítése.

## M07. Egységes árképzés és admin ármátrix

Állapot: KIADÁSRA KÉSZ. Függőség: M06; D01 eldöntve: meglévő árképzés megőrzése.
Forrás: Z: ármátrix és ármegjelenítés; L: refaktor/gyorsaság; K: meglévő árlogika.
Stagingen bemutatható eredmény: Az admin előnézet, a terméklista és a kosár ugyanarra a partnerre ugyanazt az árat adja.

- [x] M07.01 A jelenlegi rendszer ár- és szerkesztési szabályainak regressziós rögzítése: fix partnerár, összeadott kategória/extra kedvezmény, kerekítés; felhasználó/termék/termékkör kombinációk és globális/tömeges módosítások teljes megőrzése. A ZIP egymás utáni százalékszámítása nem kerül át.
- [x] M07.02 PricingService és kérésenként egyszer betöltött partner/kategória árkontextus; minden speciális partnerár automatikus betöltésének megszüntetése.
- [x] M07.03 Alapegységár, csomagár, nettó/áfa/bruttó és csomagszám egységes kezelése; lebegőpontos pénzszámítás helyett rögzített pontosság.
- [x] M07.04 Ármátrix partnernézet és terméknézet, keresés, mentetlen módosítás jelzése, mezővalidáció és mentés.
- [x] M07.05 Globális kategóriakedvezmény-módosítás hatáselőnézettel és megerősítéssel; fix/extra szabályok megőrzése az elfogadott logika szerint.
- [x] M07.06 Árváltozás audit és partnerenként elkülönített cache, célzott invalidáció; jogosulatlan ármezők ne jussanak a klienshez.
- [x] M07.07 Árazás bekötése a még régi felületekbe is; N+1 mérés és regressziós ártesztek.

Ellenőrzés: Konkrét fix/kategória/extra kombinációk; 1000 Ft + 10% + 5% példája; csomagszorzó és kerekítés; más partner ára nem szivárog.
Adat/deploy: Meglévő árak és szerkesztési lehetőségek migrációs összevetése; ugyanarra a bemenetre árváltozás nem megengedett. A mélyebb kategóriák nem írhatják át a régi megállapodásokat.

### M07 kiadási kapuk

- [x] M07.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [x] M07.K2 Migráció, környezeti előfeltétel, feature flag, visszaállítás és használati változás dokumentálva; adat megőrzése ellenőrizve.
- [x] M07.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M07.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [x] M07.K5 A kiadott SHA stagingen ellenőrizve: admin partner- és terméknézet, 280 termékes partner webshoplista és termékoldal betöltött; az ár minden felületen megjelent; nincs nyitott kiadást blokkoló hiba.
- [x] M07.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, következő feladat és ismert korlát megadva.

Kiadási rekord: commit = `d5e783274a3a7fb138ca5fce9493f4e32626e857`; workflow = [#110](https://github.com/sykolos/eb-webshop/actions/runs/38646424266), verify és deploy sikeres; staging próba = admin partner- és terméknézet, partner webshoplista és termékoldal betöltött. Helyi bizonyíték: M07 célteszt 5 teszt / 14 assertion, teljes PHPUnit 61 teszt / 249 assertion, Vite build, PHP lint, route-list, Blade cache és diff-check sikeres. A build a meglévő Bootstrap/jQuery függőségek miatt deprecation warningokat jelez; ezek M21 fokozatos kivezetési feladatai, M07 működését nem blokkolják. A staging ármátrix adminnézete 390, 768 és 1440 px viewporton is betöltött; a mért `scrollWidth` mindhárom méreten megegyezett a viewport szélességével, vízszintes túlcsordulás nem volt.

2026-10-15: M07 stagingen ellenőrzött. A `d5e7832` kiadásban a partnerenként elkülönített, fixpontos PricingService, a kategória + extra kedvezmény összeadása, a fix ár elsőbbsége, az áfa/csomagkalkuláció, az áradmin partner- és terméknézete, a célzott cache-invalidálás és az audit események éles útvonalra kerültek. A régi katalógus, ajánlott termékek, termékoldal, kosár, checkout és rendeléstörténet közös árforrást használ; a nyilvános feed továbbra nem ad speciális árakat.

## M08. Webshop, katalógus és keresés a v6 kinézetével

Állapot: KIADÁSRA KÉSZ. Függőség: M05, M06, M07; D03 eldöntve: ZIP szerinti nyilvános katalógus és partnerárak.
Forrás: Z: shop-experience; L: webshop, mobil, autocomplete, megtartott keresés, top termékek.
Stagingen bemutatható eredmény: Főoldalról kereséssel vagy kategóriával valódi termékhez jutunk, mobilon is.

- [x] M08.01 Főoldal: ZIP ipari hero, főmenü, kategóriakártyák, terméksávok, partnerfelhívás és footer; a banneradmin M14-ben kapcsolódik.
- [x] M08.02 Katalógus rács/lista, találatszám, rendezés, lapozás, kategória/alkategória, breadcrumb és mobil szűrők.
- [x] M08.03 Termékkártya és termékadatlap: kép, cikkszám, műszaki adatok, dokumentumok, jelenlegi kiszerelés, elérhetőségi státusz; vendégnek ár nélküli katalógus és regisztrációkérés, jóváhagyott partnernek saját ár és vásárlás. Árak a publikus HTML/JSON-ban sem lehetnek.
- [x] M08.04 Főmenü autocomplete névre/cikkszámra: debounce, minimum keresési hossz, találati limit, régi válasz eldobása, billentyűzet.
- [x] M08.05 Keresés/szűrők/rendezés/lap az URL-ben; visszalépés és termékből listára visszatérés megőrzi az állapotot.
- [x] M08.06 Gyorsrendelés oldal a ZIP szerint, cikkszámkereséssel és csomagszámmal; meglévő kosárhívás kompatibilis bekötése M09-ig.
- [x] M08.07 Kiemelt termékek kézi szerkesztése/sorrendje; top termék modul csak valódi adatra, a végleges statisztika M15-ben.
- [x] M08.08 Nincs találat, hibás URL, inaktív/törölt termék, kép- vagy dokumentumhiány kezelése; régi publikus URL-ek leképezése.
- [x] M08.09 DB keresés/index/lapozás mérése; képek méretezése, helyi font és az oldalhoz szükséges JS.

Ellenőrzés: Keresés -> szűrés -> adatlap -> vissza út; mobil navigáció; vendég/partner árjogosultság; kép/PDF linkek; teljesítménymérés.
Adat/deploy: Slug és URL mapping bővítő migrációval, régi URL-ek átirányítási jegyzéke.

### M08 kiadási kapuk

- [x] M08.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [x] M08.K2 Migráció, környezeti előfeltétel, feature flag, visszaállítás és használati változás dokumentálva; adat megőrzése ellenőrizve.
- [x] M08.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M08.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [x] M08.K5 A kiadott SHA stagingen ellenőrizve a fenti folyamattal; UI esetén mobil/tablet/desktop is; nincs nyitott kiadást blokkoló hiba.
- [x] M08.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, következő feladat és ismert korlát megadva.

Kiadási rekord: commit = `f9f3a39`; workflow = [GitHub Actions #117](https://github.com/sykolos/eb-webshop/actions/runs/38651616855), verify/deploy sikeres; staging = `https://ebtest.dekor26.hu`. Helyi bizonyíték: korábbi M08 célteszt 4 teszt / 17 assertion és teljes PHPUnit 66 teszt / 268 assertion sikeres; a legutóbbi JS-javítás után `npm run build`, PHP lint és `git diff --check` sikeres. A helyi PHPUnit újrafuttatása ezen a gépen blokkolt, mert a PHP CLI-ben nincs PDO SQLite driver; a #117 CI verify ezt a teljes tesztlépést sikeresen lefuttatta. Stagingen igazolva: menü egyszeri nyitása, 5 autocomplete találat ár nélkül, vendég termékoldal/JSON ár nélkül, partner termékoldal árral és kosárgombbal, főoldali hero, admin kiemelt sorrend, 390/768/1440 px vízszintes túlcsordulás nélkül. Új migrációk: `2026_10_15_130000_add_catalog_search_indexes`, `2026_10_15_140000_add_sort_order_to_recommended_products`; meglévő termék-, ár- és rendelési adatot nem törölnek. A buildben látható Sass/Bootstrap deprecation figyelmeztetések meglévő technikai adósságok, teljes Bootstrap/jQuery kivezetése M21.

## M09. Adatbázisos kosár és teljes rendelési folyamat

Állapot: KIADÁSRA KÉSZ. Függőség: M04, M07, M08; D05 eldöntve: jelenlegi mennyiség/fizetés/szállítás.
Forrás: Z: Basket, pénztár, sikeroldal; L: kosármentés, mennyiség, egyszerű hozzáadás, modal eltávolítás.
Stagingen bemutatható eredmény: Valódi partner terméket tesz kosárba, új belépés után megtalálja, és egyetlen konzisztens rendelést ad le.

- [x] M09.01 CartService és carts/cart_items adatmodell; felhasználói tulajdon, aktív kosár egyedisége, utolsó aktivitás, konkurens frissítések.
- [x] M09.02 Meglévő session kosár egyszeri és ismétlésbiztos átvitele; ZIP szerinti partnerhez kötött vásárlás, vendégnek belépési/regisztrációs felhívás; külön vendégkosár-bővítés nincs.
- [x] M09.03 Gyors hozzáadás listából/adatlapról/gyorsrendelésből; jelenlegi mennyiség/egység/szorzó és összevonás regressziós ellenőrzéssel, szerveroldali validációval; ZIP-ből új üzleti mennyiségkorlát nem kerül át.
- [x] M09.04 Tovább vásárlok modal eltávolítása; nem blokkoló siker/hiba értesítés, fejléc- és mobil kosárszámláló frissítése.
- [x] M09.05 Üres és feltöltött kosár, törlés, mennyiségmódosítás, árfrissítés, megszűnt termék, hibás hálózat és párhuzamos lapok kezelése.
- [x] M09.06 CheckoutService: címválasztás, fizetési/szállítási mód, megjegyzés, árazatlan szállítólevél, ellenőrzés és elfogadás.
- [x] M09.07 Backend újraszámítás, tranzakció és dupla beküldés elleni DB védelem; termék/ár/cím/kiszerelés/áfa pillanatkép a rendelésben.
- [x] M09.08 Csak sikeres rendelésmentéskor záródjon a kosár; háttérértesítés, sikeroldal és rendeléslink.
- [x] M09.09 Rendelésszám és PDF ne tartalmazzon rögzített demóévet; régi rendelések hiányzó történeti mezői elkülönítve.

Ellenőrzés: Teljes vásárlás; dupla kattintás/újraküldés; tranzakcióhiba; emailhiba; árközbeni változás; új belépés és két böngészőlap.
Adat/deploy: DB kosár és order snapshot bővítő migráció; session átvitel naplózva; visszaállítás ne vesszen el kosár/rendelés.

### M09 kiadási kapuk

- [x] M09.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [x] M09.K2 Migráció, környezeti előfeltétel, feature flag, visszaállítás és használati változás dokumentálva; adat megőrzése ellenőrizve.
- [x] M09.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M09.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [x] M09.K5 A kiadott SHA stagingen ellenőrizve a fenti folyamattal; UI esetén mobil/tablet/desktop is; nincs nyitott kiadást blokkoló hiba.
- [x] M09.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, következő feladat és ismert korlát megadva.

Kiadási rekord: commit = `5ce003f`; workflow = [GitHub Actions #120](https://github.com/sykolos/eb-webshop/actions/runs/38667147729), verify/deploy sikeres; helyi PHPUnit 73 teszt / 287 assertion, PHP lint, Blade cache, Vite build és diff-check sikeres. Stagingen partneri termék hozzáadás, fejlécszámláló, mennyiségmódosítás, server oldali nettó/áfa/bruttó újraszámítás, checkout és árazatlan szállítási opció betöltése ellenőrzött. A tesztkosárban 2 csomag szándékosan megmaradt a felhasználói kérés szerint; rendelés leadése nem történt.

## M10. Belépés, regisztrációkérés és partnerfiók

Állapot: KIADÁSRA KÉSZ. Függőség: M04, M05, M09; D03.
Forrás: Z: customer-experience; L: fiók újragondolás, regisztráció kérés.
Stagingen bemutatható eredmény: Partnerkérelemtől a saját rendelésekig, címekig és kedvencekig végigjárható fiók.

- [x] M10.01 V6 belépő és jelszó-helyreállító felület, email ellenőrzés, hibás/lejárt token és sikerállapot; demóbelépés nélkül.
- [x] M10.02 Regisztráció kérés gomb és céges kérelem; függő/jóváhagyott/elutasított/tiltott állapot és valódi visszajelzés.
- [x] M10.03 Fiókáttekintő és mobil navigáció, rendeléstörténet szűréssel és saját rendelésrészlet/PDF.
- [x] M10.04 Újrarendelés a pillanatnyilag rendelhető termékekkel és aktuális árakkal; megszűnt termék jelzése.
- [x] M10.05 Szállítási cím létrehozás/szerkesztés/törlés/alapértelmezés; tulajdon és egyetlen alapértelmezett cím.
- [x] M10.06 Kedvencek adatbázisban, gyors kosárba helyezés és megszűnt termék kezelése.
- [x] M10.07 Céges és kapcsolattartói adatok szerkesztése a szabályok szerint; emailváltás újraellenőrzése; jelszó módosítása.
- [x] M10.08 Értesítési és hírlevél-beállítások tartós tárolása, a marketingküldés M16-ban kapcsolódik.

Ellenőrzés: Más fiók adatainak tiltása; függő partner jogai; cím CRUD; újrarendelés árváltozással; kedvencek új belépés után.
Adat/deploy: Partnerstátusz/kedvencek/preferenciák migrációja; meglévő aktív ügyfelek ne veszítsék el hozzáférésüket.

### M10 kiadási kapuk

- [x] M10.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [x] M10.K2 Migráció, környezeti előfeltétel, feature flag, visszaállítás és használati változás dokumentálva; adat megőrzése ellenőrizve.
- [x] M10.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M10.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [x] M10.K5 A kiadott SHA stagingen ellenőrizve a fenti folyamattal; UI esetén mobil/tablet/desktop is; nincs nyitott kiadást blokkoló hiba.
- [x] M10.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, következő feladat és ismert korlát megadva.

Kiadási rekord: commit = `30304d9` (funkciós alap: `a4ad200`); workflow = [GitHub Actions run 38699613557](https://github.com/sykolos/eb-webshop/actions/runs/38699613557), verify/deploy sikeres. Helyi ellenőrzés: PHPUnit 77 teszt / 312 assertion, PHP lint, Blade cache, Vite build és diff-check sikeres. Stagingen a partnerfiók-áttekintő, szűrhető rendeléstörténet, kedvencek, értesítési beállítások, admin partnerkérelem-oldal és a meglévő cím automatikus alapértelmezetté jelölése ellenőrzött. Ismert korlát: a hírlevél/marketing kiküldés tényleges szolgáltatói bekötése M16; a mostani beállítások tartósan tárolódnak.

## M11. Admin rendeléskezelés és kézi rendelésfelvitel

Állapot: KIADÁSRA KÉSZ. Függőség: M04, M05, M09.
Forrás: Z: OrderWorkbench, CreateOrder; L: lista státusz, kijelölés, tömeges módosítás.
Stagingen bemutatható eredmény: Admin kézzel rendelést rögzít partnernek, majd listából egyesével és tömegesen feldolgozza.

- [x] M11.01 V6 rendeléslista kereséssel és cég/dátum/státusz/összeg szűrőkkel, lapozással és megőrzött URL-állapottal.
- [x] M11.02 Soronkénti állapotváltás; kijelölés és látható sorok kijelölése; szűrés/keresés váltáskor kijelölés törlése.
- [x] M11.03 Tömeges státuszváltás megerősítéssel, rekordjogosultság és állapotátmenet ellenőrzéssel, részleges hibák jelzésével.
- [x] M11.04 OrderStatusService, státuszelőzmények, felelős és megjegyzés; kapcsolódó értesítések.
- [x] M11.05 Rendelésadatlap rögzített címekkel/árakkal, dokumentumokkal; D09 szerinti egyszerű Törlés gomb megerősítéssel. A törlés SoftDeletes + audit, a Törölve státusztól elkülönül; végleges fizikai törlés nincs.
- [x] M11.06 Új rendelés: aktív partner, kapcsolattartó, mentett/egyedi cím, fizetés, termékkereső, csomagszám, ellenőrzés és rögzítés.
- [x] M11.07 Kézzel szerkesztett/importált termék és ugyanazon partner PricingService ára használható; ZIP elkülönült demóárazása helyett közös backend.
- [x] M11.08 Adminrendelés is tranzakciós és ismétlésbiztos; admin szereplő audit, a partner saját fiókjában ugyanaz a rendelés látszik.

Ellenőrzés: Kijelölés reset; tiltott átmenet; több admin ütközése; aktív/inaktív partner; adminrendelés -> partnerfiók egyezés.
Adat/deploy: Állapotmapping és audit bővítő migráció; történeti rendelések megőrzése.

### M11 kiadási kapuk

- [x] M11.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [x] M11.K2 Migráció, környezeti előfeltétel, feature flag, visszaállítás és használati változás dokumentálva; adat megőrzése ellenőrizve.
- [x] M11.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M11.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [x] M11.K5 A kiadott SHA stagingen ellenőrizve a fenti folyamattal; UI esetén mobil/tablet/desktop is; nincs nyitott kiadást blokkoló hiba.
- [x] M11.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, következő feladat és ismert korlát megadva.

Kiadási rekord: commit = `822094a`; workflow = [38717403164](https://github.com/sykolos/eb-webshop/actions/runs/38717403164), sikeres verify/deploy; staging próba = lista, kézi rendelésfelvitel, adatlap és 390/768/1440 px ellenőrzés; bizonyíték = fenti napló.

## M12. Admin partnerek és felhasználók teljes kezelése

Állapot: STAGINGEN ELLENŐRZÖTT. Függőség: M04, M10, M11.
Forrás: Z: Partners és részletes adatlap; L: összes adat, inaktív/sima felhasználó szűrés.
Stagingen bemutatható eredmény: Admin kérelmet jóváhagy, üzleti adatokat módosít, és a felhasználókat több szempont szerint szűri.

- [x] M12.01 Partnerlista és részletes adatlap: ID, cég/felhasználó, email, kapcsolattartó, számlázás, szállítás, regisztráció/jóváhagyás ideje, megjegyzések.
- [x] M12.02 Üzletileg szerkeszthető összes mező, címek és szerepkör kontrollált szerkesztése; jelszó helyett visszaállítási folyamat.
- [x] M12.03 Jóváhagyásra váró kérelmek külön lista és közvetlen elfogadás; elutasítás/tiltás okkal és auditnaplóval.
- [x] M12.04 Szűrők: aktív/tiltott/függő, admin/sima felhasználó/szerepkör, utolsó belépés, utolsó rendelés, rendelés nélküliek, dátum és cégadatok.
- [x] M12.05 Inaktivitás az utolsó rendelés alapján D10 szerint, állítható napküszöbbel; rendelés nélküli partner külön szűrő. Utolsó belépés további adat, nem az inaktivitás alapja; jogosultsági státusz külön fogalom.
- [x] M12.06 Kapcsolódó rendelések és partnerárak hivatkozásai; szerepkörváltás nem történhet partnerjóváhagyás mellékhatásaként.
- [x] M12.07 Saját adminjog és utolsó admin véletlen eltávolításának kezelése; partner törlés/archiválás üzleti adatok megőrzésével.

Ellenőrzés: Jogosultságemelés tiltása; jóváhagyási folyamat; szűrők kombinációi; módosítás audit; partner történeti rendelései megmaradnak.
Adat/deploy: Régi felhasználó/cég/cím mezők mappingje, státuszok és aktivitás kezdeti ismeretlen értékei dokumentálva.

### M12 kiadási kapuk

- [x] M12.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [x] M12.K2 Migráció, környezeti előfeltétel, feature flag, visszaállítás és használati változás dokumentálva; adat megőrzése ellenőrizve.
- [x] M12.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M12.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [x] M12.K5 A kiadott SHA stagingen ellenőrizve a fenti folyamattal; UI esetén mobil/tablet/desktop is; nincs nyitott kiadást blokkoló hiba.
- [x] M12.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, következő feladat és ismert korlát megadva.

Kiadási rekord: kód commitok = `7d0da75`, `039aa00`, `9d63951`; retry/release commit = `ea7a9cc`; sikeres workflow = [38726573353](https://github.com/sykolos/eb-webshop/actions/runs/38726573353) (`verify: success`, `deploy: success`). Staging próba: `/adminpanel/users`, `/adminpanel/partner-requests`, `/adminpanel/users/310`; 390x844, 768x1024 és 1440x900 méreten a partneradatlap betöltött, a dokumentum szélessége nem haladta meg a viewportot. A korábbi `Disk quota exceeded` rollbackek: [38721498975](https://github.com/sykolos/eb-webshop/actions/runs/38721498975), [38721808476](https://github.com/sykolos/eb-webshop/actions/runs/38721808476) történeti hibaként megmaradnak.

## M13. Excel-import és minden üzleti táblázat exportja

Állapot: STAGINGEN ELLENŐRZÖTT. Függőség: M06, M07, M11, M12.
Forrás: Z: admin-transfer, import-validation; L: Excel export minden táblázathoz.
Stagingen bemutatható eredmény: Sablonból ellenőrzött import készül, majd a teljes szűrt terméklista visszaimportálható XLSX-ként exportálható.

- [x] M13.01 Exportálható táblák nyilvántartása: rendelések, rendeléssorok, termékek, kategóriák, szűrők, partnerek, egységek, árak, kiemeltek; későbbi modulok saját exporttal záródnak.
- [x] M13.02 Export a teljes szűrt eredményhalmazból, nem csak aktuális oldalból/kijelölésből; műveletoszlopok nélkül, mentett szerveradatokból.
- [x] M13.03 Valódi XLSX és ZIP szerinti opcionális pontosvesszős UTF-8 BOM CSV; oszlopjogosultság és képletinjektálás elleni védelem.
- [x] M13.04 Nagy export háttérfeladat, állapot, privát lejáró letöltés, jogosultság újraellenőrzése, fájltakarítás.
- [x] M13.05 Import sablon, munkalapválasztás, oszloppárosítás, első 100 sor előnézete és teljes fájlvalidáció.
- [x] M13.06 ZIP alapkorlátok: 5 MB, 5000 adatsor, 50 oszlop; szerveroldali kibontási/memória/idő korlátok; XLS régi formátum konvertálási visszajelzés.
- [x] M13.07 Kötelező cikkszám/név/meglévő kategória/nettó egységár; opcionális jelenlegi egység/szorzó/csomag/gyártó/leírás/publikálási állapot és elérhetőségi státusz. Régi stock szám nem konvertálható önkényesen státusszá; mély fában kategóriaútvonal/azonosító oldja fel az azonos neveket.
- [x] M13.08 Vesszős tizedes, kis/nagybetűfüggetlen SKU, fájlon belüli duplikáció és képletcella ellenőrzés; üres opcionális cella nem töröl adatot.
- [x] M13.09 Kihagyás/frissítés mód; ismeretlen kategória hibás; bármely hibás sor blokkolja az alkalmazást, hibajegyzék exportálható.
- [x] M13.10 Kétlépcsős ellenőrzés/alkalmazás, tranzakció és ismétlésvédelem, konkurens változások újraellenőrzése; valódi DB és webshop frissül.
- [x] M13.11 Termékexport visszaimportálási próba; import/export audit és queuehiba kezelése.

Ellenőrzés: Érvényes körút; duplikált SKU; hibás kategória; képlet; vesszős ár; limit; jogosulatlan oszlop/letöltés; teljes szűrt adathalmaz.
Adat/deploy: Import job/hiba/export táblák; alkalmazás előtt előnézet; meglévő mezők megőrzése és visszakövethető módosítás.

### M13 kiadási kapuk

- [x] M13.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [x] M13.K2 Migráció, környezeti előfeltétel, feature flag, visszaállítás és használati változás dokumentálva; adat megőrzése ellenőrizve.
- [x] M13.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M13.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [x] M13.K5 A kiadott SHA stagingen ellenőrizve a fenti folyamattal; UI esetén mobil/tablet/desktop is; nincs nyitott kiadást blokkoló hiba.
- [x] M13.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, következő feladat és ismert korlát megadva.

Kiadási rekord: M13.01 commit = `e257ec0`; workflow = [38727685489](https://github.com/sykolos/eb-webshop/actions/runs/38727685489) (`verify: success`, `deploy: success`); staging próba = `/adminpanel/exports`, 9 adatkör, 390x844/768x1024/1440x900; M13.K4-K6 a teljes M13 mérföldkő további export/import pontjai miatt nyitva.

## M14. Hero, úszó banner, tartalmi oldalak és PPT

Állapot: STAGINGEN ELLENŐRZÖTT. Függőség: M04, M05, M08, M13; D08 tartalom/banner.
Forrás: Z: hero, információs oldalak, emailminta; L: banner feltöltés három eszközre, PPT, úszó menü.
Stagingen bemutatható eredmény: Adminból időzíthető banner jelenik meg a webshopban, a három képkivágás ellenőrizhető.

- [x] M14.01 Hero/úszó képek adminja: desktop/tablet/telefon kép, alt, cím, CTA/link, sorrend, aktív időszak és külön publikálás; aktuális akciókra és termékkategóriákra mutatható elemek.
- [x] M14.02 Reszponzív képkiválasztás és vágás; fix desktop 1600×500, tablet 1200×675 és telefon 900×1125 kimenet, mobilon lebegő elem tartalomvédett elhelyezéssel.
- [x] M14.03 Képfeltöltés és publikálás külön művelet, formátum/méret és hiányzó változat kezelése; banner-metaadat export az M13 katalógusban.
- [x] M14.04 ZIP szerinti kapcsolat, szállítás/fizetés, GYIK, katalógus/dokumentumtár, impresszum, ÁSZF, adatkezelés és elállás oldalak V6 layouton.
- [x] M14.05 A jelenlegi kapcsolati cégadatok egy közös konfigurációba kerültek; ZIP eltérő címe és jogi helykitöltője nem került ellenőrzött éles állításként használatba. A végleges jogi jóváhagyás továbbra D08 tartalmi átadás.
- [x] M14.06 Kapcsolati űrlap validációval, `throttle:contact` próbálkozáskorláttal, commit utáni queue-jobbal és siker/hiba visszajelzéssel.
- [x] M14.07 Katalógus útvonal, név/nyelv/verzió és letöltési ellenőrzés előkészítve; hiányzó ellenőrzött PDF-nél őszinte, gomb nélküli üres állapot. A végleges PDF üzleti tartalomként később tölthető fel.
- [x] M14.08 Bannerkészítési PPT elkészült `docs/M14-banner-keszitesi-utmutato.pptx` néven: px méretek/képarányok, szövegzónák, jó/rossz példák, feltöltési lépések és export; renderelt és strukturálisan validált.
- [x] M14.09 Tartalmi/üres/404 oldalak és V6/legacy footer linkek ellenőrzött útvonalakra kerültek.

Ellenőrzés: Három viewport és időzített publikálás; hibás feltöltés; kapcsolatküldés tesztcímzettre; dokumentumlinkek és PPT vizuális ellenőrzés.
Adat/deploy: Banner és média metaadatok; korábbi képek megőrzése; szövegek verziója rögzítve.

### M14 kiadási kapuk

- [x] M14.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [x] M14.K2 A banner-migráció, meglévő adatok megőrzése, publikus médiaútvonal, rollback és a jogi tartalom jóváhagyásra váró jelzése dokumentálva.
- [x] M14.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M14.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [x] M14.K5 A kiadott SHA stagingen ellenőrizve; admin bannerlista és publikus tartalmi oldalak 390x844/768x1024/1440x900 méreten túlcsordulás nélkül betöltöttek.
- [x] M14.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, következő feladat M15.01, ismert tartalmi korlát rögzítve.

Kiadási rekord: M14 kód = `717e917`; tablet overflow javítás = `a4e1364`; első workflow = [38743282148](https://github.com/sykolos/eb-webshop/actions/runs/38743282148), verify/deploy sikeres; javító workflow = [38743946029](https://github.com/sykolos/eb-webshop/actions/runs/38743946029), verify/deploy sikeres. A staging admin `/adminpanel/banners`, `/info/katalogus`, `/contact` és 404 útvonalai 390x844/768x1024/1440x900 méreten ellenőrizve; a katalógus jelenleg őszinte üres állapotot mutat, mert nincs jóváhagyott PDF a repóban.

## M15. Dashboard, top termékek, kosár- és aktivitásfigyelés

Állapot: KIADÁSRA KÉSZ. Függőség: M09, M11, M12, M13; D10 és D11. A staging-kör provider-semleges, saját Laravel eseménytárolót használ; GA4/Clarity/Matomo nincs aktiválva.
Forrás: Z: dashboard-definíciók; L: mutatók, top termékek, aktivitás, live tracking, kosárfigyelés.
Stagingen bemutatható eredmény: Valós rendelésekből számolt dashboardból a megfelelő szűrt munkalistára lehet jutni.

- [x] M15.01 Nettó rendelési érték, nem törölt rendelésszám, átlagérték azonos időszakkal; nyitott rendelések külön teljes állományból.
- [x] M15.02 Jóváhagyandó partnerek, hiányos terméktörzs és feldolgozandó rendelések munkalistái.
- [x] M15.03 Top termék rangsor nettó termékforgalom alapján D10 szerint, kiválasztott időszakban, történeti rendelési árakból. Törölt/archivált rendelések kizárása a dashboard egységes szabályával; db/m nem adható össze.
- [x] M15.04 Kézzel kiemelt és számított top termékek külön kezelése; a kézi kiemelt lista admin sorrendje megmarad.
- [x] M15.05 Kosárfigyelés: tulajdonos, utolsó aktivitás, tételek és aktuális összeg, aktív/elhagyott/rendeléssé vált állapot; adminmegtekintés nem frissít aktivitást.
- [x] M15.06 Utolsó rendelésen alapuló inaktivitás, külön last_seen/last_login és rendelés nélküli csoport; a tracking események hat hónapos megőrzésével.
- [x] M15.07 Valódi részletes, provider-semleges tracking D11 szerint: aktív látogatók, oldalak, események, forrás/kampány, eszköz, keresés, termék/kosár/checkout útvonal; 60 esemény/perc/IP limiterrel.
- [x] M15.08 Nem létező késési vagy számlázott adatot a dashboard nem állít; elérhetőségi státuszt darabszámként, nem mennyiségi készletként mutat.
- [x] M15.09 Dashboard, kosár- és aktivitási export-adatkör került a közös szerveroldali XLSX/CSV exportba; dashboard cache 60 másodperc.
- [x] M15.10 Tracking eseményszótár: keresés/szűrés, termékmegtekintés, kosármódosítás, checkout lépések, vásárlás és regisztrációkérés; szerveroldali rendelés az igazságforrás, UUID-alapú duplázásvédelemmel.
- [x] M15.11 Hozzájárulás-kezelő és adatkezelési tájékoztató technikai része: elfogadás/elutasítás/visszavonás, személyes és partnerár-adatok maszkolása, admin/belépési út kizárása, megőrzés rögzítése.
- [x] M15.12 A staginghez a legegyszerűbb, saját Laravel adattáras megoldás választva; külső GA4/Clarity/Matomo nincs bekötve. Blokkolt vagy elutasított tracking mellett a webshop működik, a lefedettség korlátja dokumentált.

Ellenőrzés: Ismert tesztadatból nettó top rangsor és rendelésalapú inaktivitás; nulla/rendelés nélküli eset; státuszkizárás; kosárállapotok; eseményduplázás; elfogadás/elutasítás/visszavonás; maszkolás és mérési terhelés.
Adat/deploy: Aktivitás és aggregátumok tárolása/megőrzése; visszamenőleges ismeretlen aktivitás nem kitalált.

### M15 kiadási kapuk

- [x] M15.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [x] M15.K2 Migráció, környezeti előfeltétel, visszaállítás és használati változás dokumentálva; adatmegőrzés hat hónapos expires_at mezővel és ütemezett takarítással.
- [x] M15.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M15.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [x] M15.K5 A kiadott SHA stagingen ellenőrizve a fenti folyamattal; UI esetén mobil/tablet/desktop is; nincs nyitott kiadást blokkoló hiba.
- [x] M15.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, következő feladat és ismert korlát megadva.

Kiadási rekord: commit = `67c4da0`; workflow = [38746941405](https://github.com/sykolos/eb-webshop/actions/runs/38746941405) verify/deploy sikeres; staging próba = `/adminpanel`, `/adminpanel/carts`, `/adminpanel/activity`, `/adatkezelesi-nyilatkozat` és publikus consent-flow, 390/768/1440 px, vízszintes túlcsordulás nélkül; bizonyíték = helyi PHPUnit 94 teszt / 422 assertion, Vite build, PHP lint, Blade/route/config cache, Composer validate, diff-check, stagingen egy consentelt `page_view` esemény.

### 2026-10-15: M15 stagingen ellenőrzött

- Elkészült a dashboard valódi, nem törölt rendelési adatokból számolt nettó összeggel, rendelésszámmal, átlagértékkel, teljes állományú nyitott rendelésekkel, partner- és terméktörzs-munkalistákkal, top termék rangsorral és külön kézi kiemelt listával. A top rangsor történeti `items.line_net`/`unit_price` adatokból dolgozik, a jelenlegi partneri árlogikát nem számolja újra.
- Elkészült a partnerkosár-figyelés aktív/elhagyott/rendeléssé vált állapottal, tulajdonossal, utolsó aktivitással és aktuális nettó összeggel. A sikeres checkout a kosarat rendeléshez kapcsolja, az adminnézet olvasási művelete nem frissíti az aktivitást.
- Elkészült az első fél aktivitási eseménytároló hozzájárulással, UUID-duplázásvédelemmel, érzékeny mezőmaszkolással, 60 esemény/perc/IP limiterrel és hat hónapos automatikus megőrzéssel. A webshop blokkolt vagy visszautasított mérés mellett is működik; külső GA4/Clarity/Matomo nincs aktiválva.
- Az admin dashboard, kosárfigyelés és aktivitásnézet stagingen betöltött 390x844, 768x1024 és 1440x900 méreten, túlcsordulás nélkül. Consent elfogadás után `page_view` esemény láthatóvá vált, majd a hozzájárulás visszavonása is működött. Valódi rendelés, kosármódosítás és destruktív művelet nem történt.
- Helyi ellenőrzés: M15 célteszt **6 teszt / 43 assertion**, teljes PHPUnit **94 teszt / 422 assertion**, Vite build, PHP lint, Blade/route/config cache, Composer validate és `git diff --check` sikeres. A Vite meglévő Sass/Bootstrap deprecation figyelmeztetései továbbra is M21 technikai adósságok.
- Kiadás: `67c4da0`, GitHub Actions [38746941405](https://github.com/sykolos/eb-webshop/actions/runs/38746941405) verify/deploy sikeres. A checklist ezen záró adminisztratív frissítése helyi commitban marad, külön dokumentációs deploy nem indul.
- Állapot: M15 STAGINGEN ELLENŐRZÖTT. Következő konkrét fejlesztés: M16.01 emailközpont és hírlevél szolgáltatói döntés nélkül, a D12 szerint előkészítve.

## M16. Emailközpont és hírlevél

Állapot: STAGINGRE KÉSZ. Függőség: M04, M10, M12, M13; D12 külső szolgáltató/domainhitelesítés továbbra külön döntés.
Forrás: Z: emailminta, értesítési preferenciák; L: email admin, hírlevél, rendszeres hírlevél.
Stagingen bemutatható eredmény: Admin tesztkampányt szerkeszt, előnéz, időzít, és tesztcímzetteken követi a kézbesítést.

- [x] M16.01 Email menüpont jogosultsággal: kampánysablon, címzettlista/szegmens, sandboxolt előnézet és tesztküldés.
- [x] M16.02 V6 visszaigazoló email valódi levelezőkliens-kompatibilis HTML és szöveges változata; partner- és adminlevelek.
- [x] M16.03 Tranzakciós és marketing email szétválasztása; meglévő preferenciák, leiratkozás, kizárólista és címzettjogosultság.
- [x] M16.04 Hírlevél kampány szerkesztés, ütemezés, időzóna, megszakítás és kiküldés előtti friss címzettszám-ellenőrzés.
- [x] M16.05 Provider-semleges limit- és státuszmodell, retry/failed napló és provider_message_id előkészítve; domainhitelesítés, bounce/complaint webhook a D12 szolgáltató-döntéséig halasztott.
- [x] M16.06 Queue, küldési limit, retry/backoff és idempotens delivery kulcs; admin tömeges küldés nem blokkol webkérést.
- [x] M16.07 Küldési napló és kampánytáblák admin-only XLSX/CSV exportja; a kampánytörzs és címzettadat megőrzése, levéltörzs exportból kizárva.
- [x] M16.08 A meglévő staging mail-sink szabály megtartva; tényleges marketingindítás külön kiadási művelet és D12 jóváhagyás.

Ellenőrzés: Tesztküldés, időzítés, leiratkozás, kizárt címzett, szolgáltatóhiba és ismételt job; email kliens előnézet.
Adat/deploy: Kampány/címzett/küldési napló migráció; régi felhasználókat nem szabad automatikusan feliratkozónak tekinteni.

### M16 kiadási kapuk

- [x] M16.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [x] M16.K2 Migráció, környezeti előfeltétel, feature flag, visszaállítás és használati változás dokumentálva; régi felhasználók nem lettek automatikusan feliratkoztatva.
- [x] M16.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M16.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [x] M16.K5 A kiadott SHA stagingen ellenőrizve a fenti folyamattal; UI 390/768/1440 px-en ellenőrizve; nincs nyitott kiadást blokkoló hiba.
- [x] M16.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, következő feladat és ismert korlát megadva.

Kiadási rekord: commit = `c93c74f`; workflow = [GitHub Actions run 38749992983](https://github.com/sykolos/eb-webshop/actions/runs/38749992983), verify/deploy sikeres; staging próba = `/adminpanel/emails`, admin navigáció, HTML előnézet, 390/768/1440 px és vízszintes túlcsordulás ellenőrzése; küldés/kampánymentés nem történt. Ismert korlát: D12 provider/domainhitelesítés, bounce/complaint webhook és valódi marketingindítás későbbi döntés.

## M17. Kosárelhagyás és időzített automatizmusok

Állapot: KIADÁSRA KÉSZ. Függőség: M09, M15, M16; D12.
Forrás: L: emlékeztető email, automatikus és időzített értesítések.
Stagingen bemutatható eredmény: Egy tesztkosár szabály szerint emlékeztetőt kap, vásárlás után pedig már nem.

- [x] M17.01 Elhagyott kosár küszöb, címzettkör, küldési ablak/időzóna és maximum emlékeztető meghatározása.
- [x] M17.02 Kosáraktivitásból időzített feladat; végrehajtáskor jogosultság/kosárállapot újbóli újraellenőrzése.
- [x] M17.03 Új vásárlás, üres kosár, leiratkozás, tiltás vagy új aktivitás esetén emlékeztető megszüntetése/újraütemezése.
- [x] M17.04 Eseményértesítések: partnerjóváhagyás és rendelésállapot; a ticket szabály és eseményhorog előkészítve, tényleges ticket domain az M18-ban. Elérhetőségi státuszváltozás M19 után külön kapcsolható; mennyiségi alacsony-készlet értesítés a későbbi készletbővítéshez tartozik.
- [x] M17.05 Admin automatizmusszabály, bekapcsolás/szünet, következő futás, futási napló és export.
- [x] M17.06 Scheduler és job idempotencia, küldési korlát, hibariasztási állapot és ismételt futás kizárása.

Ellenőrzés: Idővezérelt tesztek valódi várakozás nélkül; vásárlás/küldés versenyhelyzet; duplikált futás; tiltott címzett.
Adat/deploy: Szabályok/futások/küldési kulcsok bővítő migrációi; kezdetben kikapcsolt automatizmusok.

### M17 kiadási kapuk

- [x] M17.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [x] M17.K2 Migráció, környezeti előfeltétel, kikapcsolt alapértelmezés, visszaállítás és használati változás dokumentálva; meglévő kosár-, ár- és emailadat megőrzése ellenőrizve.
- [x] M17.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M17.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [x] M17.K5 A kiadott SHA stagingen ellenőrizve a fenti folyamattal; UI esetén mobil/tablet/desktop is; nincs nyitott kiadást blokkoló hiba.
- [x] M17.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, következő feladat és ismert korlát megadva.

Kiadási rekord: commit = `eac8964`; workflow = [GitHub Actions run 38775259789](https://github.com/sykolos/eb-webshop/actions/runs/38775259789), verify/deploy sikeres; staging próba = `/adminpanel/automations` admin belépéssel, 4 kikapcsolt szabály, üres futási napló, 390/768/1440 px; `/adminpanel/exports` alatt az `automation_rules` és `automation_runs` adatkör látható. Küldést, szabálybekapcsolást és kézi futtatást nem indítottam. Ismert korlát: D12 provider/domainhitelesítés nyitott; a ticket eseményhurok az M18 ticket domainjéig előkészített és kikapcsolt.

## M18. Ticket és belső üzenőfal

Állapot: STAGINGEN ELLENŐRZÖTT. Függőség: M04, M11, M12, M16.
Forrás: Z: Support; L: ticket, fejlesztői üzenőfal. A chat külön scope-ként elengedve.
Stagingen bemutatható eredmény: Hibajegy rendeléshez kapcsolható, felelőst kap, és belső megjegyzése ügyfélnek nem látható.

- [x] M18.01 ZIP supportlista és adatlap: típus, prioritás, felelős, kapcsolódó rendelés, keresés és szűrés.
- [x] M18.02 Új/Folyamatban/Válaszra vár/Megoldva/Lezárva állapotok, jogosult átmenetek és napló.
- [x] M18.03 Hozzászólások és biztonságos mellékletek, belső és ügyfélnek látható tartalom elkülönítése.
- [x] M18.04 Fejlesztői üzenőfal a közös ticket/üzenetmodellre, releváns technikai adatokkal, titkok nélkül.
- [x] M18.05 Értesítések és olvasottság; ticketlista export és megőrzési szabályok.
- [x] M18.06 Chat scope lezárva: külön chat nem szükséges, a ticketrendszer marad az ügyfélszolgálati kommunikáció.
- [x] M18.07 Chat-kiesés és újracsatlakozás kezelése nem készül, mert chatfunkció nincs a jelenlegi rendszerben.
- [x] M18.08 SLA prioritások/időpontok rögzítésének alapja; a végleges vállalások M21-ben kerülnek rögzítésre.

Ellenőrzés: Belső komment és melléklet nem szivárog; idegen ticket tiltott; állapot és értesítés; újratöltés.
Adat/deploy: Ticket/üzenet/melléklet migráció; külön chatadat-migráció nincs.

### M18 kiadási kapuk

- [x] M18.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek: M18 célteszt 5 teszt / 27 assertion, teljes CI PHPUnit 108 teszt / 484 assertion, Vite build, PHP lint, Blade cache, route-list és diff-check.
- [x] M18.K2 A ticket-migráció legacy `users.id` szélességet és félbemaradt első futást is kezelő, idempotens; privát fájl, audit, retention és rollback-viselkedés dokumentált, új környezeti változó nem kell.
- [x] M18.K3 Diff ellenőrizve, csak célzott M18 alkalmazás-, konfiguráció-, migráció-, nézet- és tesztfájlok; reprodukálható, önálló commitolható állapot.
- [x] M18.K4 A `1319f8b`, `be66379` és végső `454da2a` commitok developra kerültek; a végső [38781343141](https://github.com/sykolos/eb-webshop/actions/runs/38781343141) workflow verify/deploy sikeres.
- [x] M18.K5 A `454da2a` stagingen ellenőrizve: `/adminpanel/tickets` adminnal betöltött, a szűrők és partnerenkénti izolációs felület látszik, az exportkatalógusban a két ticket-adatkör elérhető; 390/768/1440 px-en nincs vízszintes túlcsordulás.
- [x] M18.K6 A checklist és a folytatási handoff frissítve; következő feladat M19.01, ismert korlátként a M21 végleges SLA-vállalás marad.

Kiadási rekord: végső commit = `454da2ae7bc33a3534ea960d4fd5ccaa0199129d`; GitHub Actions [#144 / 38781343141](https://github.com/sykolos/eb-webshop/actions/runs/38781343141), verify/deploy sikeres; staging próba = `/adminpanel/tickets`, `/adminpanel/exports`, 390/768/1440 px; stagingen valódi ticketet, üzenetet és csatolmányt nem hoztam létre. Az első két deploy-kísérlet migrációs legacy-állapot miatt visszaállt, a végső idempotens migráció adatvesztés nélkül sikeres.

## M19. Termékelérhetőségi státuszok

Állapot: STAGINGEN ELLENŐRZÖTT. Függőség: M06, M08, M13; D06 eldöntve. A függőségek után előrehozható, nem kell M17-re várnia.
Forrás: Z: készletjelzések; L: pontosítás a terméken állítható elérhetőségről.
Stagingen bemutatható eredmény: Admin státuszt állít a terméken, és ugyanaz jelenik meg a katalógusban, adatlapon és exportban.

- [x] M19.01 Elérhetőségi státuszok: Készleten, Beszállítói készleten, Beszerzés alatt; bővíthető kódkészlet/megnevezés és ismeretlen érték validáció.
- [x] M19.02 Termékenkénti állítás és külön publikálási státusz; meglévő adat nélküli terméknek alapértelmezett Készleten kompatibilitási állapot, nem kitalált készletszám.
- [x] M19.03 Állapotváltás adminjogosultsággal és auditnaplóval; státusztörténet, aktív állapot szűrés és egyértelmű szöveges/színes jelzés.
- [x] M19.04 Állapotváltás nem írja át automatikusan a publikálási vagy meglévő rendelhetőségi szabályt; új vásárlási tiltás csak a már meglévő Átmenetileg nem elérhető állapotnál él. Készletfoglalás ebben a körben nincs.
- [x] M19.05 Webshop kártya/adatlap és mobil megjelenítés közös státuszforrásból, dashboard-cache érvénytelenítéssel; beszállítói készlet nem állítható saját raktárkészletként.
- [x] M19.06 Státusz szerinti adminlista és XLSX/CSV export/import; numerikus készlet- vagy minimumküszöb-funkció most nem készül.
- [x] M19.07 Dashboard státuszcsoportok szerinti termékszám és teljes integráció ellenőrzése. Mennyiségi készletmozgás, szinkron és alacsony-készlet automatizmus későbbi igényként megőrizve.

Ellenőrzés: Státusz mentése/újratöltése, import/export körút, publikálási állapottól függetlenség, jogosultság és mobil jelzés; mennyiségi készlet vagy új rendelési korlát nem keletkezik.
Adat/deploy: M06 státuszmezőjére épülő integráció, meglévő állapotok dokumentált mappingje; audit megőrzése.

### M19 kiadási kapuk

- [x] M19.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [x] M19.K2 A státusztörténet új, bővíthető migrációval készül; meglévő termékadatot nem ír át, a visszaállítás az új history tábla eltávolítása; új környezeti változó és feature flag nem kell.
- [x] M19.K3 Diff ellenőrizve, csak célzott modell-, service-, controller-, migráció-, nézet-, teszt- és checklist-fájlok; reprodukálható, önállóan commitolható állapot.
- [x] M19.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [x] M19.K5 A `ba9c203` kiadott SHA stagingen ellenőrizve: admin terméklista, státuszszűrő, státusztörténet, dashboard, publikus termékadatlap; 390/768/1440 px-en nincs vízszintes túlcsordulás, kiadást blokkoló hiba nincs.
- [x] M19.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, a következő feladat M20.01, az ismert frontend deprecation figyelmeztetések megadva.

Kiadási rekord: commit = `ba9c2033880a52a39c012278b37f0f8a3e05ce56`; GitHub Actions [#145 / 38813030720](https://github.com/sykolos/eb-webshop/actions/runs/38813030720), verify/deploy sikeres; staging próba = `/adminpanel/products`, `?availability_status=procurement`, `/adminpanel/products/299`, `/adminpanel`, `/products/299`, 390/768/1440 px; valódi státuszváltást és importot stagingen nem indítottam.

## M20. Aktuális akciók és partneri ajánlatok megjelenítése

Állapot: KIADÁSRA KÉSZ. Függőség: M07, M08, M09, M14; D07 eldöntve: meglévő árazás.
Forrás: Z: akcióoldal; L: meglévő kedvezményrendszer megőrzése és aktuális akciók megjelenítése.
Stagingen bemutatható eredmény: Kiemelt ajánlatból a termékhez jutunk; a megjelenített partnerár ugyanaz, mint a kosárban és rendelésben.

- [x] M20.01 Az ajánlatmegjelenítés és a D01 szerint változatlan árazás szétválasztva; a tartalmi kiemelés nem módosít speciális árat vagy kedvezményprioritást.
- [x] M20.02 Aktuális ajánlatok kézi kiválasztása termékre és kategóriakörre; ajánlat hiányában őszinte üres állapot. Nincs új kupon- vagy kedvezményhalmozási motor.
- [x] M20.03 M14 banner-/tartalomadminra épülő kép, cím, kapcsolt ajánlati link, sorrend, megjelenési időszak és publikus előnézeti oldal; a megjelenési időszak vége nem töröl partnerár-megállapodást.
- [x] M20.04 M07 PricingService használata változtatás nélkül; a partneri speciális ár az ajánlati oldalon is ugyanúgy számolódik, a vendégár nem szivárog ki.
- [x] M20.05 Akciós lista/kártya, termék- és kategóriakör-link a V6 arculatában; publikus nézetben nincs partnerár, belépés után saját ár és jelenlegi kiszerelés. Kitalált százalék/áthúzott ár nem jelenik meg.
- [x] M20.06 Ajánlati tartalmak exportja, audit, cache invalidáció és időzített megjelenítés a közös bannerfunkcióval.

Ellenőrzés: Megjelenési időszak és időzóna, kategórialink, vendég/partner állapot, meglévő speciális árak egyezése; banner lejárata nem változtat árat.
Adat/deploy: Tartalmi termék/kategória kapcsolatok bővítő migrációja; meglévő ármegállapodások változatlanok.

### M20 kiadási kapuk

- [x] M20.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [x] M20.K2 Két új, idempotens kapcsolatot védő migráció készült: az ajánlati törzs/pivotok és a nullable banner-ajánlatkapcsolat; meglévő ár- és rendelési adathoz nem nyúl, visszaállítás csak az új táblákat/oszlopot érinti, új környezeti változó nem kell.
- [x] M20.K3 Diff ellenőrizve, csak célzott ajánlati, banner-, export-, route-, nézet-, stílus-, migráció-, teszt- és checklist-fájlok; reprodukálható, önállóan commitolható állapot.
- [ ] M20.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [ ] M20.K5 A kiadott SHA stagingen ellenőrizve a fenti folyamattal; UI esetén mobil/tablet/desktop is; nincs nyitott kiadást blokkoló hiba.
- [ ] M20.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, következő feladat és ismert korlát megadva.

Kiadási rekord: commit = előkészítés alatt; workflow = nincs; staging próba = nem futott. M20 célteszt: 3 teszt / 24 assertion; kapcsolódó M07/M08/M13/M14 regressziós kör: 17 teszt / 81 assertion, 1 skip; teljes PHPUnit: 115 teszt / 520 assertion, 2 skip; Vite build, PHP lint, Blade cache, route-list és diff-check sikeres. A build meglévő Sass/Bootstrap deprecation és hero asset runtime figyelmeztetéseket tartalmaz.

## M21. Teljes átállás, teljesítmény és üzemeltetési átadás

Állapot: TERVEZETT. Függőség: M01-M20; lezáratlan érintett döntések rendezve, a felhasználó által külön halasztott csomagok tételesen rögzítve.
Forrás: Z: összes élesítendő nézet; L: teljesítmény, biztonság, SLA, karbantartás, tárhely.
Stagingen bemutatható eredmény: A teljes új rendszer stagingen végigtesztelt, dokumentáltan élesítésre előkészített.

- [ ] M21.01 Oldaljegyzék és igénylefedettség teljes ellenőrzése; minden demo/mintaadat/halott gomb/ellenőrizetlen tartalom eltávolítása.
- [ ] M21.02 Bootstrap, jQuery és rájuk épülő UI-pluginek végleges eltávolítása csomagból, CDN-ből, layoutból és inline kódból; assetellenőrzés.
- [ ] M21.03 Teljes vásárlói/admin E2E és negatív jogosultsági ellenőrzés, mobil/tablet/desktop összehasonlítás, hozzáférhetőség és böngészőkonzol.
- [ ] M21.04 Reprezentatív adat- és terhelésmérés; p95 célok rögzítése/ellenőrzése, query/index/cache és képméret optimalizálás.
- [ ] M21.05 Biztonsági csomagaudit, CSP, session/cookie, upload/download, email és log ellenőrzések, szükséges javítások.
- [ ] M21.06 Éles adatok próbamigrációja: darabszámok, összegek, partnerárak, rendelések, címek és feltöltések egyeztetése; visszaállítás próbája.
- [ ] M21.07 Stagingből azonos commit/build éles kiadási folyamat, release notes és rollback; éles deploy külön eseményként naplózandó.
- [ ] M21.08 Tárhely/email/média/DB kapacitásfelmérés, worker/cron/mentés követelményei és bővítés ellenőrzése.
- [ ] M21.09 SLA: heti 5 nap konkrét idősávval; kritikus aznapi kezelés, magas prioritás megkezdése 1 munkanapon belül; reakció/helyreállítás/javítás külön fogalom.
- [ ] M21.10 Havi 1-2 személyes meeting és havi teljesítési riport sablonja; felelősök és hibabejelentési csatornák.
- [ ] M21.11 Ismétlődő karbantartási rend: Laravel/csomagfrissítés, biztonsági ellenőrzés, backup-visszaállítás, logelemzés és teljesítményriport.
- [ ] M21.12 Admin/bannerek/kiadás/hibakezelés kezelési dokumentáció; megszűnő régi felületek és URL-ek átvezetése.
- [ ] M21.13 Nyitott hibák és halasztott funkciók átadási listája; monitoring, riasztás és üzemeltetési felelős rögzítése.

Ellenőrzés: Teljes regresszió, vizuális összevetés, terhelési eredmények, próbamigráció, restore és működő riasztás bizonyítékkal.
Adat/deploy: Végső eltávolító migráció csak a visszaállítási ablak után; kódrollback és adatrestore külön terv.

### M21 kiadási kapuk

- [ ] M21.K1 Az érintett tesztek/build és a fenti ellenőrzések sikeresek; bizonyíték a naplóban.
- [ ] M21.K2 Migráció, környezeti előfeltétel, feature flag, visszaállítás és használati változás dokumentálva; adat megőrzése ellenőrizve.
- [ ] M21.K3 Diff ellenőrizve, csak célzott fájlok; reprodukálható, önállóan commitolható állapot.
- [ ] M21.K4 Commit és develop integráció/push megtörtént; staging workflow sikeres; tényleges SHA és futásazonosító rögzítve.
- [ ] M21.K5 A kiadott SHA stagingen ellenőrizve a fenti folyamattal; UI esetén mobil/tablet/desktop is; nincs nyitott kiadást blokkoló hiba.
- [ ] M21.K6 Kiadási/folytatási napló frissítve: állapot STAGINGEN ELLENŐRZÖTT, következő feladat és ismert korlát megadva.

Kiadási rekord: commit = nincs; workflow = nincs; staging próba = nem futott; bizonyíték = nincs.

## Oldalak és vizuális elfogadás nyilvántartása

A route-ok a ZIP-ből származó referenciaútvonalak, nem kötelező Laravel URL-ek. Minden csoport mellé a megvalósításkor rögzítendő a végleges route, referencia/Laravel képpár és ellenőrzés dátuma. A csoport csak az összes felsorolt nézet ellenőrzése után pipálható.

- [ ] V01 Főoldal `/`, fejléc/footer, hero, kategóriák és terméksávok; M08/M14.
- [ ] V02 `/termekek`: keresés/szűrés/rács/lista; `/termekek/[slug]`: adatlap és dokumentum; M08.
- [ ] V03 `/gyorsrendeles`, `/kosar` üres/feltöltött, `/penztar` és ellenőrzés, `/siker`; M08/M09.
- [ ] V04 `/belepes`, `/regisztracio`, `/jelszo`, visszajelzések és jóváhagyásra váró állapot; M10.
- [ ] V05 `/fiok`, `/fiok/rendelesek`, `/fiok/rendelesek/[id]`, újrarendelés; M10.
- [ ] V06 `/fiok/cimek` létrehozás/szerkesztés/törlés/alapértelmezés, `/fiok/kedvencek`, `/fiok/cegadatok`, `/fiok/beallitasok`; M10.
- [ ] V07 `/admin` dashboard, időszak és valódi mutatók; M15.
- [ ] V08 `/admin/rendelesek`, `/admin/rendelesek/uj`, `/admin/rendelesek/[id]`; M11.
- [ ] V09 `/admin/termekek` lista és füles szerkesztő, import nézetek és export; M06/M13.
- [ ] V10 `/admin/kategoriak`, `/admin/szurok`, `/admin/egysegek`; M06.
- [ ] V11 `/admin/felhasznalok`, `/admin/felhasznalok/[id]`, jóváhagyás; M12.
- [ ] V12 `/admin/arak` két nézete, globális kedvezmény előnézete; `/admin/kiemelt`; M07/M08.
- [ ] V13 `/admin/support`, `/admin/support/[id]`; M18.
- [ ] V14 `/kapcsolat`, `/info/szallitas`, `/info/gyik`, `/info/katalogus`; M14.
- [ ] V15 `/info/impresszum`, `/info/aszf`, `/info/adatkezeles`, `/info/elallas`; M14.
- [ ] V16 `/info/akciok`: aktuális ajánlatok D07 szerint, a meglévő partnerárazással; ajánlat nélkül üres állapot; M14/M20.
- [ ] V17 `/allapotok`, not-found: üres, nincs találat, töltés, helyreállítható hiba, mezőhiba, 403/404; komponensgaléria védett, állapotok a tényleges oldalakon is; M05/M21.
- [ ] V18 `/email-minta` vizuális alap -> valódi email HTML/text sablon; M16.
- [ ] V19 `/telefon` bemutató helyett valódi reszponzív oldalak, `/dizajn` helyett belső komponens/oldaljegyzék; demo segédoldalak nem nyilvános éles funkciók; M05/M21.
- [ ] V20 ZIP-ben hiányzó új nézetek: banneradmin, email/hírlevél, automatizmusok, kosárfigyelés, aktivitás, kibővített chat; ugyanaz a designrendszer; M14-M18.
- [ ] V21 Terméken állítható elérhetőségi státusz, webshopjelzés és aktuális ajánlatok adminja; M19/M20. Mennyiségi készlet/foglalás és külön akció-árazómotor nem része a mostani scope-nak.

Minden UI ellenőrzésnél: helyes magyar szöveg, olvasható ékezetek, pénz/mennyiség/dátum formázás, tartalomba férő hosszú címek, látható fókusz, betöltés/hiba/üres állapot, működő navigáció, konzolhiba-mentesség és hiánytalan képek. A ZIP nézetarányai megőrzendők, a szükséges eltéréseket indokolni kell.

## Folyamatos üzemeltetés

Az alábbi pontok ismétlődnek. Egy teljesített hónap nem jelenti, hogy a feladat örökre kész. M21-ben felelős, gyakoriság és konkrét vállalás készül; minden ciklus külön naplóbejegyzést kap. Ez a dokumentum nem hoz létre automatikus emlékeztetőt és nem vállal személyes rendelkezésre állást.

| ID | Feladat | Rögzítendő eredmény |
| --- | --- | --- |
| U01 | Heti 5 nap megállapodás szerinti rendelkezésre állás | Idősáv, helyettesítés, bejelentések és válaszidők |
| U02 | Kritikus hibák aznapi kezelése | Jelzés, megkezdés, enyhítés/helyreállítás és lezárás időpontja |
| U03 | Magas prioritású hibák megkezdése 1 munkanapon belül | Prioritás, határidő és tényleges kezdés |
| U04 | Havi 1-2 személyes meeting | Dátum, résztvevők, döntések és checklistre visszahivatkozás |
| U05 | Havi teljesítési riport | Kiadások, hibák, SLA, mérések, kapacitás, következő feladatok |
| U06 | Laravel és csomagfrissítés | Audit, változtatás, teszt, staging/éles kiadás |
| U07 | Biztonsági ellenőrzés | Hozzáférések, függőségek, sérülékenységek és javítások |
| U08 | Backup és visszaállítás ellenőrzés | Mentés állapota, próba dátuma, helyreállíthatóság |
| U09 | Logok, worker, scheduler, email ellenőrzés | Hibák, késések, sikertelen feladatok és intézkedések |
| U10 | Teljesítmény és tárhely | Válaszidő, adatbázis, képek, levelek, tároló és bővítési szükség |

## Kiadási napló

Kiadáskor új sor kerül ide. Nem szabad előre sikeresnek kitölteni.

| Dátum | Mérföldkő | Állapot | Commit SHA | Workflow / futás | Staging próba / bizonyíték | Következő pont |
| --- | --- | --- | --- | --- | --- | --- |
| 2026-10-13 | M00 | Dokumentáció elkészült, nincs deploy | Nincs | Nem futott | Nem alkalmazható | M01.01 |
| 2026-10-14 | M00/M01 | Developra integrálva, CI sikeres; deploy kihagyva | 7ad56f6823a6c5e4a45b5fae79b4a4c2c0398a57 | [#26](https://github.com/sykolos/eb-webshop/actions/runs/38520202165) | Nincs új staging SHA; deploy=skipped | M01.01/M01.02/M01.04/M01.09 |
| 2026-10-14 | M01 / D14 | STAGINGEN, meglévő deploybeállításokkal | 74d54f5296531370873f9182d10ead0b003d86ea | [#36](https://github.com/sykolos/eb-webshop/actions/runs/38574327623) | verify és deploy success; /, /contact, /login HTTP 200 CI-ban és külön helyi próbával | M01.08, majd M02; D14 halasztások megőrzése |
| 2026-10-14 | M02.01 | Részkiadás stagingen; rendelés-tulajdonosvédelem | d9c0cc16393fd585beab8f52e8881448881357c0 | [#41](https://github.com/sykolos/eb-webshop/actions/runs/38585205515) | verify és deploy success; idegen részlet/PDF 403, saját részlet 200 a célzott tesztben | M02.02; M01.08 élő partner/admin próba tovább nyitott |
| 2026-10-14 | M02.02 | Részkiadás stagingen; nyilvános `/clear` eltávolítva | 08e4d5102a1312a7a49a77f3c614e752c925e3ab | [#45](https://github.com/sykolos/eb-webshop/actions/runs/38586495639) | verify és deploy success; `/clear` 404 regressziótesztben | M02.03; M01.08 élő partner/admin próba tovább nyitott |
| 2026-10-14 | M02.03 | Részkiadás stagingen; kosár- és kiszerelésvalidáció | ee86f8ebe8c5b2e947ea2fc8c3fd7ab39a63394f | [#49](https://github.com/sykolos/eb-webshop/actions/runs/38588688231) | verify és deploy success; `/`, `/contact`, `/login` HTTP 200 | M02.04; M01.08 élő partner/admin próba tovább nyitott |
| 2026-10-14 | M02.04 | Stagingen; tranzakciós checkout és idempotens beküldés | a54ac7aceb605924e994874f754697b1801306b9 | [#55](https://github.com/sykolos/eb-webshop/actions/runs/38591973104) | verify és deploy success; `/`, `/contact`, `/login` HTTP 200; CI checkout-regressziók zöldek | M01.08 élő partner/admin próba, majd M02.05 |

Kiadási bejegyzéshez szükséges részletek:

- Dátum és felelős; mérföldkő és tényleges funkciók.
- Feature branch, commit(ok), developra jutott SHA; workflow URL/azonosító és eredmény.
- Staging URL és ellenőrzött SHA; tesztparancsok/eredmények, képernyőképek vagy más bizonyítékok útvonala.
- Migráció, környezeti változó neve (titkos érték nélkül), worker/cron/feature flag változás.
- Visszaállítás módja és utolsó használható kiadás.
- Ismert hibák, halasztások és következő pontos feladatazonosító.

## Folytatási napló

### 2026-10-13: checklist létrehozása

- Elvégzett: ZIP és igénylista egyesítése; funkciók forrása, eltérések, 21 fejlesztési mérföldkő és kiadási kapuk.
- Alkalmazás módosítása: nincs. Git commit/push: nincs. Staging/éles deploy: nincs.
- Ellenőrzés: forrásfájlok és meglévő develop workflow átnézve; 328 checkbox azonosítójának egyedisége, minden M01-M21 ponthoz hat kiadási kapu, explicit feladathivatkozások és helyi Markdown-linkek ellenőrizve. A listával való lefedettség kézzel összevetve. Alkalmazásteszt nem futott, alkalmazáskód nem változott.
- Nyitott: D01-D12 üzleti döntések; ezek a megfelelő mérföldkő előtt kérendők, nem egyszerre és nem mind a kezdés előtt. D13 lezárva, chat nem készül.
- Következő: M01.01; a staging aktuális állapota, hozzáférése és izolációja felmérendő.
- Meglévő untracked anyagok: docs/, .codex-inspect/, electrobusiness-v6-local.zip és electrobusiness-v6-local/. Célzott stage szükséges, a referenciaanyagok nem automatikusan részei a commitnak.

### 2026-10-13: felhasználói döntések átvezetése

- D01/D05: jelenlegi árazási, kedvezményes és mennyiségi működés megőrzése; teljes szerkesztési szabadság a meglévő felhasználó/termék/termékkör lehetőségekkel.
- D02/D03: tetszőleges kategóriamélység és ZIP szerinti hozzáférés.
- D04: tényleges munkakörök, konkrét névsor később; D08: hero/úszó képek, aktuális akciók és kategóriák, próbaképek használhatók.
- D06/D07: M19 átírva kézi termékelérhetőségre; M20 ajánlatmegjelenítés, új árképzési motor nélkül. A korábbi mennyiségi készlet/akciómotor-feladatokat ez a döntés felváltja, azonosítóik a szűkített scope feladatait követik.
- D09: ZIP-szerű egyszerű törlési UI felvetve, visszaállítható megoldás ajánlva; fizikai végleges törlés nem jóváhagyott.
- D10 külön pontosítva: nettó forgalmi top termékek ÉS utolsó rendelésen alapuló inaktivitás.
- D11: részletes tracking elfogadott terjedelem; szolgáltatóválasztás és megőrzés M15-ben, hozzájárulás/tájékoztatás az éles indulás előtt; három új M15 feladat.
- D12: később. D13: elengedve, a ticketrendszer marad. Alkalmazáskód, commit, push és deploy most sem változott.
- Ellenőrzés: 331 egyedi checkbox, ebből 179 fejlesztési feladat és változatlanul 126 kiadási kapu; minden M01-M21 kapu megvan, nincs duplikált/feloldatlan feladatazonosító vagy hibás helyi Markdown-link. Csak dokumentációs ellenőrzés, alkalmazásteszt nem futott.
- Következő fejlesztési feladat változatlan: M01.01. A korábbi, M00 létrehozásánál rögzített checkbox-darabszám történeti adat.

### 2026-10-13: projektbeli fejlesztési skill

- Létrehozva: `.agents/skills/electrobusiness-development/SKILL.md`, a skill UI-metaadata és gyökér `AGENTS.md` a felismeréshez és közvetlen betöltéshez.
- A skill a checklist aktuális állapotából és döntéseiből folytat, a releváns ZIP-forrást használja, végrehajtja az adott mérföldkövet, és frissíti a kiadási/folytatási naplót.
- A generikus folytatás következő elkezdhető mérföldkőre szól. A Git/staging lépéseket a már megadott felhatalmazással végzi; éles deployt és ütemezett háttérmunkát önmagában nem indít.
- Ellenőrzés: a skill-creator `quick_validate.py` ellenőrzése sikeres; az UI YAML érvényes és az implicit kiválasztás engedélyezett; a helyi fájlhivatkozások és mind a 21 mérföldkő kiadási kapui ellenőrizve. A validátor PyYAML-függősége elkülönített ideiglenes könyvtárba került, az alkalmazás függőségei nem változtak. Valódi fejlesztési/deploy próbát ez a dokumentációs feladat nem indított.
- Nincs alkalmazáskód-változás, commit/push vagy deploy. Következő fejlesztési pont: M01.01.

### 2026-10-13: M01 helyi staging-alap implementáció

- Munkaág: `codex/m01-staging-foundation`, alap HEAD: `2eb0798`. A korábbi docs/skill/README változtatások megőrizve. Nincs stage, commit, push vagy távoli deploy.
- Olvasási próba a meglévő stagingen: `/`, `/contact`, `/login` HTTP 200, Basic hozzáférés-védelem és X-Robots-Tag nélkül. Ez a korábbi szerverállapot, nem az új kód kiadása. PHP/web/DB/queue elkülönítés és tárhelykapacitás még nem igazolt.
- Új `config/staging.php`, `StagingAccess` globális middleware és `RedirectStagingMail` listener: csak staging környezetben Basic belépőkapu, noindex/no-store, SHA-fejléc, illetve tesztcímzettre irányított Laravel levelek. Hiányzó beállításnál zárt hozzáférés/nincs levélküldés. Statikus média szerveroldali védelme külön előfeltétel.
- Új `.env.staging.example`; izolált cookie/cache/session/queue elvárások a CLI deploy-ellenőrzésben. `.gitignore` kizárja a lokális ZIP-et, kibontást, inspect anyagot és környezetfájlokat. A már követett `.env.develop` ettől még követett marad, privát titokvizsgálata szükséges; tartalma nem került kimenetbe.
- Workflow: külön verify job dev függőségekkel és tesztekkel; deploy no-dev PHP csomaggal és ugyanazon SHA frontend artifactjával. Rögzített SSH-hostkulcs szükséges. Staging environment, szerveroldali deploy-lock, csomag SHA256 és korábbi kódmentés. Új `package.sh`, `remote.sh`, `smoke.php`; shellfájlok LF sorvége rögzítve.
- `STAGING_DEPLOY_ENABLED=true` nélkül az új workflow nem deployol. Kötelező új titoknevek: `SSH_KNOWN_HOSTS`, `STAGING_ACCESS_USER`, `STAGING_ACCESS_PASSWORD`; szerverenvben jelszóhash és `STAGING_MAIL_TO`. A meglévő `.env` nem íródik felül az `ENV_DEVELOP` titok módosításától. A teljes előfeltétellista és visszaállítás a DEPLOYMENT.md-ben.
- Tesztizoláció: kényszerített SQLite `:memory:`, üres DATABASE_URL és külön config-cache útvonal; boot utáni ellenőrzés rossz DB esetén még a migráció előtt leáll. A meglévő kedvezményindex-migráció SQLite indexvizsgálatot kapott; MySQL ága és üzleti árazás változatlan. Nincs új adatbázisséma/migráció.
- Ellenőrzés: `php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit` sikeres, 24 teszt / 84 assertion. Lefedett: staging belépés/hiba/noindex, normál környezet változatlansága, mail sink és eredeti címzett megtartása stagingen kívül, vendég route-ok, ismételhető indexmigráció, csomagtartalom/kizárás/SHA256 és szintetikus kicsomagolás, korábbi entrypoint tesztek.
- `npm run build` sikeres (Vite 5.4.19); meglévő Sass/Bootstrap deprecation figyelmeztetések, ezek M05/M21 témái. PHP lint, Bash `-n`, workflow YAML parse és verify-függőség, valamint `git diff --check` sikeres.
- Korlát: helyi PHP 8.4.7, CI cél 8.2.31; a célverziós tiszta checkout/CI még nem futott. Helyi ShellCheck nincs telepítve, CI tartalmazza az ellenőrzést. MySQL migráció, valódi kód/DB/média-visszaállítás, partner/admin bejelentkezett staging próba és távoli titkok/jogosultságok ellenőrzése nem történt meg. A szintetikus csomagteszt nem helyettesíti a mentéspróbát.
- Következő: M01.01/M01.02 szerveroldali felmérés és beállítás, M01.04 megbízható hostkulcs, M01.09 mentés-visszaállítás. Ezután célzott commit/CI és engedélyezett staging deploy, SHA + partner/admin ellenőrzés. M02 nem kezdődött el; M01 kiadási kapui nyitva maradtak.

### 2026-10-13: hozzáférési diagnózis helyesbítése és másik feladat kiadása

- A GitHub-hozzáférés működik. A mostani hibás próba `GIT_SSH_COMMAND` felülírással kihagyta a repó dedikált kulcsát és a korábban beállított Windows OpenSSH klienst. Ez tesztelési hiba volt, nem elveszett jogosultság; a tartós Git-config nem változott.
- A működő beállítást a `git config --show-origin --get core.sshCommand` mutatja: Windows OpenSSH, repohoz rendelt `eb-webshop` kulcs és `IdentitiesOnly=yes`. Diagnózis előtt ezt kell megnézni; ne cseréld általános `ssh` parancsra. Privát kulcs tartalmát ne olvasd/naplózd.
- Ellenőrzés: sima `git ls-remote origin refs/heads/develop` és `git fetch origin develop` sikeres. A GitHub Actions publikus API token nélkül visszaadta a #23 futás `completed / success` eredményét: https://github.com/sykolos/eb-webshop/actions/runs/38470167150 .
- A „Javítsd a sikertelen deployt” feladat már kiadta a `936fd9ed31504833b63f9a7b5f7059735206b8de` commitot. Annak naplója sikeres buildet, 12 deploymenttesztet és három HTTP 200 próbát tartalmaz; most az Actions-eredményt ellenőriztük újra, nem az M01-et deployoltuk.
- Az origin/develop egy committal előrébb jár a jelenlegi M01 ágnál: workflow-javítás, új `scripts/deploy/ensure-app-key.php` és két entrypoint teszt. A következő M01 kiadás ELŐTT ezeket össze kell vetni a helyi módosításokkal; a munkafa felülírása vagy vak develop push tilos.
- Közvetlen staging SSH-hozzáférés és az M01 új titkainak beállítása külön kérdés a már működő Git push/Actions útvonaltól. A GitHub deploytitkok hiánya a helyi környezeti változók között nem bizonyítja a távoli titkok hiányát. Nem szükséges új GitHub-kulcsot kérni a felhasználótól.
- Ebben a folytatásban nincs alkalmazáskód-változás, commit vagy push. A deploy előzetes konfiguráció-ellenőrzésének előrehozása még tervezett munka; nem készült el. Az izolált Linux-próbához Docker Desktop indítását kértük, konténeres teszt nem futott.
- Következő: a `936fd9e` javítás összevetése és megőrzése, M01 helyi deploy-preflight és ellenőrzések folytatása, majd a még ténylegesen hiányzó staging előfeltételek rendezése a meglévő Actions útvonalon.

### 2026-10-14: M01 preflight és kiadási hibaforgatókönyvek

- A `936fd9e` diffjét összevetettük: az `ensure-app-key.php` segéd és két tesztje bekerült a munkafába. Az új workflow nem generál automatikusan kulcsot meglévő telepítéshez, és nem veszi át az ellenőrizetlen feltöltésmásolás utáni törlést. Érvényes meglévő kulcs és storage-symlink változatlanul használható; döntés indoklása a DEPLOYMENT.md-ben.
- `remote.sh`: a csomag SHA/REVISION és a jelölt Laravel környezet ellenőrzése a karbantartás/kódcsere ELŐTT, privát ideiglenes könyvtárban történik. A tényleges szerverenv privát másolatát használja; a másolat és a feltöltött fallback kilépéskor eltűnik. Hibás konfiguráció nem állítja le az aktuális kódot. A preflight részletes naplója nem kerül CI-kimenetbe.
- További védelem: megosztott/symlinkelt runtime storage és párhuzamos deploy elutasítása; explicit nem üres törlési gyökér. Előre renderelt karbantartási oldal, public entrypoint megőrzése takarításkor, a karbantartási válasz a vendor létezésvizsgálata elé került. A tanúsítványhoz tartozó `.well-known` nincs a release csomagban.
- Új `tests/Deployment/remote.sh`: kilenc izolált Linux-forgatókönyv, köztük sikeres csere és snapshot-kicsomagolás, hibás env/path/checksum/revision, valódi public storage megőrzése, megosztott storage, lock és migrációhiba. PHP/Artisan szimulált, nem valódi DB-visszaállítás. CI-ba bekötve, új LF-szabály a shellteszthez.
- Helyi ellenőrzés: PHPUnit 27 teszt / 94 assertion sikeres; Bash `-n` és ShellCheck v0.10.0 sikeres; `git diff --check` sikeres. A ShellCheck hivatalos GitHub-release Windows binárisa elkülönített TEMP könyvtárban futott, nem került alkalmazásfüggőségbe.
- Helyi Linux-próba: Docker kezdetben válaszolt az info/image-list lekérdezésre, de a futtatás és a verziólekérdezés HTTP 500 engine hibával állt le. Konténeres teszt nem tekinthető lefutottnak; a Docker adatát/konfigurációját nem módosítottuk, nem reseteltük. Valódi GitHub CI és ShellCheck + Linux-teszt együtt még hátravan.
- Nincs stage/commit/push/deploy. A munkaági commit/push jóváhagyását külön kérdésben kértük; válasz eddig nincs. A developot nem módosítottuk. A távoli javítás munkafabeli megőrzése nem Git-merge: a jóváhagyott commit után az origin/develop őstörténetét is integrálni kell, a workflow eltérését tudatosan feloldva.
- Következő: a jóváhagyás után a célzott M00/M01 fájlok commitja, remote javítás integrációja és csak `codex/m01-staging-foundation` push/CI. Staging aktiválás csak az új környezeti előfeltételek és a tényleges DB/média-visszaállítási próba után. M01.K1-K6 továbbra sincs lezárva.

### 2026-10-14: Git kiadás engedélyezése

- A felhasználó engedélyezte a commit/push műveletet, a develop ágra is. Ezt ehhez az M01 kiadáshoz nem kell újra bekérni. Éles deployra ez nem felhatalmazás.
- Sorrend: célzott commit, `936fd9e` előzmény integrációja, munkaági CI, siker esetén develop push és a tényleges workflow/deploy eredmény ellenőrzése. Az új staging kapcsoló és szerveroldali előfeltételek ettől még szükségesek; a kihagyott deploy nem sikeres telepítés.

### 2026-10-14: M01 commit, CI-javítás és develop integráció

- A felhasználó engedélyével célzottan commitolt M00/M01: `57d176f`; a korábbi `936fd9e` javítás valódi merge-je: `fba9393`. A workflow ütközése az M01 ellenőrzött kiadási folyamatának megtartásával feloldva, a korábbi helper és tesztjei megőrizve. Nincs force push vagy idegen munkafa-változtatás felülírása.
- Munkaági [#24](https://github.com/sykolos/eb-webshop/actions/runs/38519752034): build, ShellCheck és mind a kilenc Linux-fájlrendszeres deployforgatókönyv sikeres; PHPUnit lépés hibás. A tiszta checkoutból hiányzó runtime könyvtárakat Gitben követett `.gitignore` helykitöltőkkel pótoltuk a cache/sessions/views mappákban: `7ad56f6`. Valódi cache/session adatok nem kerültek Gitbe.
- Munkaági [#25](https://github.com/sykolos/eb-webshop/actions/runs/38520082781) ezután teljesen sikeres. A tesztelt `7ad56f6823a6c5e4a45b5fae79b4a4c2c0398a57` commit fast-forward push-sal developra került. Develop [#26](https://github.com/sykolos/eb-webshop/actions/runs/38520202165): workflow success, verify success, deploy skipped.
- Ellenőrzés: helyben PHP 8.4 alatt 27 teszt / 94 assertion; GitHubon PHP 8.2.31 tiszta checkout, Composer install, npm ci/build, Bash/ShellCheck, kilenc szimulált Linux deployforgatókönyv és PHPUnit sikeres. Ezek nem jelentik egy valós MySQL-adatbázis vagy staging médiamentés visszaállítását.
- M00.05, M01.07 és M01.K3 teljesült. M01.K4 nem pipa: a commit/develop integráció megvan, de a feladat a sikeres staging deployt is megköveteli. M01 továbbra FOLYAMATBAN, nem STAGINGEN ELLENŐRZÖTT.
- A staging job feltétele nem teljesült: `STAGING_DEPLOY_ENABLED` nincs `true` értéken. Ezt nem kapcsoltuk be az igazolatlan DB/média-elkülönítés, megbízható SSH-hostkulcs, Basic/email-sink beállítás és mentés-visszaállítási próba helyett. Az utolsó igazolt szerverkiadás továbbra `936fd9e` (#23).
- A dokumentációs zárócommit csak a naplót és az AGENTS.md workflow-leírását frissíti; alkalmazáskódja megegyezik a #25/#26 által ellenőrzött állapottal. A munkaág továbbra `codex/m01-staging-foundation`; másik worktree develop ágát nem váltottuk át.
- Következő: meglévő Actions útvonalon a tényleges staging előfeltételek rendezése, M01.09 bizonyíték, majd a kapcsoló engedélyezése és engedélyezett staging deploy/HTTP SHA + partner/admin próba. Commit/push engedélyt ehhez az M01 kiadáshoz nem kell ismét kérni; éles deploy továbbra nincs engedélyezve.

### 2026-10-14: staging konfigurációaudit előkészítése

- A záró #27/#28 CI-futás sikeres, mindkét távoli ág `3059074`; a develop deploy lépése skipped. A korábbi megszakadt ellenőrzés lezárva.
- A korábbi hozzáférési és deployjavító beszélgetések alapján a működő út Git push -> Actions, nem közvetlen helyi staging SSH. Most sincs helyi staging SSH-hostconfig vagy a szükséges nevű hozzáférési környezeti változó; ez nem bizonyítja a GitHub titkok hiányát.
- Új `.github/workflows/staging-readiness.yml`: developon, a deployjal azonos staging environmentben kilenc kötelező titok jelenlétét ellenőrzi külön, név szerinti lépésekben. Nem ír ki titkos értéket, nem kapcsolódik szerverhez és nem módosítja a deploykapcsolót. Hiányzó beállításnál az audit szándékosan hibás; a build ellenőrzése külön marad.
- Ez az audit csak jelenlétet igazolhat, nem a hostkulcs hitelességét, a hitelesítő adatok helyességét, a környezet izolációját vagy a mentések visszaállíthatóságát. Az ENV_DEVELOP csak első telepítési fallback, nem kötelező egy meglévő szerverenv mellett.
- Következő: ellenőrzött audit commit/push a már engedélyezett M01 körben, futáseredményből pontos hiánylista; a szerveroldali kapuk továbbra nyitva.
- Helyi ellenőrzés: YAML parse, develop trigger és staging environment, kilenc egyedi step ID és hibagyűjtés; minden inline parancs Bash szintaxisa sikeres. Üres érték hibás, tesztérték és parancsszerű literál sikeres, kimenetük üres. Alkalmazáskód nem változott; távoli build/CI eredménye push után ellenőrizendő.
- Audit első commitja developon: `cf28e33`. [Első futás](https://github.com/sykolos/eb-webshop/actions/runs/38572610564) hiányzó beállítást jelzett, de a continue-on-error miatt az API a részlépések conclusion értékét successre módosította. A hibás részlépések láthatóságát javítottuk: nincs continue-on-error, minden ellenőrzés explicit nem-megszakított feltétellel lefut az előző hibája után is. Az első futásból pontos hiánylista nem állítható.
- Friss külső HEAD próba: `/`, `/contact`, `/login` HTTP 200; nincs X-Robots-Tag, X-Release-Sha vagy Basic challenge. Nincs M01 staging kiadás, nincs szerveroldali változtatás.

### 2026-10-14: staging konfigurációaudit eredménye

- Develop commit: `23739b87aa7d169035e9562daa292855797fc364`. [Konfigurációaudit #2](https://github.com/sykolos/eb-webshop/actions/runs/38572800669): hat régi titok elérhető (SSS_HOST, SSH_USER, SSH_PORT, SSH_PASSWORD, DEPLOY_PATH, PUBLIC_PATH); kizárólag SSH_KNOWN_HOSTS, STAGING_ACCESS_USER, STAGING_ACCESS_PASSWORD hiányzik a staging job számára.
- Az audit failure állapota a három hiányt jelzi, nem alkalmazásteszthibát. [Develop build #32](https://github.com/sykolos/eb-webshop/actions/runs/38572801059) sikeres. A szerverhez az audit nem kapcsolódott, ezért a jelszó aktuális érvényességét és az izolációt nem igazolja.
- A felhasználó ismét jogosan jelezte, hogy másik beszélgetésben már működött a push/deploy. Tisztázva: a #23 korábbi deploy sikeres volt; az M01 új védelmei és alapból kikapcsolt kapcsolója hozták létre az új előfeltételeket. Nem elveszett hozzáférés vagy új GitHub-kulcs szükséges.
- Biztonságos egyszeri konfigurációhoz böngészős GitHub/tárhely bejelentkezést vagy felhasználói beállítást kértünk, jelszó/token chatbe küldése nélkül. Git SSH-jogosultság nem biztosít GitHub titok-/változószerkesztési API-hozzáférést. Titkos értékeket az audit nem ír ki és nem másol helyi gépre.
- Következő: megbízható tárhelyi forrásból ellenőrzött SSH-hostkulcs, külön staging HTTP-belépő és megfelelő szerveroldali hash/email-sink/izoláció. A meglévő APP_KEY megőrzendő. M01.09 igazolt DB/média-visszaállítás után kapcsolható be a deploy; ennek hiányát az új audit nem helyettesíti. Éles rendszerhez nem nyúltunk.

### 2026-10-14: D14, a meglévő deploy visszaállítása

- A felhasználó nem kéri az új kiadási előfeltételeket, a korábbi működő utat választotta. Ez felülírja a megelőző napló aktiválási feltételeit és az egyszeri új GitHub/tárhely beállításra feltett kérdést; arra már nem szükséges válasz.
- Workflow: csak a meglévő hat SSH/path titok, korábbi SSH-opciók, nincs STAGING_DEPLOY_ENABLED vagy staging environment-kapu. A konfigurációaudit workflow törölve. A verify job és a célzott release-csomag megmarad.
- remote.sh a meglévő env mellett általános alkalmazásellenőrzést futtat, nem követeli meg a --staging paraméter szerinti új beállításokat. Nem indít queue:restart parancsot. Hash/REVISION, preflight, lock, kódmentés, env/média megőrzése és migrációhibánál maintenance változatlan.
- config/staging.php enabled=false: nem lép működésbe új Basic-auth vagy email-sink az APP_ENV értékétől sem. A kód és a célzott tesztjei későbbi munkához megmaradnak. Az alkalmazás meglévő belépése/jogosultságai nem változnak.
- HTTP próba visszaállítva a korábbi három nyilvános útvonal 200 válaszára, új credential/header-követelmény nélkül. AGENTS.md és DEPLOYMENT.md a tényleges D14 szerinti működést írja le, nem a visszavont követelményeket.
- Helyi PHPUnit: 28 teszt / 97 assertion sikeres, külön regresszió igazolja az extra védelem inaktív kiadási alapértékét. Linux-fixture meglévő, nem staging nevű envvel és új titkok nélkül tesztel, érvénytelen kulcsot továbbra elutasít. Nincs új migráció és nincs szerverenv-/adatmódosítás előírva.
- Következő: diff/szintaxisellenőrzés, engedélyezett commit/push és tényleges deploy megfigyelése. Ennek sikerét még nem állítjuk.

### 2026-10-14: sikeres staging kiadás a meglévő beállításokkal

- A `74d54f5` munkaági [#35](https://github.com/sykolos/eb-webshop/actions/runs/38574120283) ellenőrzése teljesen sikeres, utána fast-forward push developra. Develop [#36](https://github.com/sykolos/eb-webshop/actions/runs/38574327623): verify success, deploy success, a meglévő SSH-val telepítés és a Check deployed website lépés is sikeres. Nincs új secret/env/kapcsoló beállítva.
- A workflow a csomag REVISION értékét a `74d54f5296531370873f9182d10ead0b003d86ea` SHA-val a szerveren összeveti, majd a telepítést befejezi. A nyilvános HTTP-próba ettől külön bizonyíték, nem állít HTTP-fejléces SHA-ellenőrzést.
- Helyi PHPUnit 28 teszt / 97 assertion; CI-ban PHP 8.2.31, build, Bash/ShellCheck, kilenc Linux deployforgatókönyv és PHPUnit sikeres. YAML/deployfeltétel/új titkok hiánya helyben ellenőrizve.
- Deploy után külön GET-próba: /, /contact, /login HTTP 200. A helyi PHP alapértelmezett CA-beállítása curl 60 hibát adott; a telepített Git ca-bundle.crt fájljával ugyanaz a smoke.php sikeres. TLS-ellenőrzést nem kapcsoltunk ki, rendszerbeállítást nem módosítottunk.
- M01.K4 teljesült; M01 állapota STAGINGEN. A bejelentkezett partner/admin próba nincs lefuttatva, a D14-ben halasztott izoláció/hostkulcs/DB-média-visszaállítás nincs késznek jelölve. Éles deploy nem történt.
- Az utólagos napló külön dokumentációs commit a munkaágon, és csak oda kerül pushra: nem indítunk pusztán naplózás miatt új staging deployt. Develop és a szerver marad `74d54f5`; a napló a következő rendes develop integrációval jut át.
- Következő: M01.08 és fennmaradó átadási ellenőrzések, majd M02. Nem szükséges új GitHub-/tárhelybeállítást kérni a már működő kiadási út használatához.

### 2026-10-14: M01.08 alkalmazásszintű login-ellenőrzés

- A `tests/Feature/ExampleTest.php` két reprodukálható ellenőrzést kapott: partner belépés után `/account` 200, `/adminpanel` visszairányítás; admin belépés után `/adminpanel` 200. A tesztek izolált SQLite adatbázist és gyári felhasználót használnak, staging vagy ügyféladat nélkül.
- Helyi ellenőrzés: teljes PHPUnit `30 teszt / 105 assertion` sikeres. Frontend- és deployfájl nem változott; CI teljes ellenőrzése a commit után esedékes.
- M01.08-at nem pipálom ki: a valódi stagingen dedikált partner/admin felhasználókkal végzett próba nem történt meg. Az alkalmazásszintű szerződés most automatikusan védett.
- Következő: célzott commit és a már működő develop -> staging folyamaton kiadás; utána M02.01 csak az élő staging-próba lezárása után.

### 2026-10-14: M02.01 staging kiadás

- A `d9c0cc1` munkaági [#40](https://github.com/sykolos/eb-webshop/actions/runs/38585050763) verify teljesen sikeres; develop push után [#41](https://github.com/sykolos/eb-webshop/actions/runs/38585205515) verify és deploy is sikeres.
- A stagingre kiadott SHA `d9c0cc16393fd585beab8f52e8881448881357c0`. Az M02.01 policy és controller-ellenőrzés benne van; adatbázis-migráció nem futott, meglévő rendeléseket nem módosított.
- Helyi teljes PHPUnit: 31 teszt / 108 assertion. CI-ban build, ShellCheck, kilenc Linux deploy-forgatókönyv és PHPUnit sikeres. A kiadás a meglévő deploybeállításokkal történt.
- M02.01 stagingen van, de M02 teljes mérföldköve és a kiadási kapui nyitva maradnak. M01.08 valódi staging partner/admin belépése továbbra nincs igazolva.
- Következő fejlesztési pont: M02.02, a publikus `/clear` végpont eltávolítása; utána ismét célzott CI és staging kiadás.

### 2026-10-14: M02.02 nyilvános cache-törlés eltávolítása

- A `routes/web.php` nyilvános `/clear` útvonala törölve. Cache-törlés kizárólag a meglévő deploy CLI-folyamatban maradt.
- Új regresszióteszt igazolja, hogy `/clear` 404-et ad. Helyi teljes teszt a változtatás után futtatandó; nincs migráció és nincs adatváltozás.
- Következő: célzott CI, develop integráció és a működő staging kiadási út ellenőrzése. M02.03 a következő alkalmazásfeladat.

### 2026-10-14: M02.02 staging kiadás

- A `08e4d51` munkaági [#44](https://github.com/sykolos/eb-webshop/actions/runs/38586185152) verify sikeres; develop push után [#45](https://github.com/sykolos/eb-webshop/actions/runs/38586495639) verify és deploy sikeres.
- A stagingre kiadott SHA `08e4d5102a1312a7a49a77f3c614e752c925e3ab`. A `/clear` route eltávolítása és a 404 regresszióteszt a csomagban van; a deploy CLI cache-törlése változatlan.
- Helyi teljes PHPUnit: 32 teszt / 109 assertion. CI-ban build, ShellCheck, kilenc Linux deploy-forgatókönyv és PHPUnit sikeres. M01.08 élő partner/admin belépési próba továbbra nincs igazolva.
- M02.02 stagingen van, M02 teljes mérföldköve még nyitott. Következő fejlesztés: M02.03 kosármennyiség-, termékazonosító- és kiszerelésvalidáció.

### 2026-10-14: M02.01 rendelés-tulajdonosvédelem

- A fiók `/account/details/{id}` és `/account/getpdf/{id}` végpontjai eddig bármely bejelentkezett felhasználónak betöltötték az azonosítóval megadott rendelést. Ez jogosulatlan rendelés- és PDF-hozzáférési kockázat volt.
- Elkészült `OrderPolicy::view`, az explicit policy-regisztráció és mindkét controller-ágon az authorize ellenőrzés. A rendelés tulajdonosa továbbra hozzáfér, más partner 403-at kap; adminfelületi rendeléskezelés nem változott.
- Új regresszióteszt: idegen rendelés részlete és PDF 403, tulajdonos részlete 200. Helyi teljes PHPUnit: 31 teszt / 108 assertion sikeres. Nincs migráció, meglévő rendelésadat nem változik.
- M02.01 kód kész és helyben ellenőrzött; kiadása a már működő develop -> staging útvonalon következik. M01.08 valódi staging partner/admin belépése továbbra nyitott.

### 2026-10-14: M02.03 kosárvalidáció staging kiadás

- A kosárba helyezés szerveroldalon kötelező pozitív egész `quantity` értéket fogad el. A termékazonosító numerikus route-paraméter, az ismeretlen termék 404; a gyorskosár és a régi termékoldal route-formája megmaradt.
- A kliensből érkező `q/m` paraméterek nem kerülnek üzleti adatként a kosárba. A `q` kiszerelési szorzó és az `m` mértékegység a termék adatbázisban tárolt `product_unit` rekordjából származik; a meglévő rugalmas partnerár-számítás nem változott.
- A kosár +/- módosítása csak `-1` vagy `+1` lépést fogad el; hibás index és hibás lépés validációs hibát ad, a meglévő mennyiség nem módosul. Négy új Feature regresszióteszt fedi le a hozzáadást, hibás mennyiséget, ismeretlen terméket és léptetést.
- Módosított fájlok: `app/Http/Controllers/CartController.php`, `routes/web.php`, `tests/Feature/ExampleTest.php`. Nincs migráció, árváltozás, session-struktúra-átállás vagy deploy-környezeti előfeltétel.
- Helyben PHP lint és `git diff --check` sikeres. A helyi PHPUnit adatbázisteszt nem indult el, mert a gépen nincs PDO SQLite-driver; a feature [#48](https://github.com/sykolos/eb-webshop/actions/runs/38588072011) és develop/staging [#49](https://github.com/sykolos/eb-webshop/actions/runs/38588688231) verify/deploy sikeres. A staging `/`, `/contact`, `/login` végpontjai HTTP 200 választ adtak.
- Az alkalmazáskód SHA-ja `ee86f8e`; a checklistet rögzítő docs-only kiadás `da04511`, amely mindkét távoli ágat és a staginget jelenleg vezeti. M02.03 stagingen ellenőrzött; M02 közös kapui nyitva maradnak, mert M02.04-M02.08 még nincs elkészítve. M01.08 valódi partner/admin staging-belépési próba külön nyitott elfogadási pont.
- Következő konkrét fejlesztés: M02.04 checkout tranzakció, ismételt beküldés elleni védelem, hiányzó számlázási adatok és megszűnt termék kezelése; az M01.08 élő login-próba dedikált tesztadatot igényel.

### 2026-10-14: M02.04 checkout védelem staging kiadás

- A checkout folyamat `CheckoutService`-be került: a felhasználói szállítási cím, a számlázási adatok, a kosártermékek, az aktuális rugalmas partnerárak és a rendelési tételek ellenőrzése/mentése egy célzott üzleti rétegben történik.
- Új `orders.checkout_token` nullable UUID mező és egyedi index készült. A checkout űrlap egy beküldéshez UUID kulcsot kap; azonos kulccsal ismételt vagy párhuzamos beküldés ugyanazt a rendelést használja, és nem küld új email/PDF értesítést. A régi rendelések migráció után is érvényesek maradnak.
- A rendelés és minden tétele tranzakcióban készül. Hiányzó számlázási adat, idegen/nem létező szállítási cím, hibás kosártétel vagy törölt termék validációs hibával tér vissza; részleges rendelés nem marad az adatbázisban. A kiszerelés és mértékegység továbbra is az adatbázisból származik, a speciális ár- és termékköri kedvezmények változatlanok.
- Új Feature tesztek készültek a hiányzó számlázásra, törölt termékre és token-idempotenciára. PHP lint és `git diff --check` sikeres; a helyi PHPUnit a hiányzó PDO SQLite-driver miatt nem indítható, a CI a kiadási ellenőrzés.
- Módosított fájlok: `app/Services/CheckoutService.php`, `app/Http/Controllers/Checkout.php`, `app/Http/Controllers/PagesController.php`, `resources/views/pages/checkout.blade.php`, `database/migrations/2026_10_14_160000_add_checkout_token_to_orders_table.php`, `tests/Feature/ExampleTest.php`, ez a checklist.
- A feature [#54](https://github.com/sykolos/eb-webshop/actions/runs/38591732597) és a develop/staging [#55](https://github.com/sykolos/eb-webshop/actions/runs/38591973104) workflow sikeres. A staging `/`, `/contact`, `/login` végpontjai HTTP 200-at adtak.
- Állapot: M02.04 stagingen, M02 közös K1-K6 kapui a további M02 pontok miatt nyitva. Következő konkrét fejlesztési feladat: M01.08 élő staging partner/admin login-próba dedikált tesztadatokkal; utána M02.05.

### 2026-10-14: M02.05 státuszátmenetek staging kiadás

- Az admin rendelés-státusz módosítása eddig közvetlenül mentette a kliensből érkező szöveget. Az `Order` modell most központi státuszlistát, kompatibilis régi `pending` értéket és átmeneti gráfot ad; az `OrderController` validálja az értéket és az aktuális állapotból engedélyezett következő lépést.
- A lezárt `kiszállítva` és `törölve` állapotokból nincs visszalépés. A meglévő üzleti státuszok és a változatlanra küldött státuszfrissítés megmaradnak. Az admin részletező nézet a megengedett következő értékeket mutatja.
- Új Feature regresszióteszt fedi le a `függőben` -> `feldolgozás` átmenetet, a tiltott visszalépést és az ismeretlen státuszt. PHP lint és `git diff --check` sikeres; a helyi PHPUnit a hiányzó PDO SQLite-driver miatt nem tudott adatbázist indítani.
- M01.08 valódi staging partner/admin próba továbbra nincs végrehajtva, mert dedikált staging hitelesítő adatok nem állnak rendelkezésre ebben a környezetben. Az alkalmazásszintű login-szerződés korábbi CI-tesztje megmarad, de ez nem helyettesíti az élő fiókos átvételt.
- A feature [#58](https://github.com/sykolos/eb-webshop/actions/runs/38614946203) és a develop/staging [#59](https://github.com/sykolos/eb-webshop/actions/runs/38615137385) workflow sikeres. A staging `/`, `/contact`, `/login` végpontjai HTTP 200-at adtak.
- Állapot: M02.05 stagingen ellenőrzött; M02 közös K1-K6 kapui a további M02 pontok miatt nyitva. Következő konkrét fejlesztési feladat: M02.06 auth- és email-végpontok próbálkozáskorlátja, session- és jelszóvédelem. M01.08 élő partner/admin login-próba dedikált tesztadatokkal továbbra nyitott.

### 2026-10-14: M03 teljes staging kiadás

- A Laravel 12 upgrade első develop kiadása (`db518ec`, [#78](https://github.com/sykolos/eb-webshop/actions/runs/38626390035)) a verify után 500-as smoke eredménnyel állt meg. A staging hiba oka `ViteManifestNotFoundException` volt: Laravel 12 a belső `myapp/public/build/manifest.json` fájlt kereste, miközben a meglévő deploy-út külön publikus könyvtárat használ.
- A javító commit `c82965abf24753c309450710534fcdfae1e2abcc` a `public/index.php` belépési pontban a Laravel 12-höz szükséges explicit `$app->usePublicPath(__DIR__)` beállítást használja; a deploy-entrypoint teszt-fixture ehhez igazítva maradt kompatibilis.
- Helyi PHPUnit: 46 teszt / 215 assertion sikeres; PHP lint és diff-check sikeres. A feature [#79](https://github.com/sykolos/eb-webshop/actions/runs/38627176724) és a develop/staging [#80](https://github.com/sykolos/eb-webshop/actions/runs/38627334532) verify/deploy sikeres.
- Staging ellenőrzés: `/`, `/contact`, `/login` HTTP 200; a főoldal CSS/JavaScript build assetjei HTTP 200; az admin dashboard (`/adminpanel`) és navigáció betöltött a már bejelentkezett admin munkamenettel.
- M03 állapota STAGINGEN ELLENŐRZÖTT. Nincs migráció, új környezeti előfeltétel vagy külön kiadási napló. Következő konkrét fejlesztés: M04.01 közös Form Request/Policy/Service/Controller minta.

### 2026-10-14: M06 terméktörzs és kategóriafa kiadásra kész

- Elkészült az M06 teljes admin csomagja: termékmodell-bővítés, publikációs és D06 szerinti elérhetőségi státuszok, archiválás, tetszőleges mélységű kategóriafa ciklusvédelemmel, kereséssel/összecsukható hierarchiával/breadcrumbdel, szűrődefiníciók és ősöröklés, egységvédelem, médiafeltöltés és régi főképek importja.
- A termékszerkesztő V6 admin felületén külön alapadat, műszaki és kép/szűrő fülek készültek. A média sorrendje, előnézete, törlése, valamint kép publikus/privát tárolása adminból kezelhető. A régi speciális árak, csomagolási adatok és rendelési előzmények változatlanul megmaradnak.
- Service-réteg: CategoryTreeService, FilterCatalogService, ProductCatalogService, UnitCatalogService; a termék- és kategóriaadmin a közös törzsadatot használja. A publikus katalógus csak publikált, nem archivált terméket ad vissza; kategóriaszűrés a teljes részfára működik.
- Új migrációk: 2026_10_14_180000_expand_catalog_for_m06 és 2026_10_14_180100_create_catalog_filters_and_media; visszagörgetésük a bővített oszlopokat és M06 táblákat eltávolítja, a meglévő products.image értékeket a médiatárba importálják. Új környezeti változó vagy feature flag nincs.
- Ellenőrzés: M06 célteszt 6/6; teljes PHPUnit 57 teszt / 237 assertion; npm run build, PHP lint, route-list, Blade view cache és git diff --check sikeres. A SQLite PDO modult explicit CLI kapcsolóval kellett betölteni, mert az alap PHP CLI konfigurációból hiányzik.
- Állapot: M06 KIADÁSRA KÉSZ; M06.K1-K3 kész. M06.K4-K6 még nincs rögzítve: a következő lépés egyetlen develop integráció/push, a meglévő staging workflow megvárása, majd a bejelentkezett admin termék/kategória/egység/média ellenőrzése asztali és mobil nézetben.

### 2026-10-14: M06 első staging deploy javítása

- A 06e72b6 verify jobja sikeres volt, a staging deploy azonban a product_media.product_id idegenkulcs létrehozásánál megállt errno: 150 hibával. A deploy előtti preflight és a rollback működött: a staging szolgáltatás karbantartási mód nélkül, a korábbi kiadással maradt elérhető.
- A javítás az új M06 pivot/média kapcsolati oszlopokat idegenkulcs nélkül hozza létre, mert a meglévő legacy táblák azonosító-típusa stagingenként eltérhet. A kapcsolatok Eloquent oldalon explicit kulcsokkal működnek, az adatbázis nem töröl történeti terméket/kategóriát kaszkáddal.
- Következő: a javító commit developra pusholása, a workflow sikerének megvárása, majd az M06 admin staging ellenőrzése. Sikertelen #106 után új deploy futás szükséges; a korábbi kiadás visszaállítása megtörtént. A #107 sikeres deploy után a terméklista, kategóriafa és egységadmin betöltött, a termék létrehozó nézet Blade-kompatibilitási hibája külön javítást igényel.

### 2026-10-15: M06 staging ellenőrzés lezárva

- A termékűrlap Laravel 12-kompatibilitási javítása elkészült: a rövid `@php(...)` helyett blokkos Blade-szintaxis került a nézetbe. A `7dc6771` commit developra került, a [#109](https://github.com/sykolos/eb-webshop/actions/runs/38644876368) verify és deploy jobja sikeres.
- Stagingen cache-busteres új oldalbetöltéssel az `/adminpanel/products/create` 200-as admin nézetként betöltött; az Alapadatok, Műszaki adatok és Képek és szűrők fülek működtek. A terméklista 280 terméket, a kategóriaadmin 20 csomópontot, az egységadmin 41 egységet mutatott.
- Reszponzív ellenőrzés: a termékűrlap 390x844, 768x1024 és 1440x900 viewporton betöltött, vízszintes túlcsordulás nélkül. Helyi ellenőrzés ismét sikeres: 57 PHPUnit teszt / 237 assertion, Blade cache, Vite build és diff-check.
- M06 állapota STAGINGEN ELLENŐRZÖTT; M06.K1-K6 teljesült. Ismert korlát: a legacy azonosító-típusok miatt az új katalóguskapcsolatok adatbázis-idegenkulcs nélkül, explicit Eloquent-kapcsolatokkal működnek; mennyiségi raktárkönyvelés és alacsony készletfigyelés továbbra M19.
- Következő konkrét feladat: M07.01, a jelenlegi partner-/termék-/termékköri speciális árak, kedvezménypreferencia, összeadás és kerekítés regressziós rögzítése.

### 2026-10-15: M08 stagingen ellenőrzött

- Elkészült a v6 referencia szerinti főoldali hero és navigációs kereső, a publikus ármentes katalógus, a mély kategória- és szűrőfolyamat, a slugos termékoldal, partneri ár/vásárlás, elérhetőségi státusz, dokumentumletöltés, gyorsrendelés és üres állapotok.
- A kereső URL-ben tartja az állapotot, az autocomplete natív JavaScriptet használ debounce-szal, stale-válasz eldobással és billentyűzetes navigációval. A publikus JSON és HTML nem ad partnerárat. A kiemelt termékek admin sorrendje `sort_order` mezővel és fel/le vezérléssel tartós.
- A `f9f3a39` commit tartalmazza a végső frontend runtime-javításokat is: a Bootstrap csak egyszer inicializálódik, a kosár-előnézet saját jQuery modult importál, így az autocomplete betöltése nem függ a globális változó sorrendjétől.
- Helyben ellenőrizve: Vite build, PHP lint és diff-check. A PHPUnit újrafuttatása PDO SQLite driver hiányában nem indult el; a #117 CI verify sikeres. A két új migráció visszagörgethető; nincs új környezeti változó vagy feature flag.
- Develop/staging: `f9f3a39`, [#117](https://github.com/sykolos/eb-webshop/actions/runs/38651616855) verify/deploy sikeres. Élő staging átvétel: menü, autocomplete, vendég/partner árjogosultság, főoldali hero, admin kiemelt sorrend és 390/768/1440 px nézetek rendben.
- Állapot: M08 STAGINGEN ELLENŐRZÖTT. Következő konkrét fejlesztés: M09.01 `CartService` és `carts/cart_items` adatmodell; meglévő ár-, mennyiség- és partnerjogosultsági szabályok megőrzésével.

### 2026-10-15: M09 lezárva, M10 kiadásra kész

- M09 staging kiadás: `5ce003f`, [#120](https://github.com/sykolos/eb-webshop/actions/runs/38667147729) verify/deploy sikeres. A stagingen az adatbázisos kosár, hozzáadás, mennyiségfrissítés, server oldali újraárazás, checkout és árazatlan szállítási opció betöltése ellenőrzött; a tesztkosár 2 csomaggal megmaradt, rendelés nem készült.
- M10 elkészült: partnerkérelem és admin jóváhagyás, `pending/approved/rejected/blocked` állapotok, belépési védelem, email-ellenőrzési nézet, fiókösszegző, rendelés-szűrés/PDF, aktuális áras újrarendelés, cím-default, kedvencek, email-váltási újraellenőrzés és tartós értesítési beállítások.
- Helyi ellenőrzés: teljes PHPUnit 77 teszt / 310 assertion, PHP lint, Blade cache, Vite build és diff-check sikeres. Ismert figyelmeztetés: a meglévő Sass/Bootstrap deprecation warningok M21 technikai adósságai, az M10 működését nem blokkolják.
- Állapot: M09 és M10 STAGINGEN ELLENŐRZÖTT; következő konkrét fejlesztés: M11.01 admin rendeléskezelés és kézi rendelésfelvitel.

### 2026-10-15: M11 kód kész, staging kiadás előtt

- Elkészült az M11 teljes backend és admin felülete: V6 rendeléslista kereséssel, cégnév-, dátum-, státusz- és nettóösszeg-szűréssel, lapozással és URL-állapottal; soros státuszváltás; látható soros kijelölés; tömeges váltás megerősítéssel, állapotátmenet- és részleges hibakezeléssel.
- Bevezetésre került az OrderStatusService, az order_status_histories napló, státuszfelelős és belső megjegyzés, audit és státuszértesítési queue job. A rendelésadatlap a mentett cím-/ár-pillanatképet, tételeket, PDF-et és státuszidővonalat mutatja.
- A törlés D09 szerint külön művelet: SoftDeletes + audit, az aktív listából eltűnik, de a történeti rendelés nem veszik el és az admin „Töröltek is” szűrővel látható.
- A kézi rendelés aktív partnerhez, mentett vagy egyedi szállítási címhez, fizetéshez és termékkeresőhöz kötött. A ManualOrderService tranzakcióban, ismétlésbiztos kulccsal és a partner aktuális PricingService árával ment; a termék-/ár-/mennyiségi snapshot a partnerfiókban is ugyanazt a rendelést mutatja.
- Új migráció: 2026_10_15_170000_add_order_management_fields; visszagörgethető, történeti rendelést nem töröl. Nincs új környezeti változó vagy feature flag.
- Helyi ellenőrzés: M11 célteszt 4 teszt / 23 assertion; teljes PHPUnit 81 teszt / 335 assertion; php artisan view:cache, route:cache, Vite build és git diff --check sikeres. A build meglévő Sass/Bootstrap deprecation figyelmeztetései továbbra M21 technikai adósságok.
- M11.K1-K3 teljesült, K4-K6 nyitott. Commit és develop push még nem történt; következő konkrét lépés: célzott diff áttekintés, commit, develop push, workflow megvárása, majd staging admin lista/státusz/új rendelés átvétel.

### 2026-10-15: M11 stagingen ellenőrzött

- A kiadott végső kód a `822094a`, a develop push és a meglévő verify/deploy workflow sikeres: [GitHub Actions run 38717403164](https://github.com/sykolos/eb-webshop/actions/runs/38717403164).
- A staging admin rendeléslistája, a kézi rendelésfelvitel nézete és a történeti rendelésadatlap betöltött. A legacy rendelés bruttó végösszege már nem `0 Ft`, hanem a meglévő nettó adatból helyesen visszaszámolt `23 686 Ft`.
- A listában a keresés/szűrés és a rendelésműveletek korábbi átvétele megmaradt; a kézi rendelés oldalon az aktív partner, címválasztás és termékkereső látható. A staging próba során új rendelés nem került mentésre és rendelés nem került törlésre.
- Reszponzív ellenőrzés az adatlap végső kiadásán: 390x844, 768x1024 és 1440x900 méreten betöltés rendben, vízszintes túlcsordulás nélkül. A hosszú partner email tablet-tördelése célzott CSS-javítást kapott.
- Helyben a Vite build és `git diff --check` sikeres. A teljes PHPUnit újrafuttatása ezen a gépen a hiányzó `pdo_sqlite` driver miatt nem indítható; a korábbi M11 baseline 4 célteszt / 23 assertion és teljes PHPUnit 81 teszt / 335 assertion volt, a staging CI verify sikeres.
- M11.K1-K6 teljesült. Következő konkrét fejlesztési feladat: M12.01 partnerlista és teljes partneradatlap; a D04 végleges munkakörlista továbbra is nyitott döntés.

### 2026-10-15: M12 kód kész, staging retry tárhelyfelszabadítás után

- Elkészült az M12.01-M12.07 teljes kódcsomagja: `UserManagementService`, V6 partnerlista és adatlap, kombinálható aktivitási/szerep/státusz/cég/dátum szűrők, cím- és számlázásmódosítás, admin megjegyzés, kontrollált admin/partner szerepváltás, jelszó-visszaállító link, partnerkérelmi V6 nézet, audit, utolsó-admin védelem és SoftDeletes-alapú partnerarchiválás.
- A staging kiadásra kész commitok: `7d0da75` (M12), `039aa00` (checkbox-normalizálás), `9d63951` (MySQL-kompatibilisebb, külön oszlopműveletes migráció). A develop push megtörtént.
- Helyi ellenőrzés a projekt SQLite extensionnel: teljes PHPUnit **84 teszt / 351 assertion** sikeres; M12 célteszt **3 / 16 assertion** sikeres; Blade cache, route cache, PHP lint, Vite build és diff-check sikeres. A Vite meglévő Sass/Bootstrap deprecation figyelmeztetései megmaradtak.
- A `38721498975` és `38721808476` workflow verify része sikeres, de a deploy mindkét alkalommal visszaállt. A GitHub annotation konkrét oka: `tar: ... Cannot write: Disk quota exceeded`; a stagingen ezért továbbra is az M11 kiadás látható, M12 admin átvétel nem történt.
- A felhasználó jelezte, hogy a staging tárhelyén felszabadította a szükséges helyet. A korábbi kvótablokkoló elhárítottnak tekintendő; a `develop` újrapusholása és a meglévő workflow újrafuttatása következik. Nem töröltem távoli backupot és nem változtattam a deployfolyamat biztonsági szabályain.
- M12.K1-K3 teljesült; M12.K4-K6 nyitva marad a retry és az admin lista/adatlap reszponzív átvételéig. Következő konkrét lépés: workflow eredményének megvárása, majd staging ellenőrzés.

### 2026-10-15: M12 staging retry indítva

- A staging tárhely felszabadítása után az M12 kiadási folyamatot újraindítom a meglévő `develop` -> GitHub Actions -> staging úton. M12.K4-K6 csak sikeres workflow és helyszíni admin/reszponzív ellenőrzés után zárható.

### 2026-10-15: M12 stagingen ellenőrzött

- Az `ea7a9cc` develop kiadás GitHub Actions [38726573353](https://github.com/sykolos/eb-webshop/actions/runs/38726573353) futásban sikeres verify/deploy eredménnyel került stagingre; a tárhelykvóta hiba nem ismétlődött.
- Bejelentkezett adminnal olvasási ellenőrzés történt: a `/adminpanel/users` partnerlista 224 találattal és a kombinálható szűrőkkel, a `/adminpanel/partner-requests` jóváhagyási nézet, valamint a `/adminpanel/users/310` teljes partneradatlap betöltött. Látható a számlázás, szállítási címek, admin műveletek, kapcsolódó rendelések és külön ár hivatkozás.
- Reszponzív ellenőrzés a partneradatlapon: 390x844, 768x1024 és 1440x900; mindhárom méreten a dokumentum szélessége a viewporton belül maradt. Módosítás, archiválás, jelszó-reset és rendelésmentés nem történt.
- A korábban futtatott helyi ellenőrzések változatlanul érvényesek: M12 célteszt 3 teszt / 16 assertion, teljes PHPUnit 84 teszt / 351 assertion, Blade/route cache, PHP lint, Vite build és `git diff --check` sikeres. Következő konkrét feladat: M13.01 közös táblázat-export és XLSX/CSV exportfolyamat.

### 2026-10-15: M13.01 exportkatalógus kód kész

- Elkészült az `AdminExportCatalogService` és a verziózott `config/admin_exports.php` nyilvántartás. A kilenc M13.01 adatkör: rendelések, rendeléssorok, termékek, kategóriák, szűrők, partnerek, mennyiségi egységek, külön árak és kiemelt termékek.
- Új admin-only `/adminpanel/exports` nézet készült a közös V6 navigációban. Az oldal csak a forrásokat és export-hatóköröket mutatja; fájlletöltést még nem indít, így M13.02-M13.11 hatóköre változatlanul nyitott.
- Ellenőrzés: M13 célteszt 2 teszt / 16 assertion; teljes PHPUnit **86 teszt / 367 assertion**; Blade cache, route cache, PHP lint és `git diff --check` sikeres. A Vite build D:-s ideiglenes útvonallal sikeres, a meglévő Sass/Bootstrap deprecation figyelmeztetések és a hero asset runtime figyelmeztetése megmaradt.
- M13.K1-K3 teljesült. Következő konkrét feladat: a release-jelölt commit `develop` integrációja, workflow/deploy, majd az exportkatalógus staging átvétele.

### 2026-10-15: M13.01 stagingen ellenőrzött részkiadás

- Az `e257ec0` commit sikeresen kikerült a `develop` ágra; a [38727685489](https://github.com/sykolos/eb-webshop/actions/runs/38727685489) workflow `verify: success` és `deploy: success` eredménnyel zárult.
- Admin staging átvétel: `/adminpanel/exports` betöltött, a közös navigációban az Exportok menüpont látható, mind a kilenc nyilvántartott adatkör és a forrása megjelent. A nézet szándékosan nem indít még fájlletöltést.
- Reszponzív ellenőrzés: 390x844, 768x1024 és 1440x900; a dokumentum szélessége minden méreten a viewporton belül maradt, 9 táblázatsor töltődött be.
- M13.01 stagingen ellenőrzött. M13.K4-K6 a teljes M13 mérföldkő végéig nyitva marad; következő konkrét feladat: M13.02 teljes szűrt eredményhalmazra épülő exportút.

### 2026-10-15: M13 teljes mérföldkő stagingen ellenőrzött

- Elkészült az M13.02-M13.11 teljes köre: minden nyilvántartott adatkörből teljes, szerveroldali szűrt XLSX/CSV export készül; a CSV pontosvesszős UTF-8 BOM-os és képletinjektálás ellen védett.
- Az export háttérfeladatként fut, állapotot és hibatörténetet tárol, a fájl privát és két óra után takarítható. A termékimport XLSX előnézetet, munkalap- és oszloppárosítást, 5 MB/5000 sor/50 oszlop korlátot, teljes validációt, hibajegyzéket, kihagyás/frissítés módot és tranzakciós, ismétlésvédett alkalmazást kapott.
- A termékimport megőrzi az üres opcionális mezők meglévő adatait, a mély kategóriafában teljes útvonalat vagy azonosítót kér, a készlet számot nem alakítja át önkényesen státusszá, és auditbejegyzést ír. A meglévő speciális ár- és termékkörös árazás nem változott.
- Ellenőrzés: M13 célteszt **4 teszt / 27 assertion**, teljes PHPUnit **88 teszt / 378 assertion**, Vite build, PHP lint, Blade/route cache és `git diff --check` sikeres. A helyi PHP futtatás a CI-vel egyező SQLite/ZIP/GD modulokkal történt.
- Kiadás: végleges deployolt SHA `162320699d0ec97d0e4f9fbf131d45a085cd6510`; GitHub Actions [38738575391](https://github.com/sykolos/eb-webshop/actions/runs/38738575391) verify és deploy sikeres. Az első deploy-kísérlet a staging legacy `users.id` méreteltérése miatt megállt; a migráció ezt idegenkulcs-kényszer nélküli, indexelt nullable owner mezővel kezeli.
- Staging átvétel adminnal: `/adminpanel/exports`; a 9 adatkör, exportindító, import-előnézet és import-sablon vezérlői látszanak. A 390x844, 768x1024 és 1440x900 ellenőrzéseken nem volt vízszintes overflow; a két táblában összesen 10 sor jelent meg. Valódi exportot/importot nem indítottam stagingen.
- M13.K1-K6 teljesült. Következő konkrét feladat: M14.01 hero/úszóképek adminja.

### 2026-10-15: M14 teljes mérföldkő stagingen ellenőrzött

- Elkészült az M14.01-M14.09 technikai köre: a `banners` migráció és `BannerService` három fix eszközváltozatot készít, az adminból külön menthető és publikálható a hero/úszó banner, időzítés és visszavonás kezelhető, a banner-metaadat bekerült az M13 exportkatalógusba.
- A publikus főoldal kezeli a reszponzív hero képet és az úszó bannert. A V6 kapcsolati, szállítás/fizetés, GYIK, katalógus, impresszum, ÁSZF, adatkezelés, elállás és 404 nézetek működnek. A kapcsolatűrlap validál, rate limitet használ és commit utáni `SendContactMessage` queue-jobot indít.
- A `docs/M14-banner-keszitesi-utmutato.pptx` 7 diás, renderelt és strukturálisan validált útmutató; a három kimeneti méret, szövegzóna, jó/rossz példa, feltöltési folyamat és ellenőrzőlista szerepel benne.
- Helyi ellenőrzés: M14 célteszt **4 teszt / 28 assertion**, teljes PHPUnit **92 teszt / 406 assertion**, Vite build, Composer validate, Blade/route/config cache, PHP lint és `git diff --check` sikeres. A Vite buildben a korábbi Sass/Bootstrap deprecation és hero asset runtime figyelmeztetés megmaradt.
- Kiadás: M14 kód `717e917`, workflow [38743282148](https://github.com/sykolos/eb-webshop/actions/runs/38743282148) verify/deploy sikeres. Staging átvétel közben 768 px-es V6 fejléc-overflow derült ki, ezt `a4e1364` javította; a [38743946029](https://github.com/sykolos/eb-webshop/actions/runs/38743946029) workflow verify/deploy része szintén sikeres.
- Staging átvétel: `/adminpanel/banners`, `/info/katalogus`, `/contact` és hiányzó útvonalon a 404 nézet betöltött. A 390x844, 768x1024 és 1440x900 méretek dokumentumszélességen belül maradtak; az adminban a három eszközfeltöltő, külön piszkozat/publikálás és az üres bannerlista látható. Valódi bannerfeltöltést, kapcsolatüzenet-küldést és destructive admin műveletet stagingen nem indítottam.
- M14.K1-K6 teljesült. Ismert tartalmi korlát: a jóváhagyott katalógus PDF és a végleges jogi szövegek üzleti átadást igényelnek; addig a katalógus üres állapota és a jogi oldalak verziózott jóváhagyási jelzése marad. Következő konkrét feladat: M15.01 nettó dashboard mutatók.

### 2026-10-15: M16 kód kész, staging kiadás előtt

- Elkészült az M16.01-M16.08 kódja: admin-only Emailközpont, marketing kampánysablon, sandboxolt HTML előnézet, hírlevél/marketing szegmensek, friss címzettszám-ellenőrzés, ütemezés és megszakítás.
- A levélküldés szolgáltatófüggetlen Laravel Mail + queue folyamat: egyedi delivery rekord, retry/backoff, limitált dispatch, idempotencia, jogosultság- és preferencia-újraellenőrzés, leiratkozási aláírt link és kizárólista.
- A tranzakciós rendelési levelek plain-text alternatívát kaptak. Az emailkampányok/kézbesítések admin-only exportálhatóak; a levéltörzs nem kerül exportba. A régi felhasználók `newsletter_subscribed=false` alapértéke változatlan.
- Új migráció: `2026_10_15_210000_create_email_campaign_tables`; visszagörgethető, legacy user-kulcsokra nem kényszerít idegenkulcsot. Új környezeti változó nem kötelező; a meglévő `STAGING_MAIL_TO` sink és D14 deployfolyamat változatlan.
- Külső szolgáltató, domainhitelesítés, bounce/complaint webhook és provider-fiók D12 szerint nincs kitalálva vagy aktiválva; a lokális státuszok pontosan queue/sending/sent/failed/skipped állapotot jelentenek.
- Ellenőrzés: M16 célteszt **4 teszt / 17 assertion**, teljes PHPUnit **98 teszt / 439 assertion**, Vite build, Composer validate, PHP lint, Blade/route/config cache és `git diff --check` sikeres. A meglévő Sass/Bootstrap deprecation és hero asset runtime figyelmeztetések megmaradtak.
- M16.K1-K3 teljesült. Következő konkrét lépés: egy funkciós commit, `develop` push, meglévő workflow megvárása, majd staging `/adminpanel/emails` átvétel 390/768/1440 px-en és küldés nélkül végzett sink/preview ellenőrzés.

### 2026-10-15: M16 stagingen ellenőrzött

- A `c93c74f` commit a `develop` ágra került; a [38749992983](https://github.com/sykolos/eb-webshop/actions/runs/38749992983) verify és deploy sikeres.
- Adminnal a `/adminpanel/emails` oldal betöltött, az Emailközpont menüpont látszik, a hírlevél/marketing címzettszámok, kampányszerkesztő, ütemezés, szegmens, tesztküldés, kampány- és kézbesítési napló vezérlői megjelentek.
- A HTML előnézet stagingen ténylegesen megnyitható. Reszponzív ellenőrzés 390x844, 768x1024 és 1440x900 méreten történt, a dokumentum szélessége minden méreten a viewporton belül maradt.
- Kampány mentést, tesztküldést, tömeges küldést és leiratkozást stagingen nem indítottam, mert ezek külső levél/üzleti állapotváltozást okozhatnak; a sink-védelem a kódban és a céltesztekben ellenőrzött. A külső provider és domainhitelesítés D12 alapján nyitott.
- M16.K1-K6 teljesült. Következő konkrét feladat: M17.01 kosárelhagyási szabályok és küldési korlátok meghatározása.

### 2026-10-15: M17 stagingen ellenőrzött

- Elkészült az M17.01-M17.06 technikai köre: kikapcsolt alapértelmezett automatizmus-szabályok, 24 órás alap kosárelhagyási küszöb, címzett- és preferenciaellenőrzés, küldési ablak/időzóna, maximum és cooldown mezők, idempotens futási kulcs és queue-job.
- A kosár-emlékeztető küldés előtt újraellenőrzi az aktivitás időpontját, a kosár tételét és konverzióját, a fiókállapotot, email-verifikációt, marketingpreferenciát és kizárólistát. Partnerjóváhagyás és rendelésállapot eseményhorgok elkészültek; a ticket szabály csak M18-ig előkészített és kikapcsolt.
- Elkészült az admin Automatizmusok oldal szabálykapcsolóval, következő futással, futási naplóval és kézi kosár-dispatch gombbal, valamint az automatizmus-szabályok és futások admin-only exportja. Az alapértelmezett szabályok kikapcsolva maradtak.
- Új migráció: `2026_10_15_220000_create_automation_tables`; visszagörgethető, a `automation_runs` idempotency kulcsa egyedi, a meglévő kosár-, rendelés-, ár- és emailadatokhoz nem nyúl. Új kötelező környezeti változó nincs; a meglévő scheduler, queue és staging mail-sink folyamat maradt.
- Ellenőrzés: M17 célteszt **5 teszt / 23 assertion**, teljes PHPUnit **103 teszt / 462 assertion**, Vite build, Composer validate, Blade/route/config cache, PHP lint és `git diff --check` sikeres. A korábbi Sass/Bootstrap deprecation és hero asset runtime figyelmeztetések megmaradtak.
- Kiadás: `eac8964` commit, [38775259789](https://github.com/sykolos/eb-webshop/actions/runs/38775259789) verify/deploy sikeres. A korrekció a partnerjóváhagyási értesítést rendszerüzenetként kezeli, ezért nem kötődik a marketingfeliratkozáshoz; a rendelésállapot a `order_updates`, a kosárelhagyás a `marketing_notifications` preferenciát követi. Stagingen az Automatizmusok oldal adminnal 390/768/1440 px-en betöltött, túlcsordulás nélkül; a négy szabály szünetelt, a futási napló üres, az exportkatalógusban mindkét új adatkör megjelent. Működési emailt és szabálybekapcsolást nem indítottam.
- M17.K1-K6 teljesült. Következő konkrét feladat: M18.01 supportlista és ticket-adatlap mezői, kapcsolódó rendelés és szűrés. D12 provider-döntés és M18 ticket domain továbbra is nyitott.

### 2026-10-15: M18 stagingen ellenőrzött

- Elkészült az M18.01-M18.05 és M18.08 technikai köre: partneri ticketlétrehozás saját rendeléskapcsolattal, admin supportlista szerveroldali kereséssel és szűréssel, felelős- és állapotkezelés átmenetnaplóval, partner/admin üzenetek, belső fejlesztői megjegyzések és privát, MIME-ellenőrzött csatolmányok.
- A közös ticket/üzenetmodell partnerenként izolált; az admin belső tartalom nem kerül partneri lekérdezésbe. Az olvasottság `ticket_reads` táblában és listajelzéssel működik, a publikus adminválasz M17 `ticket_update` queue-horogot használja, amely alapértelmezetten kikapcsolt.
- Elkészült a ticket és ticket-üzenet export metaadatköre, valamint a konfigurálható 24 hónapos retention: a napi `tickets:cleanup` csak lezárt, lejárt ticketet töröl, és a privát csatolmányfájlokat is eltávolítja. Az SLA-válasz- és megoldási határidő a prioritásból számolódik; a végleges üzleti vállalás M21-ben marad.
- D13 lezárva: külső vagy külön chatintegráció nem készül; a ticketrendszer marad az ügyfélszolgálati kommunikáció felülete.
- Helyi ellenőrzés: M18 célteszt **5 teszt / 27 assertion**, teljes PHPUnit a CI-ben **108 teszt / 484 assertion**, 1 korábbi importteszt skip; Vite build, PHP lint, Blade cache, route-list és `git diff --check` sikeres. A meglévő Sass/Bootstrap deprecation és hero asset runtime figyelmeztetések megmaradtak.
- A staging első deployja a legacy `users.id` idegenkulcs-méreteltérése miatt visszaállt; a második a félbemaradt `tickets` tábla miatt állt meg. A végső `454da2a` idempotens, user-FK nélküli indexelt migrációval a [38781343141](https://github.com/sykolos/eb-webshop/actions/runs/38781343141) workflow verify/deploy sikeres lett.
- Admin staging átvétel: `/adminpanel/tickets` betöltött, a ticketlista, állapot/prioritás/típus/felelős szűrők és üres állapot megjelent; `/adminpanel/exports` alatt a Ticketek és Ticket üzenetek adatkör látható. Reszponzív QA 390/768/1440 px-en túlcsordulás nélkül ment. Stagingen nem hoztam létre valódi ticketet, üzenetet vagy fájlt.
- M18.K1-K6 teljesült. Következő konkrét fejlesztés: M19.01 elérhetőségi státuszkódok és megnevezések; a M21 végleges SLA-vállalása marad későbbi döntési pont.

### 2026-10-15: M19 kód kész, staging kiadásra előkészítve

- Elkészült az M19.01-M19.07 technikai köre: a termék elérhetőségi státuszai közös modellforrásból jelennek meg az adminban, export/importban, dashboardban és a webshop kártya/adatlap nézeteiben. Az `out_of_stock` meglévő rendelhetőségi viselkedése megmaradt; a többi státusz nem tilt új rendelést.
- Az admin státuszváltás történetet és auditbejegyzést ír; az XLSX-import meglévő terméknél is ugyanazon szolgáltatáson keresztül naplóz. A dashboard státusz-cache státuszváltáskor verziót vált.
- Helyi ellenőrzés: M19 célteszt **4 teszt / 14 assertion**, 1 `ZipArchive`-hiány miatti skip; teljes PHPUnit **112 teszt / 498 assertion**, 2 skip; Vite build, PHP lint, Blade cache, route-list és `git diff --check` sikeres. A skip a helyi PHP-bővítmény hiánya, CI PHP-ban a ZipArchive ág fut.
- Ismert, nem blokkoló figyelmeztetés: a meglévő Sass/Bootstrap deprecation és hero asset runtime figyelmeztetések megmaradtak; Bootstrap/jQuery kiváltása külön frontend mérföldkő.
- M19.K1-K3 teljesült. Következő konkrét lépés: célzott commit, `develop` push, a meglévő verify/deploy workflow megfigyelése, majd admin staging QA 390/768/1440 px-en.

### 2026-10-15: M19 stagingen ellenőrzött

- A `ba9c203` commit a `develop` ágra került, a [38813030720](https://github.com/sykolos/eb-webshop/actions/runs/38813030720) verify/deploy sikeres lett.
- Staging admin QA: a terméklistán mind a négy elérhetőségi állapot választható, a `Beszerzés alatt` szűrő helyesen üres eredményt ad; a termékszerkesztőn látszik a státuszmező és a státusztörténet panel; a dashboard mutatja az elérhetőségi csoportot; a publikus termékadatlapon megjelenik a státuszjelzés.
- Reszponzív QA 390/768/1440 px-en a termékadmin oldalon: a dokumentum- és body-szélesség a viewporton belül maradt. Stagingen nem indítottam valódi státuszmentést, importot, exportot vagy adatot módosító műveletet.
- M19.K1-K6 teljesült. Következő konkrét fejlesztés: M20.01, a meglévő partneri/speciális árazás változatlan megtartásával; Bootstrap/jQuery kiváltása külön frontend mérföldkőként marad.

### 2026-10-16: M20 kód kész, staging kiadásra előkészítve

- Elkészült az M20.01-M20.06 technikai köre: időzített ajánlati törzs, termék- és kategóriaág-kapcsolat, admin szerkesztés/publikálás/visszavonás, M14 bannerkapcsolat, publikus `/info/akciok` oldal és kezdőoldali aktuális ajánlatlista.
- A vendég ajánlati oldala ármentes; a bejelentkezett partner saját árát a változatlan `PricingService` számolja. Az ajánlat nem módosít kedvezményprioritást, speciális árat, kosár- vagy rendelési logikát, és nem jelenít meg kitalált akciós százalékot vagy áthúzott árat.
- Az ajánlati törzs exportálható, a mentés/publikálás auditált, a publikus cache verziózottan érvényteleníthető, a kezdő- és lejárati időszak szűrése szerveroldali. A két migráció idempotens, a meglévő ár-, rendelés- és banneradatokat megőrzi.
- Helyi ellenőrzés: M20 célteszt **3 teszt / 24 assertion**, kapcsolódó M07/M08/M13/M14 regressziós kör **17 teszt / 81 assertion**, 1 korábbi skip; teljes PHPUnit **115 teszt / 520 assertion**, 2 skip; Vite build, PHP lint, Blade cache, route-list és `git diff --check` sikeres. A meglévő Sass/Bootstrap deprecation és hero asset runtime figyelmeztetések megmaradtak.
- M20.K1-K3 teljesült. Következő konkrét lépés: célzott commit, `develop` push, a meglévő verify/deploy workflow megfigyelése, majd admin ajánlatkezelő és publikus ajánlati oldal QA 390/768/1440 px-en.

### Következő munkamenet sablonja

- Dátum / aktív mérföldkő:
- Elkészült feladatazonosítók:
- Folyamatban / blokkolt / halasztott azonosítók és ok:
- Változott fájlok és döntések:
- Futott ellenőrzés és tényleges eredmény:
- Commit / develop SHA / staging futás:
- Migráció / rollback / ismert hiba:
- Következő konkrét feladat:
