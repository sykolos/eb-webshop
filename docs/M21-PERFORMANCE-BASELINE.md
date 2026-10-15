# M21 teljesítmény baseline

## Célértékek

| Felület | p95 cél | Megjegyzés |
| --- | ---: | --- |
| Publikus HTML | 800 ms | vendég, ármentes válasz |
| Admin lista | 1200 ms | lapozott szerveroldali lista |
| Autocomplete | 500 ms | maximum 5 találat |
| Mobil/tablet/desktop | 0 overflow | 390/768/1440 px |

## Mérés

Az értékeket release SHA-val és staging környezettel együtt kell rögzíteni.
A browser QA dokumentálja a body/document szélességet; a HTTP mérés legalább
20 azonos kérésből számol p95-öt, cache-melegítéssel és cache-hideg első
kéréssel külön.

```powershell
php artisan data:integrity-report --json
php artisan system:capacity-report --json
Invoke-WebRequest https://ebtest.dekor26.hu/health/live
```

A release előtti baseline a kiadási checklistben szerepel. Tuning csak query,
index, cache vagy képméret bizonyítékkal kerülhet be; partnerár és rendelési
összeg változása teljes regressziót igényel.
