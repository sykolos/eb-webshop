# M21 biztonsági ellenőrzőlista

- [x] Laravel és Composer függőségek auditálva, lockfájl használva.
- [x] Frontend csomagok Bootstrap/jQuery/Select2 nélkül épülnek.
- [x] CSRF, session HttpOnly/SameSite/Secure és rate limit ellenőrzés.
- [x] CSP, `nosniff`, frame, referrer, Permissions-Policy fejlécek.
- [x] Privát upload és signed/download útvonalak ellenőrzése.
- [x] Admin és partner jogosultság, rendeléstulajdonos- és ticketizoláció.
- [x] Email sink/preferencia/leiratkozás és audit-szűrés.
- [x] Partnerár nem kerül vendég HTML/JSON/cache válaszba.
- [ ] Külső tracking provider aktiválása: külön adatvédelmi és üzleti döntés.
- [ ] Éles domain/Cookie/CSP véglegesítés: éles üzemeltetési ablakban.
