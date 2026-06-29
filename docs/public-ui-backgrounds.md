# Corexis Public Background Standard

Ovaj standard definira ograničen broj pozadinskih površina za javne stranice i sekcije.

Premium dojam ne traži više pozadina, nego jasnije pravilo kada se koja koristi. Sekcije mogu mijenjati layout, ali ne smiju uvoditi novu pozadinsku boju za svaki layout.

## Klase

| Uloga | Klasa | Koristi se za |
| --- | --- | --- |
| Osnovna stranica | `cx-public-page-bg` | standardna javna stranica |
| Neutralna stranica | `cx-public-page-bg-muted` | forme, status stranice i administrativniji javni tokovi |
| Topla stranica | `cx-public-page-bg-warm` | landing ili editorial stranice koje trebaju mekši ton |
| Topla prozirna navigacija | `cx-public-page-bg-warm-translucent` | sticky header preko tople landing pozadine |
| Standardna sekcija | `cx-public-section-bg` | većina javnih sekcija |
| Muted sekcija | `cx-public-section-bg-muted` | sekcije kojima treba mirno odvajanje od bijele pozadine |
| Topla sekcija | `cx-public-section-bg-warm` | story/editorial sekcije, misija, vizija, tim |
| Inverzna sekcija | `cx-public-section-bg-inverse` | rijetki tamni CTA ili hero blokovi |
| Standardni panel | `cx-public-panel-bg` | unutarnji panel ili manji blok |
| Muted panel | `cx-public-panel-bg-muted` | prazna stanja i tihi paneli |
| Topli panel | `cx-public-panel-bg-warm` | mali editorial panel na toploj stranici |

## Pravila

- Glavni ritam stranice koristi najviše tri sekcijske pozadine: `standard`, `muted` i povremeno `warm`.
- `inverse` koristiti rijetko, za stvarno istaknut CTA ili hero trenutak.
- Ne dodavati dekorativne gradijente, posebne boje ili nove hex vrijednosti unutar pojedinačne sekcije bez promjene standarda.
- Kartice ne trebaju izmišljati novu pozadinu; koriste `cx-public-surface*`.
- Warm pozadina služi za editorial toplinu, ne za svaku drugu sekciju.
- Muted pozadina služi za ritam i odvajanje, ne za “ukrašavanje”.

## Checklist

Prije završetka nove javne sekcije provjeriti:

- koristi li wrapper jednu od `cx-public-section-bg*` klasa
- koristi li stranica jednu od `cx-public-page-bg*` klasa
- postoji li nova ručna boja poput `bg-[#...]` i je li stvarno treba dodati u standard
- je li `inverse` pozadina korištena samo za istaknuti trenutak
- jesu li kartice i paneli odvojeni kroz surface/panel klase, a ne kroz nasumične pozadine
