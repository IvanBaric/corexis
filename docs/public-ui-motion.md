# Corexis Public Motion Standard

Ovaj standard definira animacije za javne stranice i sekcije.

Niva koristi miran motion jezik: suptilno pojavljivanje, lagani hover na klikabilnoj kartici i blagi zoom slike na hover. Ne koristiti agresivne pomake, jake bounce efekte, rotacije ili različita trajanja bez jasnog layout razloga.

## Klase

| Uloga | Klasa | Koristi se za |
| --- | --- | --- |
| Page enter | `cx-public-page-enter` | wrapper javnog sadržaja koji smije lagano ući na stranicu |
| Scroll reveal | `data-scroll-reveal` unutar `cx-public-page-enter` | sekcije koje se suptilno pojavljuju |
| Hover kartice | `cx-public-card-hover` | klikabilne kartice, zapisi i redovi |
| Legacy hover alias | `cx-public-interactive` | isti obrazac kao `cx-public-card-hover` |
| Zoom slike | `cx-public-image-zoom` | slika unutar klikabilne kartice ili zapisa |
| Legacy image alias | `cx-public-image-hover` | isti obrazac kao `cx-public-image-zoom` |
| Boja/link transition | `cx-public-motion-color` | tekstualni linkovi, naslovi i mirne promjene boje |
| Fade transition | `cx-public-motion-fade` | loading, badge i kratka opacity stanja |
| Ikonski transition | `cx-public-motion-icon` | chevroni, strelice i male ikone |

## Standardne Vrijednosti

- Page enter: `420ms ease-out`, `translateY(14px)` do `0`
- Scroll reveal: `520ms ease-out`, `translateY(10px)` do `0`
- Kartica hover: `duration-200 hover:-translate-y-0.5 hover:shadow-md`
- Slika hover: `duration-500 group-hover:scale-[1.03]`
- Link/icon transition: `duration-200`

## Javni Loader Stranice

Za javne tenant stranice preporučuje se profesionalni početni loader kada stranica ima veći vizualni ulaz: javni header, media sadržaj, tenant temu, Livewire komponente ili više sadržajnih sekcija koje se učitavaju zajedno.

Loader je vizualni overlay, ne blokada učitavanja. Stranica se mora normalno učitavati u pozadini, a loader samo prekriva sadržaj dok se prvi prikaz smiri.

Standardni obrazac:

- prikazati loader samo pri prvom otvaranju javne stranice za određeni tenant u istoj browser sesiji
- koristiti `sessionStorage` ključ vezan uz tenant slug ili uuid, npr. `corexis-public-loader:{tenant}`
- minimalno trajanje je `2000ms`
- ako se stranica učita brže, loader ostaje do isteka minimalnog trajanja
- ako se stranica učitava sporije, loader se skriva nakon `load` eventa, uz fallback timer
- ne prikazivati loader na svakoj internoj navigaciji, povratku na naslovnicu ili svakoj podstranici u istoj sesiji
- poštovati `prefers-reduced-motion`; spinner/progress animacije tada moraju stati ili se svesti na statično stanje
- koristiti kratak tekst statusa i tenant naziv/logo kada postoje
- dodati `role="status"`, `aria-live="polite"` i `aria-busy="true"` dok loader traje

Vizualni stil treba biti miran: neutralna svijetla pozadina, tenant akcent kao detalj, logo ili jednostavna ikona u sredini, suptilan spinner i kratka progress linija. Ne koristiti agresivne fullscreen animacije, tamne blokade, skakanje elemenata ili duge marketinške poruke.

Ovaj loader nije zamjena za lokalna loading stanja u adminu, listama, formama ili gumbima. Za te slučajeve koristiti ciljane Livewire/Flux loading indikatore.

## Pravila

- Klikabilne kartice koriste `cx-public-card-hover` ili postojeći alias `cx-public-interactive`.
- Slika zumira samo ako je dio klikabilne kartice ili zapisa.
- Statični informativni blokovi ne trebaju hover animaciju.
- Accordion animacije su kratke i mirne: `x-transition.opacity.duration.150ms`.
- Rotacije koristiti samo kada je layout namjerno zamišljen kao bilješka, papir ili kreativna ploča.
- Ne miješati `hover:-translate-y-1`, `scale-[1.04]`, `duration-700` i slične vrijednosti s osnovnim sustavom.
- Poštovati `prefers-reduced-motion`; Corexis klase to rade automatski.

## Checklist

Prije završetka javne sekcije provjeriti:

- koristi li klikabilna kartica `cx-public-card-hover` ili `cx-public-interactive`
- koristi li klikabilna slika `cx-public-image-zoom` ili `cx-public-image-hover`
- jesu li hover pomak i sjena isti kroz layout varijante
- jesu li accordion prijelazi kratki i jednaki
- nema li agresivnih animacija, bounce efekata ili velikih zoomova
- koristi li javna tenant stranica početni loader kada ima kompleksan vizualni ulaz
- prikazuje li se javni loader samo prvi put po tenant browser sesiji i traje li minimalno 2 sekunde
- radi li sekcija mirno kada korisnik ima `prefers-reduced-motion`
