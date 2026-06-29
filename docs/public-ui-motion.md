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
- radi li sekcija mirno kada korisnik ima `prefers-reduced-motion`
