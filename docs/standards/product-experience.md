# Corexis Product Experience Standard

Ovaj dokument sadrži obavezna pravila za početno AI postavljanje i transakcijske e-mailove.

## AI početno postavljanje
- Početno AI postavljanje treba biti fokusirani tok na kratkoj ruti, npr. `/app/ai`, ne duboko ugniježđena ruta poput `/app/website/ai-content`.
- Dok početno postavljanje nije završeno, middleware mora štititi sve admin stranice osim eksplicitno dozvoljenih onboarding/processing/status ruta. Za development mora postojati konfiguracijski bypass kako se lokalno ne bi moralo trošiti AI pozive.
- Allowlist početnog postavljanja mora uključiti samo potrebne setup, processing, status, completion, logout i sigurne support rute.
- Fokusirani onboarding ekran ne smije imati standardnu admin navigaciju ako ona ne pomaže korisniku završiti tok.
- Fokusirani onboarding treba koristiti ponovljiv admin-ui layout za setup tok, a ne obični admin page shell.
- Ne koristiti riječ "anketa" kao glavni CTA ili opis ako zvuči administrativno; preferirati "kratki odabir", "prilagodba" i "Kreni s prilagodbom".
- Prvi korak mora u nekoliko rečenica objasniti trajanje i ishod. Trajanje prikazati kao badge s ikonom sata, npr. `1-2 minute`, a ne ponavljati ga u više odlomaka.
- Copy ne smije ponavljati istu riječ u susjednim rečenicama, posebno "aplikacija". Tekst treba biti kratak, opušten i orijentiran na ishod: korisnik treba odmah vidjeti da će se kreirati stranice, sekcije i početni sadržaj koji kasnije može urediti.
- Korake razlomiti po jednoj temi po ekranu, npr. područja rada, vrijednosti, sudionici, aktivnosti, naglasci, ton teksta i pregled. Ne gurati sve u jedan dugačak obrazac.
- Kartica/tok treba imati stabilnu visinu, sadržaj poravnat gore i footer/gumbe pri dnu kako layout ne bi "šetao" između koraka. Stranica ne smije imati nepotreban body scroll.
- Gumb za sljedeći korak mora biti disabled dok obavezni unos nije ispunjen, ali backend validacija i dalje mora biti izvor istine. Kada je korak spreman, vizualno ga promijeniti iz neutralnog/default stanja u primary.
- Polja koja utječu na stanje gumba moraju koristiti Livewire live binding (`wire:model.live`, odnosno debounce za tekst) kako se readiness odmah osvježava.
- AI generator ne smije izmišljati cijelu strukturu aplikacije. Aplikacija deterministički priprema stranice, sekcije i osnovne zapise, a AI prilagođava početni tekst/sadržaj unutar tog okvira.

## Transakcijski e-mailovi
- Aplikacijski e-mailovi moraju vizualno pripadati istoj aplikaciji: naziv/brend, primarna boja i font definiraju se centralno u configu, a ne zasebno u svakom mail templateu.
- E-mail template mora biti table-based, imati ključne stilove inline i sigurne fallback fontove jer Outlook i dio webmail klijenata ne podržavaju puni moderni CSS.
- Web font smije biti prvi izbor, ali uz obavezni sistemski fallback. Ključni CTA mora ostati čitljiv i klikabilan i kada web font, media query ili border radius nisu podržani.
- Pozivnice i drugi sigurnosno važni e-mailovi trebaju uz glavni CTA prikazati rok valjanosti, kontekst/ulogu i tekstualnu fallback poveznicu.
- E-mail vizualno pripada aplikaciji, ali ostaje jednostavniji od web UI-ja i čitljiv bez web fonta, media queryja, sjene ili zaobljenih rubova.
