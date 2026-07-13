# Corexis Engineering Workflow Standard

Ovaj dokument sadrži obavezna opća pravila rada i završne provjere.

## Upravljanje dokumentacijom

- Corexis repozitorij je jedini izvor istine za zajedničke standarde IvanBaric projekata i paketa.
- `AGENTS.md` datoteke služe samo kao ulazne točke i indeksi; normativna pravila pripadaju dokumentima unutar `docs`.
- Zajedničko pravilo pripada u `docs/standards`, detaljna domenska propozicija u odgovarajući Corexis dokument, a pravilo koje vrijedi samo za jedan host projekt u `docs/projects/<project>.md`.
- `README.md` dokumentira instalaciju i javne API-je paketa. Ne duplicirati sigurnosne, arhitekturne, UI ili projektne standarde u README.
- Ako se dva dokumenta ne slažu, ne birati proizvoljno jedno pravilo. U istoj izmjeni uskladiti konflikt i ostaviti samo jedan normativni izvor za to ponašanje.
- Kada se doda novi dokument, odmah ga povezati iz Corexis `AGENTS.md`; dokumentacijska provjera ne smije dopuštati nepovezane dokumente.

## Project Instructions

- Always use UTF-8 encoding without BOM.
- Never replace Croatian characters with broken encoding.
- Croatian characters must remain correct: č, ć, ž, š, đ, Č, Ć, Ž, Š, Đ.
- All visible admin UI text must be in Croatian.
- Before changing files, preserve existing file encoding.
- Do not generate mojibake text such as broken Croatian letters.
- If encoding is broken, fix it before continuing.
- Laravel project uses Croatian UI labels, so encoding correctness is mandatory.


## Kritične provjere prije završetka
- Nakon masovnih ili mehaničkih izmjena Blade viewova obavezno ručno provjeriti nekoliko dotaknutih tagova. Ne ubacivati HTML atribute regexom u multiline `@class([...])`, Blade echo ili PHP izraze bez dodatne provjere jer se lako razbije sintaksa.
- Nakon izmjena javnih Blade sekcija pokrenuti barem `php artisan view:clear` i fokusirane feature testove koji renderiraju promijenjene sekcije ili single prikaze. Ako test nije moguće pokrenuti, to jasno navesti.
- Za promjene u `packages/ivanbaric/*` ne oslanjati se samo na root `git diff` ili `git status`; provjeriti i package direktorij s `git -C packages/ivanbaric/<package> status` jer package može biti zaseban/nested worktree.
- Kada se mijenja javni render, provjeriti stvarni HTML preko lokalnog URL-a ili testa, ne samo izvorni Blade.
