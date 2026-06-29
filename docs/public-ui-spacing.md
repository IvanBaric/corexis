# Corexis Public Spacing Standard

Ovaj standard definira osnovni ritam javnih sekcija. Cilj je da svaka stranica djeluje mirno, profesionalno i dosljedno, bez ručnog ugađanja istih razmaka u svakoj sekciji.

## Sekcijski header

- `cx-public-section-header` koristi se za omot naslova, opisa i akcija sekcije.
- `cx-public-section-header-copy` koristi se za naslovni tekst sekcije.
- `cx-public-section-header-actions` koristi se za filtere, gumbe i pomoćne akcije u headeru.

Naslov sekcije i opis sekcije ostaju odvojeni od sadržaja sekcije. Ne stavljati ih u kartice, liste ili pojedinačne zapise.

## Razmak nakon headera

- `cx-public-section-content` je zadani razmak između headera sekcije i sadržaja.
- `cx-public-section-content-spacious` koristi se za veće, editorial ili image-led layoute.
- `cx-public-section-content-compact` koristi se za sekundarne blokove unutar iste sekcije.

Ne koristiti ručno `mt-10`, `mt-12` ili `mt-8` za glavne sekcijske prijelaze. Ako treba promjena ritma, prvo dodati ili promijeniti Corexis klasu.

## Grid ritam

- `cx-public-grid` je zadani grid razmak.
- `cx-public-grid-compact` koristi se za gušće kartice, liste i manje zapise.
- `cx-public-grid-tight` koristi se za vrlo kompaktne informacijske redove.
- `cx-public-grid-loose` koristi se za veće kartice ili layout s više zraka.

Responsive stupci ostaju u Tailwind klasama, npr. `cx-public-grid sm:grid-cols-2 lg:grid-cols-3`.

## Stack ritam

- `cx-public-stack` je zadani vertikalni ritam liste.
- `cx-public-stack-compact` koristi se za FAQ i kompaktne accordion/list blokove.
- `cx-public-stack-loose` koristi se za opuštenije liste.
- `cx-public-stack-editorial` koristi se za izmjenične/editorial blokove.
- `cx-public-stack-showcase` koristi se za velike image-led ili showcase blokove.

## Padding

- `cx-public-card-padding` je zadani padding kartice.
- `cx-public-card-padding-compact` koristi se za manje kartice i redove.
- `cx-public-card-padding-loose` koristi se za istaknutije kartice.
- `cx-public-band-padding` koristi se za mirne bandove koji sadrže više kartica.
- `cx-public-band-padding-loose` koristi se za veće bandove ili kontakt/featured blokove.

Padding ikona, pill oznaka i sitnih meta elemenata može ostati lokalni Tailwind jer ne definira sekcijski ritam.

## Pravilo održavanja

Ako isti razmak treba mijenjati na više sekcija ili layout varijanti, promjena pripada u Corexis spacing standard. Lokalni Tailwind razmak koristiti samo kada je vezan uz specifičnu kompoziciju jednog layouta.
