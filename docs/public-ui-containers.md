# Corexis Public Container Standard

Ovaj standard definira maksimalne širine javnih stranica, sekcija, tekstualnih blokova i namjerne full-bleed iznimke.

Premium dojam traži da stranica ima jasan container sustav. Ako jedan section header ide jako široko, drugi usko, treći do ruba, stranica izgleda neplanirano čak i kada su kartice i tipografija dobri.

## Osnovno Pravilo

Javne sekcije koriste jedan glavni container:

```html
<div class="cx-public-container">
    ...
</div>
```

Glavni container je `max-w-6xl`. Ne koristiti proizvoljne `max-w-*` vrijednosti za sekcijske wrappere bez jasne kompozicijske potrebe.

## Klase

| Uloga | Klasa | Koristi se za |
| --- | --- | --- |
| Glavni container | `cx-public-container` | standardni wrapper javnih sekcija i footera |
| Širi container | `cx-public-container-wide` | rijetke dashboard/showcase površine koje trebaju više širine |
| Article container | `cx-public-container-article` | single objave, proizvodi i duži tekstualni prikazi |
| Featured container | `cx-public-container-feature` | namjerno uži featured blokovi unutar sekcije |
| Uski container | `cx-public-container-narrow` | kratke statične stranice, poruke i fokusirani tekst |
| Standardni copy width | `cx-public-copy` | section header opis i uvodni tekst |
| Centrirani copy width | `cx-public-copy-centered` | centriran section header opis i uvodni tekst |
| Uži copy width | `cx-public-copy-narrow` | kraći opisi i pomoćni tekst |
| Centrirani uži copy width | `cx-public-copy-narrow-centered` | centrirani kraći opisi i pomoćni tekst |
| Kompaktni copy width | `cx-public-copy-compact` | male kartice i kraći tekstualni blokovi |
| Centrirani kompaktni copy width | `cx-public-copy-compact-centered` | centrirani mali tekstualni blokovi |
| Mobile bleed | `cx-public-mobile-bleed` | namjerna mobilna full-bleed slika ili hero kompozicija |
| Mobile bleed do md | `cx-public-mobile-bleed-md` | full-bleed element koji se resetira tek na `md` breakpointu |

## Standardne Širine

- Glavni section/footer container: `max-w-6xl`
- Namjerno uži featured blok: `max-w-5xl`
- Duži single/article prikaz: `max-w-4xl`
- Fokusirani tekstualni container: `max-w-3xl`
- Section header copy: `max-w-3xl`
- Kraći copy blok: `max-w-2xl`
- Kompaktni copy blok: `max-w-xl`

## Section Header

Naslov i opis sekcije ne smiju ovisiti o širini kartica ispod njih.

Section header copy koristi `cx-public-copy` ili postojeću header klasu koja primjenjuje isti `max-w-3xl` standard.

## Full-Bleed Iznimke

Full-bleed ili mobile bleed smije postojati samo kada je namjeran dio kompozicije:

- hero slika,
- velika image-led about sekcija,
- carousel koji treba rubni swipe osjećaj,
- mobilna slika koja treba doći do ruba ekrana.

Za mobilnu full-bleed iznimku koristiti `cx-public-mobile-bleed`, a ne ručno ponavljati `-mx-6 sm:mx-0`. Ako element treba ostati full-bleed do `md` breakpointa, koristiti `cx-public-mobile-bleed-md`.

Full-bleed ne koristiti za obične kartice, FAQ, partnere, statistike ili liste.

## Unutarnji Max Width

Unutarnji `max-w-*` smije postojati za:

- tekst koji mora ostati čitljiv,
- sliku koja ne smije biti prevelika,
- featured karticu ili video koji treba namjerno biti uži,
- modal ili carousel slide.

Unutarnji max-width ne smije mijenjati globalni container osjećaj sekcije.

## Checklist

Prije završetka javne sekcije ili layout varijante provjeriti:

- koristi li sekcija `cx-public-container`
- koristi li footer isti glavni container
- koristi li single/article prikaz `cx-public-container-article`
- koristi li section header copy širinu oko `max-w-3xl`
- postoje li proizvoljni `max-w-[...]` ili `max-w-7xl/5xl` bez jasne potrebe
- je li full-bleed iznimka namjerna i dokumentirana kroz klasu
- ne izlaze li kartice, slike ili headeri slučajno iz glavnog ritma stranice
