# Niva Project Profile

Ovaj dokument sadrži pravila specifična za Niva host aplikaciju, njezin monorepo, Forge deployment i zasebne GitHub package repozitorije.

## Korištenje paketa

- Pakete iz `packages/ivanbaric/*` koristiti maksimalno umjesto dupliciranja funkcionalnosti u host aplikaciji.
- `packages/ivanbaric/corexis` je glavni paket za zajedničke standarde i mora se pregledati prije programiranja.

## Model repozitorija

- `niva` ostaje glavni monorepo i normalno se pusha iz root direktorija na Bitbucket/Forge.
- Ne vraćati zasebne `.git` direktorije u `packages/ivanbaric/*`; to smeta Forge deployu i miješa package repozitorije s root repoom.

## GitHub sinkronizacija paketa

Kada korisnik traži push paketa na GitHub:

1. Klonirati ciljni GitHub repozitorij u privremeni direktorij izvan `niva`, primjerice u `%TEMP%`.
2. U privremenom cloneu ukloniti stari sadržaj, ali sačuvati `.git` i `.github`.
3. Kopirati tracked sadržaj iz `packages/ivanbaric/<package>`, uključujući `README.md`, `CHANGELOG.md` i ostale trackane datoteke.
4. Provjeriti diff, commitati samo ako promjene postoje i pushati na granu iz mape repozitorija.

Ne koristiti `git archive` jer root `.gitattributes` ima `README.md export-ignore` pa archive izostavlja README iz package repozitorija.

## GitHub repozitoriji

| Package direktorij | SSH repozitorij | Grana |
| --- | --- | --- |
| `admin-ui` | `git@github.com:IvanBaric/admin-ui.git` | `main` |
| `audit` | `git@github.com:IvanBaric/audit.git` | `main` |
| `billing` | `git@github.com:IvanBaric/billing.git` | `main` |
| `blog` | `git@github.com:IvanBaric/blog.git` | `main` |
| `corexis` | `git@github.com:IvanBaric/corexis.git` | `main` |
| `eav` | `git@github.com:IvanBaric/eav.git` | `main` |
| `gallery` | `git@github.com:IvanBaric/gallery.git` | `main` |
| `language` | `git@github.com:IvanBaric/language.git` | `main` |
| `meta` | `git@github.com:IvanBaric/meta.git` | `main` |
| `pages` | `git@github.com:IvanBaric/page.git` | `main` |
| `plan` | `git@github.com:IvanBaric/plan.git` | `main` |
| `sanigen` | `git@github.com:IvanBaric/sanigen.git` | `master` |
| `seo` | `git@github.com:IvanBaric/seo.git` | `main` |
| `settings` | `git@github.com:IvanBaric/settings.git` | `main` |
| `starter` | `git@github.com:IvanBaric/starter.git` | `main` |
| `status` | `git@github.com:IvanBaric/status.git` | `main` |
| `taxonomy` | `git@github.com:IvanBaric/taxonomy.git` | `main` |
| `template-engine` | `git@github.com:IvanBaric/template-engine.git` | `main` |
| `velora` | `git@github.com:IvanBaric/velora.git` | `main` |
