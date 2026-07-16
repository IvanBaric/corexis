# Corexis Public UI Standard

Ovaj dokument sadrži obavezna opća pravila za javni render, vizualnu dosljednost i animacije.

## Ugodna i pristupačna navigacija
- Zajednički javni layout mora kao prvi fokusabilni element imati poveznicu `Preskoči na sadržaj` koja se prikazuje tek pri fokusu tipkovnicom.
- Cilj poveznice mora biti stabilni `#main-content`, a javna stranica mora imati jedan semantički `main` landmark. Editorske kontrole i modalni hostovi ne smiju biti dio tog landmarka.
- Globalni gumb za povratak na vrh mora imati pristupačan naziv i tooltip te poštovati `prefers-reduced-motion` bez glatkog skrolanja kada korisnik traži smanjene animacije.

## Rich text i odlomci
- Kada javni single prikaz renderira opis iz editora ili višeredni tekst, ne koristiti squish() prije dijeljenja na odlomke jer briše nove redove.
- Za duži tekst koristiti obrazac kao kod single objave: trim(), preg_split po praznom retku, zatim nl2br(e(trim(paragraph))) unutar p elementa.
- U karticama/listama smije se koristiti stripTags()->squish()->limit(...) jer tamo treba kratak sažetak u jednom retku ili kraćem bloku.

## Flux flyout, dropdown i stabilnost javnog layouta
- Host aplikacija mora nakon Flux CSS-a importati Corexis `interaction-cursors.css`. Zajednički stylesheet daje `cursor: pointer` semantičkim akcijama, Flux tabovima i izbornicima te Livewire/Alpine klik akcijama, a disabled kontrolama `cursor: not-allowed`; lokalni `cursor-wait` utility smije nadjačati to pravilo tijekom requesta.
- Host aplikacija mora importati `packages/ivanbaric/corexis/resources/css/public-overlays.css` nakon Flux CSS-a i označiti javni `<body>` atributom `data-corexis-public-shell`. Ne kopirati ovaj workaround u projektni `app.css`.
- Corexis na desktopu koristi `scrollbar-gutter: stable` samo za označeni javni shell kako otvaranje Flux dropdowna ili modala ne bi horizontalno pomicalo sadržaj.
- Budući da javni desktop layout koristi root scrollbar, otvoreni Flux `ui-dropdown[data-open]` ne smije ostaviti Fluxov inline `overflow: hidden` na `<html>` i sakriti scrollbar. Shared `public-overlays.css` zadržava `overflow-y: scroll !important` samo dok je dropdown otvoren i nijedan Flux modal nije otvoren.
- Dropdown iznimku ne primjenjivati na otvorene modale ili flyoute: modal mora i dalje zaključati pozadinski scroll, a njegov sadržaj imati vlastiti scroll owner.
- Dok je Flux flyout otvoren, root gutter mora prijeći na `auto`. Flux ima poznat problem s root `scrollbar-gutter: stable` i flyout modalima; bez ovog izuzetka flyout može biti pogrešno pomaknut ili nestati. Referenca: [Flux issue #2348](https://github.com/livewire/flux/issues/2348).
- Javni editorski flyout koji sadrži Flux dropdown, select, popover ili submenu mora koristiti `:dismissible="false"` i imati vidljiv eksplicitni gumb za zatvaranje. Native popover klik ne smije biti protumačen kao klik izvan flyouta i zatvoriti cijeli editor.
- Stateful javni flyout montira se jednom pri kraju stabilnog javnog layouta, izvan sadržaja stranice koji se Livewireom ponovno renderira. Ne uvjetno uklanjati root modala dok se mijenja njegov child editor.
- Admin shell ne koristi ovaj javni root workaround. Admin standard ostaje zaseban: `admin-ui.css` zaključava shell na `100dvh`, a `scrollbar-gutter: stable` postavlja samo na `[data-flux-main]` kao stvarni scroll owner.

## Vizualna dosljednost javnih sekcija
- Naslov sekcije i opis sekcije moraju uvijek biti odvojeni od sadržaja sekcije i ne smiju biti dio kartica, redova ili pojedinačnih zapisa.
- Prazna stanja javnih sekcija moraju koristiti `x-corexis::public-empty-state` i neutralan tekst za posjetitelje. Ne raditi ručne empty-state blokove po sekcijama i ne prikazivati admin upute tipa "Dodajte..." na javnom frontendu.
- Prazni media slotovi unutar javnih sekcija moraju koristiti `x-corexis::public-image-placeholder` s istim aspect ratiojem/radiusom kao prava slika. Ne raditi sive ili prazne fallback blokove bez ikonice.
- Ne dodavati dekorativne crte, linije, separatore, gradijente ili dodatne vizualne efekte ako nisu dio globalnog dizajna stranice.
- Svi layouti iste vrste sekcije moraju koristiti isti font, istu osnovnu veličinu teksta i isti vizualni ritam.
- Kartice moraju imati ujednačen `rounded` stil s ostatkom stranice. Ne uvoditi veće zaobljenje samo u jednom layoutu.
- Slike unutar kartica ili redova moraju imati ujednačen `rounded` stil s karticama i ostalim sekcijama.
- Ako sekcijska stavka ima ikonicu u adminu, javni layout treba ju prikazati u svakom izgledu gdje to ne narušava dizajn.
- Layouti ne smiju hardkodirati dekorativne ikone ili tekstove koji ne dolaze iz sadržaja/admina.
- Izbjegavati miješanje više tipografskih stilova unutar istog layouta. Naslovi stavki, opisi i pomoćni tekst trebaju pratiti postojeću hijerarhiju stranice.
- Novi izgled mora prvo izgledati kao dio postojeće stranice, tek onda kao zaseban dizajnerski uzorak.
- Klikabilni tekstovi na javnoj stranici ne smiju na hover dobiti podcrtanu crtu ispod teksta; dovoljan signal je ručica/kursor, boja linka i postojeći vizualni kontekst. Ako je naslov ili tekst zapisa klikabilan i zapis ima sliku, ista slika mora biti klikabilna i voditi na istu stranicu kao tekst.
- Tekstualni linkovi na javnoj stranici trebaju imati miran `transition` i na hover prijeći u nijansu primarne boje (`--niva-primary`, najčešće `--niva-primary-800`, a u dark modu svjetliju nijansu). Ne koristiti podcrtavanje kao hover signal.
- Galerijski layouti (Trenuci iz rada) moraju koristiti isti hover obrazac kao klikabilne kartice: `transition duration-200 hover:-translate-y-0.5 hover:shadow-md`; slike u klikabilnim galerijskim karticama koriste `transition duration-500 group-hover:scale-[1.03]`, a tekstualni dijelovi poveznica na hover prelaze u nijansu primarne boje.
- Svi klikabilni elementi na javnoj stranici moraju jasno pokazati ručicu/kursor (`cursor-pointer`). Ako je slika klikabilna poveznica, mora imati tekstualni hover/tooltip signal s nazivom sadržaja, npr. naziv galerije, objave, rada ili partnera, preko `title`/`aria-label` atributa ili vidljivog hover teksta.
- Provjeri samostalno i uskladi admin izgled sličica tako da jasnije prati javni prikaz.
- Samostalno bez pitanja kreni s radom, autonomno za vizualno ujednačavanje, bez dodatnih pitanja.

## Konzistentnost animacija i hover stanja
- Animacije na javnoj stranici moraju biti konzistentne kroz sve sekcije i sve layout varijante iste vrste sadržaja.
- Animacija ne smije izgledati kao zaseban dizajnerski efekt dodan samo jednom layoutu. Ako se animacija koristi, mora djelovati kao dio globalnog jezika stranice.
- Zadani hover obrazac za klikabilne kartice, zapise i redove je: `transition duration-200 hover:-translate-y-0.5 hover:shadow-md`.
- Ne miješati različite jačine pomaka na usporedivim elementima. Ne koristiti istovremeno `hover:-translate-y-0.5`, `hover:-translate-y-1` i druge vrijednosti bez jasnog razloga.
- `hover:-translate-y-1` koristiti samo ako je element namjerno istaknut kao veća hero/featured kartica. Za obične kartice i redove koristiti `hover:-translate-y-0.5`.
- Zadani hover obrazac za slike unutar klikabilnih kartica je: `transition duration-500 group-hover:scale-[1.03]`.
- Ne miješati `scale-[1.03]`, `scale-[1.04]`, `duration-500` i `duration-700` kroz istu stranicu bez jasnog razloga. Zadani standard je `duration-500` i `scale-[1.03]`.
- Ako jedan layout unutar iste sekcije koristi hover animaciju na kartici, svi usporedivi layouti te sekcije trebaju imati isti tip hover animacije.
- Ako jedan layout unutar iste sekcije koristi image zoom, svi usporedivi image-card layouti te sekcije trebaju koristiti isti image zoom obrazac.
- Statični informativni blokovi koji nisu klikabilni ne moraju imati hover animaciju.
- Ne dodavati hover animaciju samo zato što element izgleda kao kartica. Animacija ima smisla primarno na klikabilnim ili interaktivnim elementima.
- Linkovi u tekstu ne smiju dobiti podcrtavanje na hover. Signal klikabilnosti treba biti ručica/kursor, boja linka i postojeći vizualni kontekst. Ne dodavati pomake, zoom ili sjene na obične tekstualne linkove.
- Obični tekstualni linkovi trebaju koristiti blagi prijelaz boje prema primarnoj boji tenanta, bez promjene layouta, bez podcrtavanja i bez dodatnih dekoracija.
- Accordion elementi trebaju imati kratku i mirnu animaciju. Zadani obrazac je `x-transition.opacity.duration.150ms`.
- Sve FAQ accordion varijante trebaju koristiti isti tip animacije otvaranja, osim ako layout ima jasno opravdan drugačiji obrazac.
- Ikone koje se rotiraju u accordionu, npr. chevron, trebaju koristiti jednostavan `transition` i `rotate-180`.
- Rotacije (`rotate`, `-rotate`) koristiti samo ako je layout izričito zamišljen kao bilješka, papir, studio ili kreativna ploča.
- Ne koristiti rotacije kao opću dekoraciju za obične kartice, redove, partnere, objave, statistike ili FAQ.
- Ako layout koristi rotirane kartice, hover može vratiti karticu u neutralan položaj s `hover:rotate-0`, ali isti princip mora vrijediti za sve kartice tog layouta.
- Ne miješati animacije tipa pomak, zoom, rotacija i jaka sjena na istom elementu ako to nije namjerno istaknuti layout.
- Blage sjene smiju se pojačati na hoveru, ali ne koristiti agresivne `shadow-lg` efekte na običnim karticama ako ostatak stranice koristi `shadow-md`.
- Admin preview sličice ne trebaju imati unutarnje hover animacije.
- Admin preview kartica kao odabir smije imati hover stanje, ali sadržaj unutar preview sličice treba ostati statičan.
- Admin preview mora prikazivati strukturu i vizualni stil javnog layouta, ali ne mora prikazivati animacije javnog layouta.
- Prije završetka nove sekcije provjeriti postoje li animacije koje odskaču od ostatka stranice: pomak, zoom, trajanje, rotacija, sjena i accordion transition.
- Ako se postojeći layout uređuje, uskladiti animacije s ostalim layoutima iste sekcije prije dodavanja novih efekata.
