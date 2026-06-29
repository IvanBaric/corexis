# Corexis Public Icon Standard

Ovaj standard definira kako javne stranice koriste UI ikone, akcijske ikone, ikone u karticama i iznimke poput social brand ikona.

Premium dojam najviše dolazi iz toga da ikone djeluju kao jedna obitelj: ista debljina linije, isti radius okvira, isti odnos prema tekstu i ista semantika kroz sve sekcije.

## Osnovno Pravilo

Za javni UI koristiti `flux:icon`, odnosno istu ikonografsku obitelj koju koristi Flux UI. Ne miješati Flux/Heroicons stil s Lucide, Font Awesome, emoji ikonama, custom filled ikonama ili dekorativnim SVG ikonama unutar iste javne stranice.

Ikona se dodaje samo kada pomaže skeniranju, značenju ili interakciji. Ne dodavati ikone kao dekoraciju samo zato što kartica ima prostora.

## Klase

| Uloga | Klasa | Koristi se za |
| --- | --- | --- |
| Standardna ikona | `cx-public-icon` | obična ikona u kartici ili meti |
| Mala ikona | `cx-public-icon-sm` | linkovi, meta redovi, male akcije |
| Veća ikona | `cx-public-icon-lg` | istaknute informacije i prazna stanja |
| Velika ikona | `cx-public-icon-xl` | placeholder slike ili velike vizualne oznake |
| Okvir ikone | `cx-public-icon-frame` | kružni primarni okvir ikone |
| Mali okvir | `cx-public-icon-frame-sm` | kompaktne kartice |
| Srednji okvir | `cx-public-icon-frame-md` | zadani icon badge |
| Veliki okvir | `cx-public-icon-frame-lg` | istaknute kartice |
| Extra veliki okvir | `cx-public-icon-frame-xl` | hero/featured kartice |
| Akcijska ikona | `cx-public-icon-action` | strelice, chevroni, close ikone |
| Mirna ikona | `cx-public-icon-muted` | pomoćne ikone koje mijenjaju boju na hover grupe |

Primjer:

```blade
<span class="cx-public-icon-frame cx-public-icon-frame-md">
    <flux:icon name="sparkles" class="cx-public-icon" />
</span>
```

## Veličine

Zadane veličine:

- `size-4` za akcije, strelice, chevrone i male meta ikone
- `size-5` za standardne kartice i informacijske blokove
- `size-6` za malo istaknutije ikone
- `size-9` ili `size-10` samo za velike placeholder/empty-state vizuale

Ne miješati puno veličina unutar iste layout varijante. Ako jedna kartica koristi `size-5`, usporedive kartice u istoj sekciji trebaju koristiti istu veličinu.

## Okviri

Ikone koje stoje kao vizualna oznaka kartice smiju imati kružni okvir:

```blade
<span class="cx-public-icon-frame cx-public-icon-frame-lg">
    <flux:icon name="heart" class="cx-public-icon" />
</span>
```

Okvir ikone koristi `rounded-full`, mirnu primarnu pozadinu i tanki ring. Ne uvoditi kvadratne, jako obojene ili različito zaobljene okvire po layoutima iste sekcije, osim ako je to jasno dio posebnog layouta.

## Akcijske Ikone

Strelice, chevroni, close ikone, play ikone i loading ikone moraju biti funkcionalne, ne dekorativne.

Za link strelice koristiti `arrow-right` ili `arrow-down` iz Flux ikonografije. Za accordion koristiti `chevron-down` s jednostavnom rotacijom `rotate-180`. Za zatvaranje koristiti `x-mark`.

Ne koristiti emoji strelice, tekstualne znakove poput `→`, `×` ili custom SVG za standardne akcije ako postoji Flux ikona.

## Brand Ikone

Social i brand ikone su dopuštena iznimka. One smiju biti inline SVG ili brand-specific path, jer Flux/Heroicons nema službene brand ikone.

Brand ikone ne koristiti kao zamjenu za UI ikone. UI akcije i sadržajne kartice i dalje koriste `flux:icon`.

## Admin Sadržaj

Ako sekcijska stavka ima ikonicu u adminu, javni layout treba ju prikazati u svakom izgledu gdje to ne narušava dizajn.

Vrijednost ikone iz admina mora biti naziv Flux ikone. Ne spremati emoji, HTML, SVG path ili naziv druge biblioteke u polje ikone.

Ako korisnik ne unese ikonu, layout može koristiti miran fallback iz iste obitelji, npr. `sparkles`, `heart`, `photo`, `cube` ili `question-mark-circle`, ovisno o tipu sadržaja.

## Checklist

Prije završetka javne sekcije ili layout varijante provjeriti:

- koriste li UI ikone `flux:icon`
- nema li emoji ikona ili tekstualnih simbola za akcije
- nema li miješanja Lucide, Font Awesome, inline UI SVG i Flux ikona
- jesu li ikone usporedivih kartica iste veličine
- koristi li ikona okvir samo kada pomaže hijerarhiji
- jesu li brand/social SVG ikone ograničene na social/brand kontekst
- dolaze li admin ikone iz istog Flux seta
- prikazuju li svi layouti iste sekcije admin ikonu gdje je to dizajnerski smisleno
