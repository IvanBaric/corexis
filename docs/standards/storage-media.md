# Corexis Storage And Media Standard

Ovaj dokument sadrži obavezna pravila za deployment konfiguraciju, pohranu, media obradu, lazy loading i slike.

## Storage i produkcijske konfiguracije
- U lokalnom SQLite developmentu ne koristiti `CACHE_STORE=database` ni `SESSION_DRIVER=database`; koristiti `file` ili Redis. Database cache/session na SQLite-u često zaključava `database.sqlite` tijekom paralelnih Livewire requestova i javlja `SQLSTATE[HY000]: database is locked`.
- Ne hardkodirati produkcijski promjenjive diskove, drivere, bucket-e, URL-ove, modele, providere ili druge deployment opcije direktno u kodu.
- Ne koristiti `Storage::disk('public')`, `store(..., 'public')` ili slične hardkodirane diskove za javne uploadane medije; koristiti `corexis_public_media_disk()` i `corexis_public_media_url()`.
- Gallery/media uploadi trebaju ići kroz jedan centralni javni media disk: `MEDIA_DISK`. `media-library.disk_name`, `gallery.disk` i `corexis_public_media_disk()` moraju se razriješiti na isti disk kako bi se isti kod mogao prebaciti s lokalnog diska na S3 bez izmjena u aplikacijskom kodu.
- Ako je fallback potreban, fallback smije biti u configu ili corexis helperu, ali ne razbacan po viewovima, Livewire komponentama i akcijama.
- Media Library upload mora spremiti optimizirani WebP master umjesto sirovog korisničkog originala. Format, kvaliteta i maksimalne dimenzije dolaze iz `gallery.stored_originals.*` (`GALLERY_OPTIMIZE_ORIGINAL_UPLOADS`, `GALLERY_STORED_ORIGINAL_FORMAT`, `GALLERY_STORED_ORIGINAL_QUALITY`, `GALLERY_STORED_ORIGINAL_MAX_WIDTH`, `GALLERY_STORED_ORIGINAL_MAX_HEIGHT`).
- Media Library konverzije ostaju centralne: format i kvaliteta konverzija moraju dolaziti iz `gallery.conversions.format` i `gallery.conversions.quality` (`GALLERY_CONVERSION_FORMAT`, `GALLERY_CONVERSION_QUALITY`), a javni prikaz treba koristiti imenovane konverzije (`thumb`, `medium`, `large`, `xlarge`) prije originala.
- Media Library javni URL-ovi trebaju biti verzionirani (`media-library.version_urls` u configu) kako browser/CDN ne bi držao staru sliku nakon zamjene ili regeneracije konverzija.

## Livewire lazy i slike
- Livewire lazy loading i HTML `loading="lazy"` nisu ista stvar. Livewire lazy odgađa mount/render cijele komponente, a `loading="lazy"` odgađa samo učitavanje slike.
- Javne sekcije ispod prvog ekrana smiju koristiti Livewire 4 `lazy`, ali hero/header i prvi viewport ne smiju biti lazy ako su ključni za prvi prikaz.
- Kod Livewire 4 lazy class-based komponenti placeholder ide kroz `placeholder()` metodu i mora imati isti root element kao stvarna komponenta. Placeholder mora imati stabilnu visinu kako se stranica ne bi trzala dok se sekcija učitava.
- Nakon uključivanja Livewire lazy provjeriti renderirani HTML i tražiti `lazyLoaded:false` te `x-intersect="$wire.__lazyLoad(...)"` ili odgovarajući `x-init` za defer način.
- Slike u hero/header/above-the-fold dijelu u pravilu trebaju `loading="eager"` i `decoding="async"`. Obične slike ispod prvog ekrana trebaju `loading="lazy"` i `decoding="async"`.
- Veće mobilne fotografije prije Livewire uploada pripremiti client-side kad god je moguće: rotacija/orijentacija, smanjenje dimenzija, WebP/JPEG kompresija i jasna hrvatska poruka greške. Server-side limit i validacija ostaju obavezni i centralni.
