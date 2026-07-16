# Corexis Security Standard

Ovaj dokument sadrži obavezna pravila za tajne, autentikaciju, pozivnice te Livewire i CSP sigurnost.

## Tajne i API ključevi
- API ključeve, S3 pristupne podatke i druge tajne ne pisati u chat, dokumentaciju, testove, seedere, commitove ili vidljive logove.
- Tajne unositi samo u lokalni `.env` ili sigurni secret manager. `.env.example` smije sadržavati samo prazne ili dokumentacijske placeholder vrijednosti.
- Kada se testira integracija s vanjskom uslugom, u odgovoru ne ispisivati vrijednost ključa; dovoljno je potvrditi je li varijabla postavljena i je li poziv uspio.

## Auth i pozivnice
- Login, two-factor, passkey, password reset i javni invitation tokovi moraju imati eksplicitne rate limitere. Ne oslanjati se samo na UI ili validaciju forme.
- Fortify login limiter treba ostati vezan na email/username + IP, two-factor na login session, a passkey na credential/session + IP.
- Ako projekt ne koristi passkey prijavu, isključiti je centralno u auth/Fortify konfiguraciji i ne prikazivati opciju "prijava pristupnim ključem" na login ekranu.
- Javni linkovi pozivnica moraju koristiti signed URL, kratko trajanje, token spremljen samo kao hash, route-level IP throttle i dodatni per-token limiter za preview/submit.
- Prihvaćanje pozivnice mora ponovno provjeriti status pozivnice unutar `lockForUpdate()` transakcije prije dodjele članstva ili uloge. Accepted, revoked i expired pozivnice ne smiju se moći ponovno potrošiti.
- Ako pozivnica dodjeljuje ulogu, uloga se mora ponovno provjeriti neposredno prije prihvaćanja pozivnice, ne samo u trenutku slanja.

## Livewire sigurnost
- Pri svakoj izmjeni ili dodavanju Livewire komponente obavezno napraviti sigurnosni scan prije završetka rada.
- Livewire public properties, form state i action parametri uvijek se tretiraju kao nepouzdan browser input. Livewire checksum nije zamjena za autorizaciju, tenant scope i validaciju.
- Server-owned state mora biti `#[Locked]`: route/model identiteti (`uuid`, model property, `page`, `section`, `post`, `gallery`, `product`), mount-only konfiguracija (`modelClass`, `modelKey`, `collection`, `context`, `definitionKey`, redirect/URL/label opcije), modal pending identifikatori i read-only display state koji browser ne smije mijenjati.
- Ne zaključavati stvarni korisnički unos: tekstualna polja, select/toggle vrijednosti koje korisnik mijenja, upload polja, search/filter/sort state s allowlist validacijom i druge namjerno editabilne vrijednosti.
- Action metode koje primaju UUID, type, sort field, filter, tab, status ili action name moraju ponovno validirati allowlist i/ili dohvatiti model kroz tenant-scoped query prije bilo kakvog pisanja.
- Destruktivne ili osjetljive modalne akcije trebaju koristiti obrazac `confirm...($uuid)` koji server-side postavi `#[Locked]` pending UUID, a stvarna akcija (`delete`, `archive`, `restore`, `publish`, `detach`) ne smije nepotrebno primati direktni UUID iz browsera.
- Svaki write mora proći kroz Action ili ekvivalentnu backend metodu koja ponovno autorizira radnju. Sakrivanje gumba u Bladeu je samo UX, nije sigurnost.
- Privilegirana javna Livewire akcija mora odbiti zahtjev ako actor ili tenant nije razriješen. `null` tenant nikada ne smije značiti "bez tenant ograničenja" za browser-triggered write, čak ni kada je gumb skriven ili zakomentiran.
- Ako Livewire komponenta živi iza custom route middlewarea bez argumenata, taj middleware mora biti registriran kroz `Livewire::addPersistentMiddleware(...)` u service provideru kako bi se ponovno primijenio na naknadne Livewire requestove.
- Custom middleware s argumentima, npr. `permission:*` ili `role:*`, ne tretirati kao dovoljan za naknadne Livewire requestove jer Livewire persistent middleware ne podržava argumente; osjetljive write akcije moraju ponoviti permission/role/policy provjeru u komponenti, Actionu ili policyju.
- Livewire update route mora imati centralni rate limiter u `config/livewire.php` (`update_middleware`, `rate_limits.update_requests_per_minute`). Ne dodavati odvojene hardkodirane throttle vrijednosti po komponentama bez stvarnog razloga.
- Livewire temporary upload endpoint mora imati centralno podešena pravila u `config/livewire.php` (`temporary_file_upload.rules`) i throttling middleware (`temporary_file_upload.middleware`). Limit veličine slika mora ostati vezan na `config('corexis.image_uploads.default.max_file_size_kb')`, ne na zasebne hardkodirane vrijednosti.
- Kod Livewire promjena pokrenuti fokusirane testove koji pokrivaju promijenjenu komponentu i barem jedan test destruktivne/osjetljive akcije ako postoji.

### Dodatna kontrolna lista komponente

- Direktni pristup stranici autorizirati u `mount()`, route middlewareu, policyju ili kroz `corexis_authorize()`.
- Ne vjerovati hidden inputima ni public properties za tenant, actor, ulogu, plaćeni pristup ili vlasništvo.
- Tajne, tokene, payment-provider stanje i potpisane payloadove ne držati u javnom Livewire stateu.
- Uploade uvijek validirati server-side prije predaje Actionu, neovisno o client-side pripremi datoteke.
- Stateful Flux modal otvoriti tek nakon što server resetira stari state, tenant-scoped razriješi i autorizira entitet te napuni formu ili pending UUID.
- Prije završetka provjeriti izravni pristup stranici, kasniji Livewire update request i barem jedan destruktivni ili privilegirani modalni tok.

## Livewire CSP
- Livewire 4 CSP-safe mode (`config('livewire.csp_safe')`) mora biti uzet u obzir pri pisanju novih Livewire/Alpine interakcija, ali ga ne uključivati na produkciji bez posebnog browser test passa.
- Ne uvoditi kompleksne inline Alpine/Livewire izraze koji otežavaju strogi CSP: arrow funkcije, template literale, spread sintaksu, dinamički method/property access (`this[methodName]`, `object[key]`) ili veće JS blokove u atributima.
- Kompleksnu Alpine logiku prebaciti u imenovane metode/gettere unutar `x-data` objekta ili u registrirani `Alpine.data()` modul, umjesto u inline `x-on`, `x-show`, `x-bind` i `x-text` izraze.
- Ako se uključuje strogi CSP bez `'unsafe-eval'`, prvo uključiti `config('livewire.csp_safe')` na lokalnom/staging branchu, otvoriti ključne admin i javne tokove u browseru i provjeriti konzolu za CSP/Alpine/Livewire greške.
- Ako stranica koristi ručne `<script>` blokove, CSP produkcijski rollout mora predvidjeti nonce ili preseljenje skripte u bundlani asset. Ne dodavati nove inline `<script>` blokove bez jasnog razloga.
- CSP browser pass mora obuhvatiti file upload, modale, dropdowne, paginaciju, lazy sekcije i custom Alpine widgete.

## Sigurnosna HTTP zaglavlja
- Corexis web middleware mora zadano postaviti `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin` i restriktivni `Permissions-Policy` za kameru, mikrofon i geolokaciju.
- Projekt smije proširiti ili isključiti ta zaglavlja samo kroz `corexis.security_headers` konfiguraciju i uz dokumentiran razlog; ne duplicirati ih po kontrolerima.
- Strogi `Content-Security-Policy` nije dio osnovnog middlewarea jer zahtijeva zaseban CSP-safe browser test prolaz opisan iznad.
