# Corexis Framework Defaults Standard

Ovaj dokument definira zajedničke Laravel zadane postavke koje Corexis automatski primjenjuje na sve host aplikacije.

## Vlasništvo

- Zajedničke Eloquent, database, date, password, validation i testne HTTP zaštite pripadaju u `CorexisServiceProvider`, ne u `AppServiceProvider` pojedinog projekta.
- Host `AppServiceProvider` smije sadržavati samo ponašanje specifično za aplikaciju, njezine middlewaree, komponente i integracije.
- Sve zadane postavke moraju raditi bez dodatnih `.env` unosa. Host ih mijenja samo kada postoji stvaran projektni razlog, kroz `config/corexis.php`.

## Zadane postavke

Corexis automatski:

- koristi `CarbonImmutable` kroz Laravel `Date` facade;
- uključuje Eloquent strict mode u svim okruženjima osim produkcije;
- zabranjuje destruktivne database Artisan naredbe u produkciji;
- upozorava kada kumulativno vrijeme SQL upita prijeđe 500 ms, bez zapisivanja SQL-a i bindinga u log;
- u produkciji zahtijeva lozinku od najmanje osam znakova, velika i mala slova, broj, simbol i uncompromised provjeru;
- za required i accepted validacijska pravila koristi zajedničku kratku prevedenu poruku;
- u testovima zabranjuje stvarne Laravel HTTP client pozive koji nisu eksplicitno fakeani.

`Model::automaticallyEagerLoadRelationships()` nije zajednički default. On može prikriti nedostajući `with()` i otežati razumijevanje profila upita; strict mode mora prijaviti takav problem.

## Konfiguracija

- `strict_models = null` znači uključeno izvan produkcije.
- `prohibit_destructive_commands = null` znači uključeno samo u produkciji.
- `cumulative_query_time_threshold_ms = 0` isključuje SQL upozorenje.
- `prevent_stray_http_requests_in_tests = false` dopušten je samo kada test eksplicitno mora koristiti stvarnu mrežu.
- Password pravila mogu se mijenjati samo centralno u `corexis.framework.passwords`; ne definirati drugi `Password::defaults()` u host aplikaciji.
- `required_validation_message` može biti translation key ili `null` za isključivanje Corexis replacera.

Kada test namjerno treba stvarni HTTP poziv, koristiti usko ograničen `Http::allowStrayRequests([...])` umjesto globalnog isključivanja zaštite.
