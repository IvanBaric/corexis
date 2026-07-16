# Corexis Public Media Standard

Ovaj standard definira kako javne stranice koriste slike, video thumbove, avatare, logotipe, placeholder vizuale i overlaye.

Cilj je da slike izgledaju kao dio istog sustava, čak i kada layout varijante imaju različit raspored.

## Uloge Slika

| Uloga | Klasa | Koristi se za |
| --- | --- | --- |
| Media okvir | `cx-public-media-frame` | osnovni omot slike, videa ili vizualnog placeholdera bez dodatne sjene |
| Media okvir sa sjenom | `cx-public-media-frame-surface` | samostalna slika ili media kartica kojoj treba ista dubina kao ostale površine |
| Slika unutar okvira | `cx-public-media-image` | slika koja ispunjava okvir s `object-cover` |
| Placeholder | `cx-public-media-placeholder` | prazno stanje kada slika nije unesena |
| Blagi overlay | `cx-public-media-overlay` | klikabilna video/media kartica s tekstom ili play kontrolom preko slike |
| Jaki overlay | `cx-public-media-overlay-strong` | hero ili text-over-image layout gdje čitljivost teksta traži jači kontrast |
| Avatar okvir | `cx-public-avatar-frame` | kružne fotografije osoba, svjedočanstava i identitetski mali vizuali |
| Avatar slika | `cx-public-avatar-image` | slika unutar kružnog avatar okvira |

## Radius

Sadržajne slike javne stranice koriste pravokutni `rounded-xl` okvir. Slika sama najčešće nema radius; radius pripada omotu koji ima `overflow-hidden`.

Iznimka je slika unutar umetnutog ili padded okvira. Tada unutarnja slika smije dobiti isti `rounded-xl`, jer se vizualno ponaša kao zaseban umetnuti medij.

`rounded-full` koristiti samo za avatare, male logotipske/identitetske slike, ikone i badge elemente. Ne koristiti kružne cropove za obične sadržajne fotografije sekcije.

## Sjene

Sjena pripada okviru ili kartici, ne samoj slici.

Za samostalnu sliku koristiti:

```html
<figure class="cx-public-media-frame-surface">
    <img class="cx-public-media-image" alt="">
</figure>
```

Za sliku unutar kartice najčešće je dovoljan:

```html
<figure class="cx-public-media-frame">
    <img class="cx-public-media-image" alt="">
</figure>
```

Ne dodavati `shadow-lg`, `shadow-2xl` ili custom sjene na obične slike.

## Hover

Klikabilne slike u karticama koriste isti hover obrazac:

```html
<a class="group">
    <figure class="cx-public-media-frame">
        <img class="cx-public-media-image cx-public-image-zoom" alt="">
    </figure>
</a>
```

`cx-public-image-zoom` koristiti samo kada je media dio klikabilne kartice ili klikabilnog zapisa. `cx-public-image-hover` ostaje kompatibilan alias za isti obrazac. Statične informativne slike ne moraju zumirati na hover.

Standardni hover za sliku je definiran u Corexis motion standardu: `duration-500` i `group-hover:scale-[1.03]`.

## Overlay

Overlay ne koristiti kao dekoraciju. Dopušten je kada ima funkciju:

- čitljivost teksta preko slike,
- video/play stanje,
- hero ili feature layout gdje je tekst namjerno na fotografiji.

Zadani overlay je `cx-public-media-overlay`. `cx-public-media-overlay-strong` koristiti samo kada je tekst stvarno na slici i treba jači kontrast.

Ne miješati više overlay stilova unutar iste vrste sekcije bez jasnog razloga.

## Aspect Ratio

Aspect ratio smije ovisiti o layoutu, ali mora biti stabilan i definiran u klasi, npr. `aspect-[4/3]`, `aspect-[16/9]`, `aspect-[16/10]` ili `aspect-video`.

Ne ostavljati sadržajne slike bez stabilne visine/aspect odnosa ako njihov omjer može promijeniti ritam sekcije.

## Tehnička Optimizacija

Javni prikaz ne smije nasumično koristiti originalnu uploadanu sliku. Za korisničke uploadane slike Spatie Media Library "original" u storageu mora biti optimizirani master, a ne sirova datoteka koju je korisnik poslao.

Korisnički uploadane public slike moraju prolaziti kroz Spatie Media Library/gallery kolekcije. To uključuje header/singleton slike, blog featured image, radove/proizvode, galerije i slike stavki sekcija. Novi korisnički image upload tok ne smije spremati običan storage path izvan Media Libraryja.

Stored master za Media Library upload optimizira se prije `addMedia()` poziva. Format, kvaliteta i maksimalne dimenzije dolaze iz `gallery.stored_originals.*` u centralnom gallery configu. Zadani stored master za produkcijski public sadržaj je WebP.

Media conversions su drugi sloj optimizacije i ostaju centralne. Format i kvaliteta konverzija dolaze iz `gallery.conversions.format` i `gallery.conversions.quality` u centralnom gallery configu. Zadani format za javni prikaz je WebP.

Za kontrolirane statične ili legacy public media pathove koji nisu korisnički upload koristiti `corexis_public_media_url()`. Helper je centralno mjesto za odabir optimizirane sidecar varijante prema `Accept` headeru preglednika: AVIF kada je dostupan i podržan, zatim WebP, zatim original kao fallback. `corexis_public_image_optimizer()` nije zamjena za Media Library kod novih korisničkih uploadova.

Za javne slike koje dolaze iz media library/gallery sustava koristiti imenovane konverzije prema stvarnoj ulozi slike:

| Uloga | Preferirana konverzija | Napomena |
| --- | --- | --- |
| Mala sličica, mali avatar | `thumb` | Za slučajeve gdje je crop namjeran i očekivan. |
| Admin preview istaknute slike | `medium` | Veliki preview koristi `object-contain`; kompaktne admin liste za objave/radove smiju koristiti `object-cover` kako bi slika ispunila thumbnail okvir. |
| Logo ili manji identitetski vizual | `medium` | Kada je potreban ograničen okvir bez velikog cropa. |
| Standardna slika kartice ili sekcije | `large` | Zadani izbor za javni prikaz u layoutima. |
| Velika hero slika, featured slika, lightbox početna slika | `xlarge` | Koristiti samo kada layout stvarno treba veću sliku. |
| Lightbox/fullscreen fallback | original | Dopušteno samo nakon pokušaja `xlarge`/`large`. |

## Header i Hero Slike

Header slike javne stranice nisu obične sekcijske slike. Ako se prikazuju full-bleed ili kao veliki hero vizual, moraju koristiti zasebnu `hero` konverziju za header kolekciju, ne standardni `xlarge`.

Preporučena izvorna fotografija za desktop header je barem 3000 px širine. Ako korisnik uploada manju fotografiju, aplikacija smije spremiti optimizirani master, ali ne treba umjetno obećavati 4K oštrinu koju izvorna slika nema.

Default/fallback header asseti moraju biti pripremljeni kao stvarni hero asseti, a ne male preview slike. Ne koristiti fallback od oko 1200 px za full-width header.

Ako se koristi Spatie media objekt, prvo probati `getAvailableUrl([...])` s listom preferiranih konverzija. Direktni `getUrl()` bez konverzije smije biti samo fallback. Za string path koji nije media objekt koristiti centralni helper aplikacije, npr. `corexis_public_media_url()`, a ne hardkodirani disk.

Javni media disk mora imati jedan centralni izvor konfiguracije. Host projekt treba koristiti `MEDIA_DISK` za Media Library, galeriju i Corexis helper `corexis_public_media_disk()`. Ne uvoditi zasebne disk varijable za isti javni media storage, npr. `GALLERY_DISK`, osim ako se radi o stvarno odvojenoj vrsti storagea s jasnim razlogom.

## Upload i Konverzije

Upload validacija mora ići kroz Corexis image upload policy ili gallery context validaciju. Ne duplicirati pravila za format, veličinu datoteke ili minimalne dimenzije po komponentama.

Za admin Livewire upload korisničkih fotografija, posebno header/hero slike i fotografije s mobitela, obavezno predvidjeti client-side pripremu prije Livewire uploada. Browser treba lokalno provjeriti tip i veličinu datoteke, po potrebi smanjiti velike mobilne fotografije na razumnu maksimalnu dimenziju, komprimirati ih u web-prikladan format i tek tada pokrenuti Livewire upload. Ovo ne zamjenjuje server-side validaciju ni Media Library optimizirani master, nego sprječava beskonačan "Učitavanje" dojam na sporim mobilnim vezama i smanjuje neuspjele upload pokušaje.

Upload UI mora korisniku prikazati jasna stanja na hrvatskom: priprema slike, postotak uploada i razumljivu grešku za format, veličinu ili internetsku vezu. Ne oslanjati se samo na generički Livewire `wire:loading` loader za velika image polja.

Zadani formati su JPG, PNG i WebP. SVG ne tretirati kao običnu fotografiju; SVG se smije koristiti samo za kontrolirane ikone/logotipe i mora imati zaseban sigurnosni put.

Media library konverzije trebaju biti konfigurirane centralno u gallery configu. Layouti ne smiju uvoditi vlastite ad hoc dimenzije, formate ili kvalitetu konverzija. Ako nova vrsta javnog prikaza stvarno treba novu veličinu, prvo dodati imenovanu konverziju u gallery standard, zatim ju koristiti kroz postojeće helper metode.

Sirove korisničke originale ne čuvati kao Media Library originale. Aplikacija treba spremiti optimizirani master i generirati konverzije iz njega. Javni HTML ne smije servirati višemegabajtni original za običnu karticu ili sekcijsku sliku.

## Responsive Images

Responsive images nisu obavezne za svaki projekt, ali ako se uključe moraju se uključiti centralno u gallery/media konfiguraciji, ne pojedinačno po layoutu.

Prije uključivanja responsive images provjeriti:

- storage trošak i broj generiranih datoteka
- queue/worker obradu konverzija
- postoji li javni helper ili Blade komponenta koja renderira ispravan `srcset`
- fallback kada responsive datoteke još nisu generirane

Ne uključivati responsive images samo zato što postoje u media libraryju. Uključiti ih tek kada javni render stvarno koristi `srcset` i kada produkcijski storage/queue setup može pouzdano obraditi dodatne datoteke.

## Lazy Loading

Slike ispod prvog ekrana trebaju koristiti `loading="lazy"` kada layout nema poseban razlog za eager load.

Hero/above-the-fold slika ne smije biti lazy ako je ključna za prvi prikaz. Za takvu sliku preferirati odgovarajuću veću konverziju i stabilan aspect/height kako ne bi došlo do skakanja layouta.

Livewire lazy loading je odvojen sloj optimizacije od HTML image lazy atributa. Javne sekcije koje nisu u prvom ekranu smiju se renderirati kao Livewire `lazy` komponente kako bi se odgodili queryji, mount logika i teže galerijske liste dok se korisnik ne približi sekciji.

Kod Livewire lazy sekcija obavezno dodati placeholder koji ima isti root element kao stvarna komponenta i stabilnu minimalnu visinu. Placeholder ne smije izgledati kao zaseban dizajn sekcije; treba samo mirno držati prostor dok se stvarni sadržaj ne učita.

## Placeholderi

Placeholder mora koristiti isti okvir, radius i aspect ratio kao prava slika.

Za javne media slotove koristiti zajedničku Corexis komponentu, ne ručno složene sive ili prazne blokove:

```blade
<figure class="cx-public-media-frame">
    <x-corexis::public-image-placeholder
        class="aspect-[4/3] w-full"
        icon="photo"
    />
</figure>
```

Ikona mora odgovarati tipu sadržaja: `photo` za fotografije i galeriju, `cube` za radove/proizvode, `newspaper` za objave, `film` za video thumbove, `heart` za partnere ili podršku.

Placeholder ne smije izgledati kao druga vrsta UI elementa od slike koju zamjenjuje. Ako je prava slika kružna, npr. avatar, komponenta smije dobiti `rounded-full`; ako je prava slika u `rounded-xl` okviru, placeholder ostaje u istom okviru i aspect ratioju.

## Checklist

Prije završetka nove javne sekcije ili layout varijante provjeriti:

- koristi li sadržajna slika `cx-public-media-frame` ili `cx-public-media-frame-surface`
- nalazi li se radius na omotu s `overflow-hidden`
- ima li slika stabilan aspect ratio ili stabilnu visinu
- koristi li klikabilna slika `cx-public-image-hover`
- koristi li javni prikaz imenovanu konverziju (`thumb`, `medium`, `large`, `xlarge`) umjesto originala
- koristi li original samo kao lightbox/fullscreen ili zadnji fallback
- je li upload validacija došla iz Corexis/gallery policyja, a ne iz lokalnog hardcodea
- ima li admin Livewire image upload client-side pripremu/kompresiju velikih mobilnih fotografija prije slanja
- koristi li statična slika bez linka miran prikaz bez hover zooma
- koristi li avatar/logotip `rounded-full` samo kada je stvarno identitetski mali vizual
- postoji li overlay samo kada ima funkciju
- izgleda li placeholder kao ista komponenta koju zamjenjuje
