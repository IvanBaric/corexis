# Corexis Livewire Performance Standard

Ovaj dokument sadrži zajednički standard za performanse Livewire komponenti u IvanBaric projektima i paketima. Standard je namjerno proširiv: svako novo potvrđeno pravilo treba dodati ovdje uz jasan razlog i, kada je moguće, regresijski test.

Sigurnosna pravila imaju prednost pred optimizacijom. Tenant scope, published scope, autorizacija, validacija i `#[Locked]` state ne smiju se ukloniti radi manjeg broja upita. Obavezna Livewire sigurnosna pravila nalaze se u [Security standardu](security.md).

## Temeljna načela

- Optimizirati na temelju izmjerenog problema: broj i oblik SQL upita, veličina Livewire payloada, broj requestova, vrijeme renderiranja ili browser ponašanje.
- Public Livewire state nije cache. Svako public svojstvo tretirati kao state koji se prenosi između browsera i servera te ga držati što manjim i jednostavnijim.
- Dohvat podataka treba ostati tenant-scoped i autoriziran. Uklanjanje ponovljenog upita dopušteno je samo ako ponovno korišten model prolazi iste poslovne uvjete kao fallback query.
- Najprije ukloniti N+1 i nepotreban payload, zatim smanjiti učestalost requestova, a tek nakon mjerenja uvoditi aplikacijski cache ili složeniju arhitekturu.
- Optimizacija zajedničkog helpera ili relacije pripada paketu koji je vlasnik tog ponašanja, ne lokalnoj kopiji u host aplikaciji.

## Upiti i dohvat podataka

- Ne koristiti `Model::all()` u Livewire listama. Za korisnički ili rastući skup podataka koristiti `paginate()`, `simplePaginate()` ili drugi ograničeni query. Iznimka je malen, konačan lookup skup uz dokumentiran razlog.
- Liste moraju odabrati samo stupce potrebne za prikaz i akcije. Kod suženog `select()` obavezno zadržati primarni ključ te strane ključeve potrebne Eloquent relacijama i tenant provjerama.
- Sve relacije koje Blade, accessor, presenter ili helper čita unutar petlje moraju biti eager-loadane prije renderiranja.
- Eager load ograničiti na potrebne relacije, kolekcije i stupce. Ne učitavati cijelu galeriju, taxonomy stablo ili sve media zapise kada prikaz koristi samo featured sliku ili naziv kategorije.
- Helper koji čita relaciju mora poštovati već učitanu relaciju preko `relationLoaded()` i ne smije ponovno pokrenuti query za svaki redak.
- Kada je potreban samo broj ili postojanje povezanih zapisa, koristiti `withCount()` ili `withExists()` umjesto učitavanja cijele relacije.
- Statističke kartice ne smiju pokretati zaseban `COUNT` query za svaku karticu ako se vrijednosti mogu dobiti jednim uvjetnim agregatnim upitom.
- Filteri, tenant scope i zadani sortovi na rastućim tablicama moraju imati odgovarajuće složene indekse. Indekse definirati migracijom u paketu koji posjeduje tablicu.
- Skupi, neovisni dashboard ili statistički blok izdvojiti u lazy child komponentu kada nije potreban za prvi koristan prikaz stranice.

## Public state i hidratacija

- U public svojstvima držati korisnički unos, male allowlistane filtere, paginacijski state i minimalne identifikatore potrebne između requestova.
- Ne držati cijele kolekcije, modele s učitanim odnosima, velike rezultate upita ili duplicirane velike arrayeve u public svojstvima samo radi renderiranja. Takve podatke dohvatiti kroz computed metodu ili query u render ciklusu.
- Server-owned UUID-e, model identitete, tenant kontekst i mount-only konfiguraciju zaključati prema [Security standardu](security.md).
- Za dirty-state većih editora spremiti zaključani hash normaliziranog stanja, ne drugu kopiju cijelog rich-text sadržaja ili forme.
- `#[Computed]` rezultat unutar istog requesta koristiti kao svojstvo, primjerice `$this->stats`, kako bi Livewire iskoristio computed cache. Izravni ponovljeni pozivi `$this->stats()` ponovno izvršavaju metodu.
- Private memoizacija vrijedi samo unutar jednog PHP requesta. Ona nije trajni cache ni sigurnosna granica i mora razlikovati stanje „nije razriješeno” od valjanog `null` rezultata.
- Model primljen u `mount()` smije se ponovno koristiti samo ako zadovoljava iste tenant, status i vidljivost uvjete kao query koji se izvršava na kasnijim Livewire requestovima.

## Učestalost Livewire requestova

- Ne koristiti `wire:model.live` bez potrebe za odgovorom servera tijekom tipkanja.
- Kada je live pretraga opravdana, dodati primjeren debounce, primjerice `wire:model.live.debounce.300ms`, i ograničiti duljinu search inputa prije queryja.
- Za polja koja se validiraju ili obrađuju tek nakon izlaska iz polja koristiti `wire:model.blur`; za submit forme zadržati zadano odgođeno sinkroniziranje kada je dovoljno.
- `wire:poll` koristiti samo za podatke koji se stvarno mogu promijeniti bez korisničke akcije. Poll metoda mora brzo završiti bez pisanja kada nema promjene i ne smije ponovno dohvaćati velike relacije bez potrebe.
- Evente slati ciljano. Izbjegavati globalne evente koji nepotrebno rerenderiraju više nepovezanih komponenti.
- Javni editori koji mijenjaju jednu sekciju ili jedan zapis trebaju emitirati event vezan uz stabilni identitet, primjerice `sectionUuid`, i osvježiti samo pogođenu Livewire komponentu. Puni browser reload ostaviti samo za promjene koje stvarno mijenjaju strukturu cijele stranice.

## Granice komponenti i browser state

- Ne pretvarati svaki redak tablice ili karticu u zasebnu Livewire komponentu. Child komponenta je opravdana kada redak ima vlastiti složen state, neovisni lifecycle ili izolirano skupo učitavanje.
- Stabilan `wire:key` mora koristiti trajni identitet zapisa, primjerice UUID ili primarni ključ. Ne koristiti indeks petlje za podatke koji se mogu filtrirati, sortirati, dodavati ili uklanjati.
- Tabovi, dropdowni, accordion state i jednostavni modali koji ne trebaju server podatke trebaju ostati u Flux UI-u ili Alpineu bez dodatnog Livewire requesta.
- Stateful modal koji dohvaća ili mijenja model ostaje server-owned i slijedi Admin UI i Security standarde; ne premještati ga u Alpine samo radi uštede requesta.
- Parent i child komponenta ne smiju držati dupliciranu veliku kopiju istog editabilnog sadržaja. Vlasništvo nad form stateom mora biti jasno i jedinstveno.
- Skrivena Livewire komponenta koja služi samo kao event/action handler ne smije početno renderirati puni administracijski prikaz. Koristiti minimalan handler view, a sadržaj modala, lookup liste i povezane queryje renderirati tek nakon odabira konkretne akcije; regresijski test treba brojati ciljane upite prije otvaranja akcije.

## Renderiranje i mediji

- Blade petlja ne smije skrivati query u accessorima, helperima, policy provjerama ili media URL resolverima. Query plan treba biti vidljiv iz komponente ili kontrolera koji priprema podatke.
- Za slike i medije koristiti pravila iz [Storage and media standarda](storage-media.md) i [Public media standarda](../public-ui-media.md), uključujući odgovarajuće konverzije i lazy loading.
- Paginaciju prikazati samo kada postoji više stranica, a pravila admin prikaza i loading statea preuzeti iz [Admin UI standarda](admin-ui.md).
- Anonimnom korisniku ne montirati privilegiranu child komponentu ako njezin prikaz i akcije nisu dostupni. Backend autorizacija i dalje ostaje obavezna ako se komponenta pozove izravno.

## Cache

- Aplikacijski cache uvoditi tek nakon što su query, eager loading i payload optimizirani i kada mjerenje potvrdi korist.
- Cache ključ mora uključiti tenant i sve parametre koji mijenjaju rezultat, uključujući locale, filter ili permission kontekst kada je relevantan.
- Za svaki cache mora postojati jasan invalidation put. Ne cacheirati autorizacijski rezultat ili mutable model bez dokumentiranog invalidation mehanizma.
- Kratka private memoizacija jednog requesta preferira se nad globalnim cacheom kada samo treba spriječiti ponovljeni query unutar istog render ciklusa.

## Mjerenje i regresijski testovi

- Prije i poslije veće optimizacije zabilježiti barem relevantan broj SQL upita ili broj Livewire requestova. Za payload promjene provjeriti public state i browser Network zapis.
- N+1 test treba renderirati komponentu s jednim pa s više zapisa i dokazati da broj relevantnih relation upita ostaje konstantan.
- Kada framework, tenant resolver ili test harness dodaje vlastite upite, brojati ciljanu SQL kategoriju umjesto krhkog ukupnog broja svih upita.
- Za agregirane statistike testirati da se koristi jedan agregatni query i da vraćene vrijednosti ostaju točne.
- Query optimizacija mora imati funkcionalni render test kako suženi `select()` ili constrained eager load ne bi uklonio podatak potreban Bladeu.
- Promjena Livewire komponente mora proći sigurnosni scan i fokusirane testove propisane [Security standardom](security.md).

## Kontrolna lista za pregled

Prilikom pregleda Livewire modula provjeriti ovim redoslijedom:

1. Postoji li `Model::all()` ili neograničen query nad rastućim podacima?
2. Drže li public svojstva cijele kolekcije, modele s odnosima ili velike arrayeve?
3. Postoji li `wire:model.live` bez opravdanja i debouncea?
4. Je li svaki redak liste nepotrebno zasebna Livewire komponenta?
5. Postoje li N+1 upiti u Bladeu, accessorima, helperima ili media resolverima?
6. Jesu li liste paginirane i upiti ograničeni na potrebne stupce?
7. Koristi li se `withCount()` ili `withExists()` kada nije potreban sadržaj relacije?
8. Imaju li petlje stabilan `wire:key` temeljen na identitetu zapisa?
9. Mogu li čisti UI state voditi Flux UI ili Alpine bez server requesta?
10. Mogu li se skupe statistike ili neovisni paneli učitati kao lazy child komponenta?
11. Postoje li odgovarajući indeksi za tenant, filtere i sortove?
12. Postoji li test koji bi ponovno uvođenje N+1 ili prevelikog public statea učinio vidljivim?

## Nadopunjavanje standarda

- Novo pravilo dodati tek kada je ponašanje potvrđeno dokumentacijom, mjerenjem, testom ili stvarnim problemom u projektu.
- Uz pravilo zapisati konkretan preporučeni obrazac; izbjegavati neprovjerene univerzalne zabrane.
- Ako novo saznanje mijenja postojeće pravilo, izmijeniti ovaj dokument umjesto dodavanja proturječnog pravila u drugi README ili projektni `AGENTS.md`.
- Pravilo specifično samo za Nivu pripada u projektni profil; ponovno upotrebljiv Livewire obrazac pripada ovdje.
