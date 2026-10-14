# M05 design handoff

Ez a dokumentum a ZIP referencia és a stagingen elkészült Laravel/Blade preview közös, Figma-ready képernyő- és komponensjegyzéke. Nem tartalmaz ügyféladatot, demo-belépést vagy üzleti árlogikát.

## Vizuális alapok

- Színek: `#e60000` kiemelés, `#202529` grafit navigáció, `#f5f6f7` munkafelület, fehér tartalomfelület.
- Betű: Poppins; címsorok erősek és rövidek, törzsszöveg jól olvasható, 0 letter-spacing.
- Tartalmi keret: legfeljebb 1240 px; mobilon 16 px oldalsó belső tér.
- Kártya: fehér felület, 1 px halvány keret, legfeljebb 8 px sarokrádiusz, finom árnyék.
- Fókusz: látható piros fókuszgyűrű; státusz nem csak színnel, hanem szöveggel is értelmezhető.
- Kép: ZIP-ből átvett ipari hero `public/img/electrobusiness-hero.png`; a nyitott bal oldalon hely marad a címsornak és a CTA-knak.

## Képernyővázak

### Webshop

- Fejléc: logó, fő navigáció, kosár darabszám, fiók; keskeny nézetben egy gombbal nyitható menü.
- Hero: sötét ipari háttér, rövid értékajánlat, piros elsődleges és fehér másodlagos CTA.
- Kategóriák: négy gyors belépési csempe, későbbi mély kategóriafával bővíthető.
- Terméklista: sorozatszám, név, elérhetőségi badge, partnerárat nem mutató publikus állapot, bejelentkezési CTA.
- Állapotok: üres, hiba és töltés külön komponenshelyet kap.

### Partnerfiók

- Kétoszlopos asztali váz: oldalsó fióknavigáció és fő munkaterület.
- Összefoglaló mutatók, legutóbbi rendelések táblája, státuszbadge-ek.
- Mobilon a navigáció és a mutatók egymás alá törnek; a rendelési táblázat nem tartalmazhat rejtett vízszintes tartalmat.

### Admin

- Grafit oldalsáv, világos munkaterület, jól szkennelhető mutatósor.
- Adattábla: fejléc, sorállapot, prioritás és műveleti CTA.
- Hibaállapot: hibás mező, szöveges magyarázat és fókuszállapot.
- Későbbi admin modulok ugyanezt a shellt és a táblázat/űrlap variánsokat használják.

## Komponens-variánsok

`v6/header`, `v6/footer`, `v6/product-card`, `v6/button`, `v6/badge`, `v6/field`, `v6/table`, `v6/quantity`, `v6/dialog`, `v6/notification`, `v6/empty`, `v6/error`, `v6/loading`.

Minden komponenshez legalább alap, hover/focus, disabled vagy hibás, valamint mobil elrendezési állapot tartozik. A jelenlegi M05 preview a shell, hero, kártya, badge, űrlap, tábla és üres/hiba/töltés állapotok implementált mintája; a mennyiség, dialógus és értesítés komponenshelye a következő üzleti modulokban kapcsolódik valódi adathoz.

## Staging preview

- Komponensgaléria: `/adminpanel/design-system`
- Webshop: `/adminpanel/design-system/shop`
- Partnerfiók: `/adminpanel/design-system/account`
- Admin: `/adminpanel/design-system/admin`

Mindegyik preview admin middleware mögött van. A preview-mintaadatok nem kerülnek a publikus vásárlói felületre, és a valódi partnerár továbbra is a meglévő backend jogosultság- és árlogikából érkezik.

