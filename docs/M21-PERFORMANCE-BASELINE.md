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

## 2026-10-16 staging mérés

20 egymást követő `Invoke-WebRequest` mintából, cache-melegítés mellett:

| Útvonal | p50 | p95 | maximum |
| --- | ---: | ---: | ---: |
| `/` | 347 ms | 394 ms | 464 ms |
| `/shop` | 191 ms | 227 ms | 228 ms |
| `/health/live` | 171 ms | 212 ms | 215 ms |

A publikus célok teljesültek. Az admin lista p95-e külön, hitelesített
terhelésmérést igényel; a jelenlegi staging QA során a dashboard, banner,
export és árazás oldalak betöltése és reszponzív viselkedése lett ellenőrizve.
