# Corexis Admin UI Standard

Ovaj dokument sadrži obavezne standarde za administracijsko sučelje, forme, validaciju, paginaciju i prikaze.

## Admin UI i ponovljivi obrasci
- Svaka admin lista/tablica koja prikazuje redove mora imati vidljiv `admin-list-header` neposredno iznad redova, s istim grid kolonama kao redovi. Prazna stanja ne trebaju header, ali stranice, sekcije, objave, radovi, kategorije, oznake, korisnici i buduće liste ne smiju ostati bez naziva kolona.
- Ponovljivi admin layout, responsive pravila, Flux modal širine, mobilni gutteri, editor overflow fix, cursor ponašanje i stilovi za required oznake pripadaju u `packages/ivanbaric/admin-ui`, ne u pojedinačni projekt.
- Host aplikacija treba samo importati `admin-ui.css`; ne duplicirati iste modal/mobile fixeve u `resources/css/app.css` osim ako postoji stvarno projektno odstupanje.
- Obavezna admin polja ne označavati HTML atributom `required` ako želimo Livewire/backend validaciju prije browser validacije. Na Flux kontrolu staviti `data-required`; `admin-ui.css` automatski dodaje crvenu zvjezdicu na Flux label. `x-admin-ui::required-label` koristiti samo za custom markup koji ne koristi Flux label.
- Svi Flux select/dropdown elementi u administraciji trebaju koristiti `variant="listbox"` osim ako postoji dokumentiran razlog za drukčiji prikaz.
- Primarni Livewire submit gumbi moraju koristiti `x-admin-ui::submit-button`: scoped `wire:target`, disabled tijekom requesta, Flux loader i loading label `Spremanje...`. Kod većih admin formi primarni save gumb mora biti unutar iste `<form wire:submit="...">` kao polja koja sprema, čak i kada je vizualno u page headeru. Veće admin forme trebaju imati `wire:loading.class="admin-panel-content-loading"` i `x-admin-ui::loading-overlay` s tekstom `Spremanje...`. Ne ostavljati ručne `<flux:button type="submit" variant="primary">` gumbe koji se mogu kliknuti više puta tijekom spremanja.
- Flux modali koji ovise o server-side entitetu, form stateu ili pending UUID-u ne smiju se otvarati kroz `<flux:modal.trigger>` uz istodobni `wire:click`. Livewire metoda mora prvo resetirati stari state, tenant-scoped dohvatiti/validirati entitet, napuniti formu ili pending UUID, pa tek onda otvoriti modal s `Flux::modal(...)->show()` ili `modal-show`.
- Svaki takav modal mora resetirati state na zatvaranje (`x-on:close="$wire.cancel...()"`, `@cancel` ili ekvivalent). X, ESC, klik izvan modala i Odustani ne smiju ostaviti stare podatke koji se vide pri sljedećem otvaranju.
- Admin stranice moraju imati konzistentan mobilni gutter kao stranica "Stranice"; forme, editori, tabovi i kartice ne smiju dodirivati rub ekrana niti izlaziti iz viewporta.
- Admin desktop shell ne smije koristiti globalni `scrollbar-gutter` na `html`, `body` ili `admin-root`; to stvara lažni scrollbar i vraća stare layout probleme.
- Za Flux admin layout standard je: shell zaključan na `100dvh`, grid main row kao `minmax(0, 1fr)`, scroll samo na `[data-flux-main]`, a `scrollbar-gutter: stable` samo na `[data-flux-main]`. Tako se ne miče sadržaj kod Flux dropdowna i ne pomiču se glavni naslovi 2-3px između kratkih i dugih stranica.
- Standardni gutter admin stranice smije imati samo `.admin-page`; `[data-flux-main]` je isključivo scroll owner. Flux main zadano dodaje `p-6 lg:p-8`, pa `admin-ui` taj padding mora ukloniti kada je `.admin-page` njegov izravni child. Ne ostavljati oba paddinga jer dvostruki vertikalni razmak stvara umjetni overflow i scrollbar na kratkim stranicama.
- Izbjegavati "karticu u kartici": admin paneli na mobitelu ne crtaju vanjski obrub/sjenu niti dodatan horizontalni padding, a upload polja, pomoćni blokovi i form sekcije ne smiju stvarati više ugniježđenih obruba. Media upload shell nema vlastiti obrub ni na desktopu ni na mobitelu; vidljiv ostaje samo unutarnji klikabilni image frame. Za media upload koristiti shared `admin-media-upload-*` klase iz `admin-ui`.
- Admin preview za istaknute/featured slike mora biti ujednačen kroz aplikaciju: široke slike zadano koriste `aspect-video` i `object-contain`, a admin liste za objave/radove ne smiju koristiti cropani `thumb` za glavni preview jer korisniku može izgledati kao pogrešno uvećana slika. Ako se želi crop u javnom layoutu, to je zasebna dizajnerska odluka javnog prikaza, ne admin previewa.
- Na mobitelu form itemi moraju imati veći vertikalni razmak nego na desktopu. Ne rješavati to lokalnim `mt-*` klasama po svakoj formi; shared mobile spacing ide u `admin-ui.css`.
- Svi interaktivni elementi koji izgledaju kao akcija, uključujući sekundarne gumbe i povezivanje galerije, moraju imati jasan `cursor-pointer`/hover signal.
- `admin-ui` mora ostati prezentacijski paket: bez queryja, validacije, permission logike, Actiona, Eventa, Listenera ili model saveova.
- Premium admin standard je miran i radno usmjeren: obične površine koriste najviše `rounded-lg`, statične kartice se ne pomiču na hover, a hover animacija pripada samo stvarno klikabilnim elementima.
- Statistike moraju koristiti adaptivni `admin-stat-grid` kako bi 2-3 kartice popunile red bez prazne četvrte kolone. Ponavljive površine i sličice koristiti kroz shared `admin-list-thumbnail`, `admin-list-empty-icon`, `admin-inset-panel` i `admin-list-card`, ne lokalnim kopijama klasa.
- Flux paginaciju prikazati samo kada paginator vraća `hasPages() === true`; na popisu s jednom stranicom ne prikazivati neaktivne prethodno/sljedeće kontrole.
- Profil, sigurnost, package postavke i superadmin stranice dio su istog admin sustava: hrvatski tekst, `x-admin-ui::page-header`, paneli, loading state i mobilni ritam ne smiju ostati na Laravel starter predlošku.
- Kod konfiguriranih editora glavni page header prati aktivni tab (`Sadržaj`, `Izgled`, `Postavke`) i njegov opis iz iste `Tab` definicije. Ne držati statični naziv entiteta iznad svih tabova niti duplicirati tab copy u host viewu.

## Validacija u adminu
- Sva obavezna polja moraju vraćati kratku poruku `Obavezno polje`.
- Centralni izvor za `required`, `required_if`, `required_with`, `required_without`, `required_array_keys`, `accepted` i srodna pravila je `App\Providers\AppServiceProvider::configureRequiredValidationMessages()` uz `lang/hr/validation.php`.
- Ne dodavati nove ručne poruke tipa `Unesite...`, `Odaberite...` ili `Polje je obavezno` za `*.required` pravila; ako komponenta mora imati custom `messages()`, required poruka mora ostati `__('Obavezno polje')`.

## Admin paginacija
- Kada Flux paginacija mijenja stranicu liste, tablice ili grid prikaza u adminu, dodati `scroll-to` na početak relevantnog sadržaja.
- Wrapper liste/tablice treba imati stabilan `id`, npr. `id="table"`, a paginacija treba koristiti obrazac: `<flux:pagination :paginator="$orders" scroll-to="#table" />`.
- Ako sadržaj nije stvarna tablica, koristi jasan id poput `#list`, `#archive-list` ili `#items`, ali korisnik nakon promjene stranice ne smije ostati pri dnu popisa.

## Admin submit gumbi
- Primarni Livewire submit gumbi u administraciji trebaju koristiti `<x-admin-ui::submit-button target="save">...</x-admin-ui::submit-button>`.
- Gumb mora imati scoped `wire:target`, Flux loader, disabled state tijekom requesta i loading label `Spremanje...`.
- Primarni save gumb na većim formama držati unutar iste `<form wire:submit="...">`; vanjski submit, `form="..."` i ručni `wire:click="save"` koristiti samo kao iznimku s fokusiranim testom.
- Ako je vanjski submit nužan, shared Blade wrapper mora proslijediti izvorni `$attributes` bag izravno u nested Flux komponentu. Ne preimenovati attribute bag i ne stavljati `@if` direktive među atribute nested komponente; obavezno testirati da renderirani gumb stvarno sadrži očekivane `form` i `wire:target` atribute.
- Veće admin forme trebaju imati `wire:loading.class="admin-panel-content-loading"` i `x-admin-ui::loading-overlay` s tekstom `Spremanje...`.
- Ne pisati običan `<flux:button type="submit" variant="primary">` za spremanje Livewire formi bez loading/disabled ponašanja.
- Veće edit forme trebaju koristiti obrazac "isto kao u objavama": `savedStateSnapshot`, `isDirty()`, info toast `Nema promjena za spremanje.`, prikaz zadnje izmjene s korisnikom i `wire:poll.180000ms="autoSave"` koji sprema samo kada postoje promjene.
- Veće create/edit forme trebaju koristiti `x-admin-ui::editor-header`: kontekst s ikonom, jasan naslov zadatka, kratak opis, zaseban metadata slot i akcije poredane od navigacije prema primarnom spremanju. Editor header ostaje bez kartice i ne izrađuje se ručno po entitetu.

## Admin nadzorna ploča
- Dashboard smije prikazivati samo akcije i informacije koje su korisniku stvarno važne u svakodnevnom radu.
- Ne stavljati u brze akcije administrativne detalje koje korisnik ne može urediti ili rijetko dira, npr. uređivanje organizacije ako mu to nije dopušteno.

## Design stil
- Manje "card UI" i manje dekorativan.
- Admin list/detail stranice moraju koristiti `x-admin-ui::page-header` s `icon` propom usklađenim s glavnom sidebar navigacijom. Ne raditi ručni `admin-page-header` markup po viewovima osim ako postoji stvaran layout razlog.
- Create/edit forme trebaju koristiti `x-admin-ui::editor-header`; ikona mora odgovarati sidebar stavci ili najbližem navigacijskom kontekstu.
- Admin save/create/update forme moraju koristiti `x-admin-ui::submit-button` s točnim Livewire `targetom`, `wire:loading.class="admin-panel-content-loading"` na formi/wrapperu i `x-admin-ui::loading-overlay` s tekstom `Spremanje...`. Ručni primarni `flux:button` za spremanje ne koristiti osim ako nije specifičan non-form flow.
- Ako parent header gumb sprema child Livewire editor preko `$dispatch(...)`, parent gumb mora imati isti saving UX (`Spremanje...`, spinner, disabled), a child komponenta mora u `finally` poslati završni event i u svoje `wire:target`/overlay targete uključiti metodu koja se stvarno poziva, npr. `saveAllChanges`.

## Admin preview kartice
- Admin preview sličice layouta moraju vizualno pratiti javni prikaz tog layouta, ali ostati uredne, pastelne i mirne kao na postojećem primjeru iz admina.
- Layout preview kartice u adminu trebaju biti u gridu od 4 kartice u redu na širokom ekranu (`xl:grid-cols-4`), 2 u redu na srednjim ekranima (`md:grid-cols-2`) i 1 u redu na mobitelu.
- Svaka preview kartica je bijela, s tankim neutralnim obrubom, `rounded-xl`, `p-4` i mirnim hover stanjem. Ne koristiti pune, tamne, agresivne ili tenant-primary pozadine na cijeloj kartici.
- Aktivna preview kartica koristi ružičasti/magenta akcent kao u admin primjeru: ružičasti obrub, vrlo blaga ružičasta pozadina i suptilan ring. Aktivno stanje ne smije preuzeti primarnu boju tenanta ako ona vizualno odskače.
- Gornja sličica unutar preview kartice ima vrlo blagu ružičastu pozadinu i ružičasti obrub/ring. Ona je samo okvir za minijaturni prikaz layouta, ne dominantna obojena ploha.
- Unutar preview sličice koristiti pastelnu paletu iz primjera: mint zelena, nježna žuta, svijetloplava i po potrebi druga blaga zelena. Ne pretvarati sve blokove u primarnu boju tenanta.
- Bijela boja ostaje glavna boja mini kartica unutar previewa. Pastelne boje koriste se samo za thumbnail blokove, ikone, male oznake, kružiće, linije ili vizualne placeholder elemente.
- Ne miješati pristupe unutar iste sekcije: ako jedna varijanta koristi pastelne thumbnail blokove, sve varijante te sekcije trebaju koristiti isti pastelni princip.
- Izbjegavati sive placeholder blokove ako druge preview sličice u istoj sekciji koriste pastelne boje.
- Admin preview ne mora biti pixel-perfect, ali mora jasno prikazati strukturu javnog layouta: raspored, odnos slike/teksta, istaknute stavke i ritam kartica.
- Preview ne smije prikazivati crte, obrube, separatore ili dekoracije koje javni layout više nema.
- Preview sličica treba imati stabilnu visinu (`h-28`, osim ako postojeći layout stvarno treba manju visinu) i ne smije širiti karticu ovisno o tekstu.
- Naziv layouta ide ispod sličice kao kratak podebljan tekst, a opis ispod njega kao manji miran tekst. Ne stavljati naziv i opis unutar same preview sličice.
- Ako sekcija ima više layout varijanti, sve preview kartice te sekcije moraju imati isti okvir, istu veličinu sličice, isti selected state i isti princip pastelnih boja.
