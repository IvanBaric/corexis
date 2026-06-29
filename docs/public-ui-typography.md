# Corexis Public UI Typography Standard

Ovaj dokument definira tipografski standard za javne Tailwind layout sekcije u IvanBaric/Corexis ekosustavu.

Cilj nije da svaka stranica izgleda isto. Cilj je da sve sekcije koriste isti mali broj semantičkih uloga teksta, tako da layout varijante mogu mijenjati raspored, slike, grid i ritam, ali ne izmišljaju novu tipografsku skalu za isti tip sadržaja.

## Osnovno Pravilo

Veličina teksta bira se prema ulozi teksta, ne prema pojedinom layoutu.

Ne uvoditi novu Tailwind veličinu samo zato što kartica izgleda prazno, layout ima više prostora ili sekcija treba djelovati "posebnije". Nova veličina je dopuštena samo kada postoji nova semantička uloga teksta.

Layout varijante iste vrste sekcije moraju koristiti iste tipografske uloge:

- naslov sekcije ostaje naslov sekcije
- opis sekcije ostaje opis sekcije
- naslov stavke ostaje naslov stavke
- opis stavke ostaje opis stavke
- meta podatak ostaje meta podatak

## Tipografske Uloge

Corexis isporučuje CSS klase u `resources/css/public-typography.css`. Host projekt ih uključuje jednom u glavni CSS:

```css
@import '../../packages/ivanbaric/corexis/resources/css/public-typography.css';
```

Ako se koristi instalirani Composer paket iz `vendor`, putanja može biti:

```css
@import '../../vendor/ivanbaric/corexis/resources/css/public-typography.css';
```

| Uloga | Tailwind standard | Koristi se za |
| --- | --- | --- |
| Hero naslov | `cx-public-hero-title` | glavni naslov hero sekcije |
| Hero opis | `cx-public-hero-description` | podnaslov ili kratki uvod u hero sekciji |
| Eyebrow oznaka | `cx-public-eyebrow` | kratka oznaka iznad hero ili section naslova |
| Naslov sekcije | `cx-public-section-title` | javni naslov svake sadržajne sekcije |
| Opis sekcije | `cx-public-section-description` | kratki tekst ispod naslova sekcije |
| Istaknuti sadržajni naslov | `cx-public-featured-title` | featured objava, CTA naslov ili posebno istaknuti sadržajni blok |
| Naslov stavke | `cx-public-item-title` | standardni naslov kartice, reda, partnera, rada, objave ili FAQ pitanja |
| Kompaktni naslov stavke | `cx-public-item-title-compact` | male kartice, feature grid, galerijski naslov, kompaktne liste |
| Tekst stavke | `cx-public-item-text` | opis u kartici, listi, galeriji ili kompaktnoj stavci |
| Obični sadržajni tekst | `cx-public-body` | FAQ odgovor, kontakt tekst, sadržajni blok, standardni odlomak |
| Lead tekst | `cx-public-lead` | uvodni odlomak u "o nama", CTA ili važnom editorial bloku |
| Meta tekst | `cx-public-meta` | datum, lokacija, cijena, vrijeme, broj fotografija, kategorija |
| Naglašeni meta tekst | `cx-public-meta-strong` | meta podatak koji treba biti čitljiviji, npr. vrijeme, kategorija ili akcijski tekst |
| Sitni pomoćni tekst | `cx-public-small` | uloga osobe, sekundarna oznaka, vrlo kratka pomoćna informacija |
| Naglašeni sitni tekst | `cx-public-small-strong` | kratke oznake, labeli i badge tekstovi |
| CTA tekst | `cx-public-cta` | gumbi i akcijski tekstualni linkovi |
| Velika statistika | `cx-public-stat-value` | primarni broj u statističkoj kartici ili većem metričkom bloku |
| Srednja statistika | `cx-public-stat-value-medium` | standardni broj u statističkoj kartici |
| Kompaktna statistika | `cx-public-stat-value-compact` | broj u uskoj statističkoj traci ili listi |
| Mjesec datuma | `cx-public-date-month` | kratka oznaka mjeseca u kalendarskoj kartici |
| Dan datuma | `cx-public-date-day` | broj dana u kalendarskoj kartici |
| Citat | `cx-public-quote` | standardni testimonial ili citat |
| Kompaktni citat | `cx-public-quote-compact` | citat u manjim testimonial karticama |
| Istaknuti citat | `cx-public-quote-featured` | veći citat u spotlight testimonial layoutu |
| Oznaka citata | `cx-public-quote-mark` | dekorativni navodnik u testimonial layoutu |
| Inicijal avatara | `cx-public-avatar-initial` | fallback inicijal u kružnom avataru |

Klase sadrže samo tipografsku ulogu: veličinu, debljinu i line-height. Boja, spacing, hover i layout ostaju odgovornost konkretnog layouta.

## Pravila Primjene

`text-lg` nije zadani tekst za opise u karticama. Koristi se za lead tekst i važnije uvodne odlomke. Opisi stavki u karticama, listama i galerijama koriste `text-sm leading-6`, osim kada je sadržaj namjerno predstavljen kao duži editorial blok.

`text-2xl` nije obični naslov kartice. Koristi se samo za istaknuti sadržaj, npr. featured objavu, CTA naslov ili veći editorial blok. Ako layout nema jasnu featured hijerarhiju, koristi `text-lg`.

`text-xs` ne koristiti za ključan sadržaj koji korisnik mora lako pročitati. Dopušten je za pomoćne oznake, uloge, brojeve fotografija, kratke meta informacije i sličan sekundarni tekst.

`text-sm` je standard za rutinski kartični opis i meta tekst. Razlika između opisa i meta teksta dolazi iz `leading`, boje, težine fonta i pozicije, ne iz stalnog dodavanja novih veličina.

Naslovi sekcija i opisi sekcija moraju biti odvojeni od sadržaja sekcije. Ne stavljati naslov sekcije u karticu, red ili pojedinačni zapis.

## Sekcije I Layout Varijante

Svaka sekcija koja ima više javnih layout varijanti mora mapirati svoje tekstove na iste uloge.

Primjer za objave:

- naslov sekcije: `Naslov sekcije`
- opis sekcije: `Opis sekcije`
- featured naslov objave: `Istaknuti sadržajni naslov`
- obični naslov objave: `Naslov stavke`
- opis objave: `Tekst stavke`
- datum i kategorija: `Meta tekst`
- gumb ili link: `CTA tekst`

Primjer za galerije:

- naslov sekcije: `Naslov sekcije`
- opis sekcije: `Opis sekcije`
- naslov galerije: `Kompaktni naslov stavke` ili `Naslov stavke`, ovisno o gustoći layouta
- opis galerije: `Tekst stavke`
- broj fotografija: `Meta tekst`

Primjer za FAQ:

- naslov sekcije: `Naslov sekcije`
- opis sekcije: `Opis sekcije`
- pitanje: `Naslov stavke` ili `Kompaktni naslov stavke`
- odgovor: `Obični sadržajni tekst`

Ako jedna layout varijanta iste sekcije koristi `text-lg` za naslov stavke, usporedive varijante iste sekcije ne smiju koristiti `text-xl` ili `text-2xl` za istu ulogu bez jasnog featured stanja.

## Dopuštene Iznimke

Hero sekcija smije imati vlastitu veću skalu jer je prvi vizualni signal stranice.

Featured kartica smije imati veći naslov od običnih kartica ako je istaknuti odnos dio strukture sadržaja, ne samo dekoracija.

Editorial blokovi poput "O nama", uvodnog sadržaja ili CTA sekcije smiju koristiti `Lead tekst` za jedan kratak uvodni odlomak. Ne koristiti lead stil za sve odlomke u sekciji.

Statistički brojevi, cijene ili velike metrike smiju imati vlastitu vizualnu skalu kada je broj primarni sadržaj stavke.

## Implementacijski Checklist

Prije završetka nove javne sekcije ili nove layout varijante provjeriti:

- koristi li svaki tekst jednu od definiranih tipografskih uloga
- imaju li sve varijante iste sekcije istu skalu za iste uloge
- postoje li opisi kartica koji nepotrebno koriste `text-lg`
- postoji li obični naslov kartice koji nepotrebno koristi `text-2xl`
- jesu li section naslov i section opis izvan kartica i pojedinačnih zapisa
- jesu li meta tekstovi čitljivi, ali ne jači od naslova i opisa
- je li nova veličina teksta stvarno nova semantička uloga

Ako se pojavi potreba za novom veličinom, prvo ažurirati ovaj dokument s novom ulogom i obrazloženjem. Tek nakon toga koristiti novu veličinu u layoutima.
