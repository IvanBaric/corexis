# Corexis Public CTA Standard

Ovaj standard definira hijerarhiju javnih CTA elemenata: glavne gumbe, sekundarne gumbe, CTA na tamnoj pozadini, tekstualne linkove i povratne linkove.

## Hijerarhija

Svaka sekcija smije imati najviše jedan primarni CTA. Sekundarni CTA koristi se samo kada postoji druga, manje važna akcija. Link koji vodi natrag ili otvara popis nije primarni CTA.

| Uloga | Klasa | Koristi se za |
| --- | --- | --- |
| Primarni CTA | `cx-public-button-primary` | glavna akcija sekcije ili stranice |
| Sekundarni CTA | `cx-public-button-secondary` | druga akcija na svijetloj pozadini |
| Primarni CTA na tamnoj pozadini | `cx-public-button-inverse` | glavna akcija na tamnom ili primarno obojenom bloku |
| Sekundarni CTA na tamnoj pozadini | `cx-public-button-inverse-secondary` | druga akcija na tamnom ili primarno obojenom bloku |
| Mirni gumb | `cx-public-button-ghost` | manje akcije koje ne smiju dominirati |
| Povratni link | `cx-public-back-link` | "Natrag", "Sve galerije", povratak na popis |
| Ikonski gumb | `cx-public-icon-button` | filter, alatne akcije i mali kružni gumbi |
| Floating ikonski gumb | `cx-public-floating-icon-button` | globalni floating gumb, npr. natrag na vrh |
| Tekstualni link | `cx-public-text-link` | obični link u tekstu |

## Tekst CTA Gumba

Tekst gumba treba biti kratak, glagolski i dosljedan.

- Koristiti obrazac: `Pogledaj ...`, `Saznaj više`, `Javite nam se`, `Pošalji upit`, `Prikaži više`.
- Ne miješati za istu akciju tekstove poput `Više`, `Saznaj`, `Pročitaj`, `Pogledaj` bez razloga.
- Povratni linkovi nisu CTA gumbi: koristiti `Natrag na ...` ili `Sve ...`.
- CTA tekst dolazi iz admina kada je sadržaj sekcije uredljiv; layout ne smije hardkodirati promotivni tekst koji korisnik ne može promijeniti.

## Pravila

- Ne koristiti različite radius, shadow ili hover stilove za gumbe u različitim sekcijama.
- CTA gumbi koriste `rounded-xl`, ne `rounded-full`, osim ako je riječ o ikonskom gumbu.
- CTA hover koristi isti mirni obrazac: `transition duration-200 hover:-translate-y-0.5 hover:shadow-md`.
- Tekstualni linkovi i povratni linkovi ne dobivaju underline na hover.
- Ako sekcija ima više layout varijanti, CTA položaj smije ovisiti o layoutu, ali stil CTA-a mora ostati isti.
- Ako CTA stoji na tamnom/primarnom bloku, koristiti inverse varijantu umjesto ručnog slaganja bijelog gumba.

## Checklist

Prije završetka javne sekcije provjeriti:

- postoji li samo jedan primarni CTA
- koristi li glavna akcija `cx-public-button-primary` ili `cx-public-button-inverse`
- koristi li druga akcija sekundarnu varijantu
- koriste li povratni linkovi `cx-public-back-link`
- koriste li filteri i alatne akcije `cx-public-icon-button`
- je li tekst gumba kratak i dosljedan
- dolazi li uredivi CTA tekst iz admina
