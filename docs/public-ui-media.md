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

## Placeholderi

Placeholder mora koristiti isti okvir, radius i aspect ratio kao prava slika.

```html
<figure class="cx-public-media-frame">
    <div class="aspect-[4/3] cx-public-media-placeholder">
        ...
    </div>
</figure>
```

Placeholder ne smije izgledati kao druga vrsta UI elementa od slike koju zamjenjuje.

## Checklist

Prije završetka nove javne sekcije ili layout varijante provjeriti:

- koristi li sadržajna slika `cx-public-media-frame` ili `cx-public-media-frame-surface`
- nalazi li se radius na omotu s `overflow-hidden`
- ima li slika stabilan aspect ratio ili stabilnu visinu
- koristi li klikabilna slika `cx-public-image-hover`
- koristi li statična slika bez linka miran prikaz bez hover zooma
- koristi li avatar/logotip `rounded-full` samo kada je stvarno identitetski mali vizual
- postoji li overlay samo kada ima funkciju
- izgleda li placeholder kao ista komponenta koju zamjenjuje
