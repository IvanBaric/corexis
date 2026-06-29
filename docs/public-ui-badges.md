# Corexis Public Badge Standard

Ovaj standard definira badge, pill, tag, status i kratke oznake na javnim stranicama.

Badge sustav mora biti miran i dosljedan jer se pojavljuje u kategorijama, statusima, tipovima aktivnosti, proizvodima, događajima, galerijama i filtrima. Ako svaka sekcija sama izmišlja veličinu, border, pozadinu i tekst, stranica brzo izgubi premium dojam.

## Osnovno Pravilo

Badge je kratka oznaka koja pomaže skeniranju sadržaja. Ne koristiti badge za duge rečenice, CTA gumbe ili obične opise.

Zadani oblik badgea je `rounded-full`, mali font, mirna primarna nijansa i tanki ring. Ne miješati pune, outline, neutralne i overlay badgeve bez jasne semantike.

## Klase

| Uloga | Klasa | Koristi se za |
| --- | --- | --- |
| Standardni badge | `cx-public-badge` | kategorije, tipovi aktivnosti, aktivni filteri, broj medija |
| Mali badge | `cx-public-badge-sm` | kompaktne oznake unutar kartica |
| Muted badge | `cx-public-badge-muted` | neutralni loading/status pill ili pomoćna oznaka |
| Mali muted badge | `cx-public-badge-muted-sm` | neutralni kompaktni step label ili sekundarna oznaka |
| Inverse badge | `cx-public-badge-inverse` | badge preko slike ili tamnijeg vizuala |
| Solid badge | `cx-public-badge-solid` | rijetko, samo za izrazito aktivno/stanje koje mora biti naglašeno |
| Badge akcija | `cx-public-badge-action` | klikabilni badge koji mijenja stanje/filter |
| Zatvaranje badgea | `cx-public-badge-close` | mali `x` gumb unutar aktivnog filter badgea |

Primjer aktivnog filtera:

```blade
<span class="cx-public-badge">
    <span class="truncate">Kategorija: Novosti</span>
    <button type="button" class="cx-public-badge-close" aria-label="Ukloni filter">
        <flux:icon name="x-mark" class="size-3.5" />
    </button>
</span>
```

Primjer badgea preko slike:

```blade
<span class="cx-public-badge-inverse">12 fotografija</span>
```

## Kada Koristiti Badge

Koristiti badge za:

- kategoriju objave ili proizvoda
- aktivni filter
- status ili kratku sistemsku oznaku
- broj fotografija, radova ili zapisa
- tip aktivnosti ili događaja
- kratku oznaku poput “Novo”, “Istaknuto” ili “Uskoro”

Ne koristiti badge za:

- duži opis sekcije
- CTA gumb
- običan link u tekstu
- datum koji već ima svoj date-card prikaz
- cijenu koja treba biti čitljiva kao podatak, ne kao tag

## Veličina I Ritam

Standardni badge koristi `px-3 py-1.5`. Mali badge koristi `px-2.5 py-1`.

Ako se badgevi ponavljaju u istoj sekciji, svi usporedivi badgevi moraju imati istu veličinu i isti stil. Ne koristiti u istoj sekciji istovremeno veliki primary badge, mali outline badge i neutralni tekstualni badge za istu vrstu podatka.

Badge tekst koristi `text-xs font-semibold leading-4`, osim muted statusa koji može koristiti `text-sm leading-5` kada nosi pomoćnu sistemsku poruku.

## Boje

Zadani badge koristi mirnu primarnu nijansu:

- light: `--niva-primary-50`, tekst `--niva-primary-700`, ring `--niva-primary-100`
- dark: `--niva-primary-950`, tekst `--niva-primary-300`, ring `--niva-primary-900`

Inverse badge koristiti samo preko slike ili tamnijeg vizualnog sloja.

Solid badge koristiti rijetko. Ako svaki badge postane pun i glasan, hijerarhija nestaje.

## Interaktivnost

Klikabilni badge mora imati `cursor-pointer` i miran hover. Ne dodavati pomak, zoom ili jaku sjenu na obične tagove/filtere.

Za uklanjanje aktivnog filtera koristiti mali unutarnji gumb s `cx-public-badge-close`.

## Checklist

Prije završetka javne sekcije ili layout varijante provjeriti:

- koristi li badge jednu od `cx-public-badge*` klasa
- koristi li ista vrsta oznake isti stil u svim layoutima sekcije
- jesu li kategorije/statusi/tipovi kratki i skenirljivi
- koristi li badge `rounded-full`, a ne proizvoljni radius
- nije li badge zamijenio CTA gumb ili duži opis
- koristi li overlay badge `cx-public-badge-inverse`
- koristi li aktivni filter `cx-public-badge` i `cx-public-badge-close`
- nema li miješanja full/outline/muted stilova za isti tip podatka
