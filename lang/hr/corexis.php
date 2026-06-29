<?php

declare(strict_types=1);

return [
    'result' => [
        'success' => 'Radnja je uspješno izvršena.',
        'error' => 'Radnju nije moguće izvršiti.',
    ],
    'authorization' => [
        'denied' => 'Nemate ovlasti za ovu radnju.',
    ],
    'concurrency' => [
        'stale_model' => 'Ovaj zapis je u međuvremenu promijenjen. Osvježite podatke prije spremanja.',
    ],
    'idempotency' => [
        'processing' => 'Zahtjev s istim idempotency ključem je već u obradi.',
        'replayed' => 'Zahtjev je već obrađen.',
    ],
    'cookie_consent' => [
        'aria_label' => 'Obavijest o kolačićima',
        'title' => 'Ova stranica koristi kolačiće',
        'message' => 'Koristimo nužne kolačiće za ispravan rad stranice i anonimnu statistiku pregleda kako bismo bolje razumjeli koji se sadržaji najviše čitaju.',
        'accept' => 'U redu',
        'policy' => 'Saznajte više',
    ],
];
