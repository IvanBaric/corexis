# Corexis Public Border Standard

Ovaj standard definira obrube, ringove, dividers i timeline linije na javnim stranicama.

Premium dojam traži da rubovi budu suptilni i dosljedni. Kartice, slike, FAQ, partneri, timeline, inputi i mali UI elementi ne smiju imati različite debljine i agresivne nijanse bez razloga.

## Osnovno Pravilo

Za vizualni rub kartice, slike, media okvira i badgea preferirati `ring-1`. Ring daje miran optički obrub koji ne mijenja layout i bolje se ponaša uz sjene.

Za stvarne separatore, timeline linije i rubove koji dijele sadržaj koristiti `border` ili `divide`.

Ne koristiti `border-2`, jake crne rubove, custom ring vrijednosti ili miješanje `border`, `ring`, `outline` i sjene za isti tip elementa.

## Klase

| Uloga | Klasa | Koristi se za |
| --- | --- | --- |
| Standardni optički rub | `cx-public-border` | kartice, logo okviri, media okviri, partneri |
| Muted optički rub | `cx-public-border-muted` | prazna stanja, vrlo tihi blokovi, floating elementi |
| Primarni optički rub | `cx-public-border-primary` | badgevi, icon okviri, istaknute male oznake |
| Jači primarni rub | `cx-public-border-primary-strong` | statement blokovi i blago istaknuti elementi |
| Inverse rub | `cx-public-border-inverse` | glass elementi i sadržaj preko slike |
| Standardni separator | `cx-public-divider` | `border-t`, `border-b`, `border-l` separatori |
| Tihi separator | `cx-public-divider-muted` | unutarnji razdjelnici u karticama |
| Divide separator | `cx-public-divide` | liste koje koriste `divide-y` ili `divide-x` |
| Timeline linija | `cx-public-timeline-border` | timeline, vertikalni procesi, kronologija |
| Timeline točka | `cx-public-timeline-dot` | točka na timeline liniji |

## Standardne Vrijednosti

Standardni optički rub:

```txt
ring-1 ring-zinc-200/70 dark:ring-zinc-800
```

Muted optički rub:

```txt
ring-1 ring-zinc-950/5 dark:ring-white/10
```

Primarni optički rub:

```txt
ring-1 ring-[color:var(--niva-primary-100)] dark:ring-[color:var(--niva-primary-900)]
```

Standardni separator:

```txt
border-zinc-200/70 dark:border-zinc-800
```

## Kada Koristiti Ring

Koristiti `ring-1` za:

- kartice i površine
- media okvire
- logo okvire partnera
- badge/pill elemente
- icon okvire
- glass elemente preko slike

Ring mora biti tanak i suptilan. Ne koristiti ring kao dekoraciju koja se natječe s tekstom ili slikom.

## Kada Koristiti Border

Koristiti `border` za:

- `border-t`, `border-b`, `border-l` separatore
- timeline liniju
- tablične ili list sekcije koje stvarno razdvajaju redove
- inpute ako framework komponenta očekuje border

Border separator ne smije biti crn ili prejak. Zadani separator je `cx-public-divider`.

## Fokus Stanja

Focus ring je interaktivno stanje, nije dekorativni border. Za javne interaktivne elemente koristiti postojeći `cx-public-focus` ili standardni focus obrazac iz button/surface klasa.

Ne miješati obični border hover i focus ring ako element već ima jasan focus stil.

## Iznimke

`ring-4` je dopušten samo za male timeline točke ili element koji mora pokriti podlogu ispod sebe. Ne koristiti `ring-4` za kartice, slike, FAQ ili partnere.

Tamni/inverse rubovi poput `ring-white/70` dopušteni su za glass elemente i sadržaj preko slike.

## Checklist

Prije završetka javne sekcije ili layout varijante provjeriti:

- koriste li kartice i media okviri `ring-1`, a ne proizvoljni `border`
- koristi li partner/logo okvir isti `cx-public-border`
- koriste li timeline linije `cx-public-timeline-border`
- koriste li timeline točke `cx-public-timeline-dot`
- nema li `border-2`, tamnih crnih rubova ili custom ringova na običnim karticama
- ne miješaju li se `ring-zinc-200`, `ring-zinc-950/5` i `border-zinc-200` za istu vrstu elementa
- jesu li dividers tihi i dosljedni
- je li focus ring odvojen od dekorativnog bordera
