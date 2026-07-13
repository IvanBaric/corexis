# Corexis Public UI Surfaces Standard

Ovaj dokument definira standard za javne Tailwind površine, gumbe, radijuse, sjene i hover stanja u IvanBaric/Corexis ekosustavu.

Cilj nije da svaka sekcija izgleda kao ista kartica. Cilj je da iste vrste UI elemenata imaju isti osnovni osjećaj: isti radius, istu dubinu, isti hover i isti focus obrazac.

## Osnovno Pravilo

Vizualna varijanta smije mijenjati raspored, odnos slike i teksta, gustoću i ritam. Ne smije izmišljati novu dubinu, novi radius ili novi stil gumba za isti tip elementa.

Standard se nalazi u `resources/css/public-surfaces.css`. Host projekt ga uključuje jednom u glavni CSS:

```css
@import '../../packages/ivanbaric/corexis/resources/css/public-surfaces.css';
```

Ako se koristi instalirani Composer paket iz `vendor`, putanja može biti:

```css
@import '../../vendor/ivanbaric/corexis/resources/css/public-surfaces.css';
```

## Površine

| Uloga | Klasa | Koristi se za |
| --- | --- | --- |
| Standardna površina | `cx-public-surface` | kartice i blokovi kojima treba bijela pozadina, blagi obrub i standardna sjena |
| Mirna površina | `cx-public-surface-plain` | kartice bez obruba/ringa, kada layout treba tiši izgled |
| Muted površina | `cx-public-surface-muted` | prazna stanja, neutralni blokovi i nenametljive pozadine |
| Sekcijski band | `cx-public-surface-band` | pozadinski okvir za grupu sadržaja, npr. letter layout |
| Glass površina | `cx-public-surface-glass` | sadržaj preko fotografije ili tamnije pozadine |
| Media okvir | `cx-public-media-frame` | omot slike, videa ili vizualnog placeholdera |
| Media okvir sa sjenom | `cx-public-media-frame-surface` | samostalna slika ili media kartica kojoj treba standardna sjena |

Kartice i media frameovi koriste `rounded-xl`. Ne koristiti `rounded-2xl`, `rounded-3xl`, custom radius ili različite radiuse po layoutima iste sekcije.

`rounded-full` je dopušten za kružne ikone, avatare, male badge elemente i pill oznake. Ne koristiti ga za standardne javne CTA gumbe unutar sekcija.

## Public Empty States

Javne sekcije ne smiju imati vlastite ručno složene prazne blokove. Kada sekcija nema objavljiv sadržaj, koristiti Corexis komponentu:

```blade
<x-corexis::public-empty-state
    class="cx-public-section-content"
    icon="photo"
    :title="__('Fotografije uskoro')"
    :description="__('Fotografije će se prikazati ovdje kada budu spremne za objavu.')"
/>
```

Komponenta koristi `cx-public-empty-state`, `cx-public-empty-state-icon`, `cx-public-empty-state-title` i `cx-public-empty-state-description` iz `public-surfaces.css`.

Tekst mora biti neutralan za javnog posjetitelja. Ne pisati administrativne upute poput "Dodajte fotografije" ili "Dodajte logo" na javnom frontendu. Ako je potrebno uputiti korisnika što treba napraviti, ta poruka pripada admin sučelju, ne javnoj stranici.

Ikona mora biti Flux/Heroicons naziv i treba odgovarati tipu sadržaja, npr. `photo` za galeriju, `cube` za radove, `newspaper` za objave, `calendar-days` za događaje.

## Sjene

Standardna mirna sjena je:

```txt
shadow-sm shadow-zinc-950/5
```

Dark mode standard je:

```txt
dark:shadow-black/20
```

Interaktivni hover smije pojačati sjenu na:

```txt
hover:shadow-md hover:shadow-zinc-950/10
```

Ne koristiti `shadow-lg`, `shadow-2xl` ili custom `shadow-[...]` za obične kartice i sekcijske blokove. Te sjene su dopuštene samo za hero, modal ili namjerno izdvojeni floating element, i tada moraju biti dokumentirana iznimka.

## Interaktivnost

Za klikabilne kartice i redove koristi se:

```html
class="cx-public-surface cx-public-interactive"
```

`cx-public-card-hover` je nova semantička motion klasa za isti obrazac, a `cx-public-interactive` ostaje kompatibilan alias.

Ako element već ima dodatne layout klase, dodaju se uz semantičku klasu:

```html
<article class="group flex h-full flex-col cx-public-surface p-5 cx-public-interactive">
```

Hover pomak je uvijek `hover:-translate-y-0.5`. Ne miješati `-translate-y-1` i druge vrijednosti na običnim karticama.

## Gumbi

Javne sekcije koriste tri osnovna CTA stila:

| Uloga | Klasa | Koristi se za |
| --- | --- | --- |
| Primarni gumb | `cx-public-button-primary` | glavna akcija sekcije |
| Sekundarni gumb | `cx-public-button-secondary` | mirnija akcija na svijetloj pozadini |
| Inverzni gumb | `cx-public-button-inverse` | CTA na tamnoj ili primarno obojenoj pozadini |
| Sekundarni inverzni gumb | `cx-public-button-inverse-secondary` | druga akcija na tamnoj ili primarno obojenoj pozadini |
| Ghost gumb | `cx-public-button-ghost` | mirne pomoćne akcije |
| Povratni link | `cx-public-back-link` | povratak na popis ili prethodnu stranicu |
| Ikonski gumb | `cx-public-icon-button` | filteri i male alatne akcije |
| Floating ikonski gumb | `cx-public-floating-icon-button` | globalni floating gumbi |

Svi standardni CTA gumbi imaju isti radius, istu sjenu, isti hover i isti focus obrazac. Varira samo boja prema kontekstu.

Detaljna CTA hijerarhija i pravila teksta opisana su u `docs/public-ui-cta.md`.

Za obične tekstualne linkove koristi se:

```html
class="cx-public-text-link"
```

Tekstualni linkovi ne dobivaju underline na hover.

## Implementacijski Checklist

Prije završetka nove javne sekcije ili layout varijante provjeriti:

- koristi li kartica jednu od `cx-public-surface*` klasa
- koristi li klikabilna kartica isti `hover:-translate-y-0.5` i `hover:shadow-md`
- koriste li CTA gumbi `cx-public-button-primary`, `cx-public-button-secondary` ili `cx-public-button-inverse`
- postoje li `rounded-2xl`, `rounded-3xl`, custom radius ili više radiusa za istu vrstu elementa
- postoje li `shadow-lg`, `shadow-2xl` ili custom sjene na običnim karticama
- koriste li slike i media okviri isti `rounded-xl`
- jesu li `rounded-full` elementi stvarno kružne ikone, avatari ili badge oznake
- koristi li prazna javna sekcija `x-corexis::public-empty-state` umjesto ručnog markup-a

Ako novi projekt treba drugačiji radius ili dubinu, prvo promijeniti Corexis standard, a tek zatim sekcije.
