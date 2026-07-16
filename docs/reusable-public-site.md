# Reusable Public Site Architecture

Ovaj dokument definira zajedničku arhitekturu javnih web-stranica koje se uređuju izravno na javnom prikazu.

## Vlasništvo Paketa

- `velora` posjeduje timove, javni profil organizacije, članstva, pozivnice i RBAC.
- `pages` posjeduje stranice, podstranice, sekcije, stavke, javni page resolver, javni controller i public-first editore.
- `template-engine` posjeduje registraciju templatea, schema polja, redoslijed i izvršavanje komponenti sekcija.
- `niva-template` posjeduje konkretne Niva Classic/Modern komponente, osam zaglavlja, hijerarhijsku desktop i mobilnu navigaciju, footer, javne Blade prikaze i Pages admin katalog sekcija.
- Konkretni vizualni template pripada zasebnom template paketu ili host aplikaciji. Ne stavljati domenske upite za proizvode, objave ili galerije u temeljni `template-engine`.
- `blog`, `gallery` i drugi domenski paketi posjeduju svoje zapise i pojedinačne javne prikaze.
- Host projekt konfigurira branding, konkretan template, dostupnost javnog vlasnika i opcionalni tracker posjeta.

Smjer ovisnosti mora ostati jednosmjeran:

```text
Corexis
  -> Velora
  -> Template Engine
  -> Pages -> Template Engine
  -> Concrete Template -> Pages + Template Engine + optional domain packages
  -> Host Application
```

## Javni Vlasnik

Velora `Organization` je generički javni profil vezan uz `Team`. Host smije konfigurirati podklasu kroz `velora.models.organization` i na nju dodati Gallery, SEO ili druge traitove. Velora model ne smije izravno ovisiti o tim paketima.

Novi projekt treba preferirati jedan javni profil po timu. Ako javni profil nije potreban odvojeno od tima, host smije koristiti konfigurirani Team model kao `pages.public_site.subject.model` pod uvjetom da ima stabilan slug, tenant identitet i active state.

## Pages Public Site

`pages.public_site` je zadano isključen kako paket ne bi preuzeo catch-all rute postojećeg projekta. Host ga uključuje konfiguracijom:

```php
'public_site' => [
    'enabled' => true,
    'view' => 'pages::public-site.page',
    'layout' => 'layouts.public',
    'view_subject_variable' => 'organization',
    'view_tracker' => App\Support\PagesPublicPageViewTracker::class,
    'subject' => [
        'model' => App\Models\Organization::class,
        'slug_column' => 'slug',
        'tenant_column' => 'team_id',
        'active_column' => 'is_active',
        'eager_load' => [],
    ],
    'route' => [
        'enabled' => true,
        'uri' => '/{organizationSlug}/{pageSlug?}',
        'name' => 'public.organization.page',
        'middleware' => ['web'],
        'subject_parameter' => 'organizationSlug',
        'page_parameter' => 'pageSlug',
    ],
],
```

Host može ostaviti `route.enabled=false` i ručno povezati `IvanBaric\Pages\Http\Controllers\PublicPageController` kada catch-all redoslijed ruta zahtijeva dodatne content ili taxonomy rute ispred page rute.

Za URL pojedinačnog sadržaja Pages nudi `PublicContentController`. `pages.public_site.content_providers` mapira stabilni `page_key` na implementaciju `PublicContentProvider`; provider dobiva tenant-safe `PublicContentContext` s vlasnikom, stranicom, navigacijom i slugom sadržaja. Domenski provider dohvaća samo zapis koji njegov paket posjeduje.

## Controller Ugovor

Generički controller mora:

1. Razriješiti javnog vlasnika kroz `PublicSiteSubjectResolver`.
2. Dohvatiti samo objavljenu tenant-scoped stranicu.
3. Eager-loadati `visibleSections`.
4. Dohvatiti objavljenu hijerarhijsku navigaciju.
5. Prepustiti render sekcija `template-engine` paketu.
6. Pozvati opcionalni `PublicPageViewTracker` bez ovisnosti o host analytics tablicama.
7. Montirati privilegirane editore samo autoriziranom korisniku istog tenanta.

Generički content controller mora ponovno koristiti isti subject i page resolver, odbiti neregistrirani `page_key` s 404 te render prepustiti registriranom `PublicContentProvideru`. Ne smije sadržavati upite prema Blog, Gallery, katalog ili host modelima.

## Što Se Ne Kopira U Novi Projekt

Novi projekt ne smije kopirati page controller, page query, public page Blade ili Organization bazni model. Instalira pakete i konfigurira javnog vlasnika, layout i template.

Host-specifični kod dopušten je za:

- brand i vizualni layout
- concrete template komponente
- domenske record providere i single prikaze
- middleware poslovne dostupnosti, npr. suspended pretplata
- analytics storage adapter
- seedere početnog sadržaja

## Niva Audit

Već izdvojeno:

- Organization baza i migracija u Velora
- hijerarhija stranica i public-first editori u Pages
- javni page subject resolver, page resolver, page/content controlleri, provider registry i view u Pages
- konfigurabilni public management flyout, panel registry i permission dispatch u Pages
- tenant image usage summary u Gallery
- request-aware administrativni audit u Audit
- support context i superadmin middleware u Velora
- template registracija, schema, payload i render loop u Template Engine
- concrete Classic/Modern template, header/footer navigacija, sekcijski renderer, prijevodi i admin definicije u Niva Template
- QR generator, allowlisted download kontroler i reusable sharing panel u QR paketu

Namjerno host-specifično:

- aktivacijski i subscription middleware
- tracker tablica i statistički dashboard
- proizvodi i njihove single stranice
- Niva recept pitanja i početne strukture; workflow, stanje i AI agent već pripadaju Onboarding paketu

Concrete template je izdvojen u `ivanbaric/niva-template`. Host konfigurira Organization/Product modele i admin URL resolver; projekt bez proizvoda može ostaviti product model praznim. Temeljni `template-engine` i dalje ne sadrži domenske upite ni konkretne vizualne predloške.

### Audit Preostalih Modula

| Niva područje | Odluka | Ciljno vlasništvo |
| --- | --- | --- |
| `CurrentTeamPublicWebsite` | Package servis postoji, Niva zadržava samo typed wrapper | Pages `CurrentPublicSite` |
| Organization bazni model i migracija | Izdvojeno | Velora |
| Public page/content controlleri, provider registry i page view | Izdvojeno | Pages |
| Page/section/item uređivanje na javnoj stranici | Već reusable | Pages |
| Template registry, schema, payload i render loop | Već reusable | Template Engine |
| Classic header, footer, hero i section Bladeovi | Izdvojeno, uključujući osam zaglavlja i mobilnu navigaciju | Niva Template |
| `GenericSection` record queryji | Izdvojeno uz konfigurabilni Product/Organization model; daljnji katalog ugovor ostaje moguća nadogradnja | Niva Template + domenski paketi |
| Product model, Actions, admin i public single provider | Niva zadržava domenski provider dok ne postoji zaseban katalog paket | Budući catalog/product paket |
| Public page view analytics | Storage i dashboard nisu Pages odgovornost; Pages izlaže tracker ugovor | Budući analytics paket ili host adapter |
| QR generator, download i panel | Izdvojeno; host resolver određuje URL, naziv, tenant pristup i smije li se QR preuzeti | QR |
| Theme color zabrana zelenih tonova | Niva brand pravilo, nije reusable platform pravilo | Host/concrete template |
| Public management flyout | Shell, registry, authorization i panel dispatch izdvojeni; projekt konfigurira komponente/viewove | Pages + vlasnički panel provideri |
| Initial setup wizard i AI sadržaj | Poslovni onboarding i recepti početnog sadržaja | Host + Starter recepti |
| Aktivacijski zahtjevi i subscription availability | Poslovni lifecycle proizvoda | Host/Billing integracija |
| Seederi konkretnih stranica i hrvatski tekstovi | Recept, ne package temelj | Starter/host preset |

Kod se premješta tek kada ciljni paket može definirati stabilan ugovor bez ovisnosti prema host aplikaciji. Sama činjenica da će drugi projekt imati sličan ekran nije dovoljan razlog za premještanje Niva modela ili route imena u temeljni paket.

## Checklist Novog Projekta

1. Instalirati Corexis, Velora, Template Engine, Pages, Admin UI i potrebne domenske pakete.
2. Pokrenuti migracije i permission sync.
3. Konfigurirati `velora.models.team` i `velora.models.organization`.
4. Konfigurirati `pages.public_site` subject, page/content rute, middleware, layout, tracker i content providere.
5. Instalirati Niva Template ili registrirati drugi concrete template i njegove sekcije.
6. Konfigurirati Niva Template modele/admin URL te koristiti `niva-template::public-layout` ili vlastiti layout s package Header/Footer komponentama.
7. Po potrebi konfigurirati public Language switcher i `PagePublicationGuard` host adapter.
8. Konfigurirati QR target resolver ako proizvod preuzima QR kod javne stranice.
9. Dodati samo potrebne domenske record providere.
10. Testirati anonimni render, tenant izolaciju, suspendirani tenant, public editing, mobilnu navigaciju i single content rute.
