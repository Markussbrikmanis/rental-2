<?php

return [
    'roles' => [
        'admin' => 'Administrators',
        'owner' => 'Īpašnieks',
        'tenant' => 'Īrnieks',
    ],

    'welcome' => [
        'badge' => 'CoreUI + React',
        'title' => 'CoreUI ir uzstādīts šajā Laravel projektā.',
        'description' => 'Šī lapa tiek renderēta ar React un stilizēta ar CoreUI komponentēm, tāpēc frontend daļa ir gatava nākamajiem ekrāniem.',
        'checklist' => [
            'React ieejas punkts darbojas caur Vite',
            'CoreUI stili ir ielādēti aktīvu būvēšanas plūsmā',
            'Komponenšu bibliotēka ir gatava Blade lapām vai SPA paplašināšanai',
        ],
        'coreui_docs' => 'CoreUI dokumentācija',
        'react_docs' => 'React dokumentācija',
    ],

    'client' => [
        'common' => [
            'badge' => 'Klientu zona',
            'email' => 'E-pasts',
            'password' => 'Parole',
            'login' => 'Pieslēgties',
            'logout' => 'Izrakstīties',
            'register' => 'Reģistrēties',
            'back_to_login' => 'Atpakaļ uz pieslēgšanos',
            'account_type' => 'Konta tips',
            'full_name' => 'Pilns vārds',
            'confirm_password' => 'Apstipriniet paroli',
            'remember' => 'Atcerēties mani',
        ],
        'navigation' => [
            'aria' => 'Klienta navigācija',
            'panel' => 'Panelis',
            'properties' => 'Īpašumi',
        ],
        'topbar' => [
            'profile' => 'Profils',
            'language' => 'Valoda',
            'language_short' => 'LV',
            'language_soon' => 'Valodas pārslēgs tiks pievienots vēlāk.',
        ],
        'profile' => [
            'page_title' => 'Profils',
            'heading' => 'Profila iestatījumi',
            'description' => 'Atjauniniet sava konta informāciju, paroli un pārvaldiet konta piekļuvi.',
            'delete_description' => 'Dzēšot kontu, tiks neatgriezeniski dzēsti arī ar to saistītie dati.',
            'sections' => [
                'account' => 'Konta informācija',
                'password' => 'Paroles maiņa',
                'delete' => 'Dzēst kontu',
            ],
            'fields' => [
                'current_password' => 'Pašreizējā parole',
            ],
            'actions' => [
                'save_account' => 'Saglabāt profilu',
                'save_password' => 'Saglabāt paroli',
                'delete_account' => 'Dzēst kontu',
            ],
            'messages' => [
                'updated' => 'Profila informācija ir atjaunināta.',
                'password_updated' => 'Parole ir atjaunināta.',
                'deleted' => 'Konts ir dzēsts.',
            ],
        ],
        'messages' => [
            'invalid_credentials' => 'Norādītie piekļuves dati neatbilst mūsu ierakstiem.',
            'need_account' => 'Vajadzīgs konts?',
        ],
        'login' => [
            'page_title' => 'Klienta pieslēgšanās',
            'heading' => 'Pieslēgties',
            'intro' => 'Piekļūstiet kopīgajam klientu panelim administratoriem, īpašniekiem un īrniekiem.',
            'register_cta' => 'Izveidot klienta kontu',
        ],
        'register' => [
            'page_title' => 'Klienta reģistrācija',
            'heading' => 'Izveidot kontu',
            'intro' => 'Pašreģistrācija ir pieejama īpašniekiem un īrniekiem. Administratora konti tiek pārvaldīti atsevišķi ar sējumiem vai iekšējiem rīkiem.',
            'submit' => 'Izveidot kontu',
        ],
        'panel' => [
            'page_title' => 'Klienta panelis',
            'heading' => 'Klienta panelis',
            'signed_in_as' => 'Pieslēdzies kā :name (:email).',
            'workspace_title' => 'Lomai pielāgota darba vide',
            'workspace_intro' => 'Sadaļa /client tagad apvieno autentifikāciju un paneļa piekļuvi zem viena prefiksa ar atšķirīgu saturu administratoriem, īpašniekiem un īrniekiem.',
            'seeded_accounts' => 'Demo konti',
            'cards' => [
                'admin' => [
                    [
                        'title' => 'Platformas kontrole',
                        'text' => 'Pārvaldiet globālos iestatījumus, lietotājus un sistēmas pārskatāmību visā platformā.',
                    ],
                    [
                        'title' => 'Piekļuves pārvaldība',
                        'text' => 'Pārskatiet lomu piešķīrumus un uzturiet īpašnieku un īrnieku piekļuves saskaņā ar biznesa noteikumiem.',
                    ],
                    [
                        'title' => 'Sistēmas uzraudzība',
                        'text' => 'Izmantojiet šo paneli kā sākumpunktu audita žurnāliem, eskalācijām un izņēmumu apstrādei.',
                    ],
                ],
                'owner' => [
                    [
                        'title' => 'Īpašumu portfelis',
                        'text' => 'Sekojiet saviem īpašumiem, noslodzei un īpašnieka darbībām vienā panelī.',
                    ],
                    [
                        'title' => 'Īrnieku koordinācija',
                        'text' => 'Pārskatiet īrnieku aktivitātes, apstiprinājumus un ar īpašumiem saistītos paziņojumus.',
                    ],
                    [
                        'title' => 'Finanšu plūsma',
                        'text' => 'Sagatavojiet šo sadaļu rēķiniem, īres uzskaitei un īpašnieku atskaitēm.',
                    ],
                ],
                'tenant' => [
                    [
                        'title' => 'Īres pārskats',
                        'text' => 'Glabājiet nomas informāciju, maksājumu statusu un svarīgos atjauninājumus vienuviet.',
                    ],
                    [
                        'title' => 'Pieteikumi un problēmas',
                        'text' => 'Paplašiniet šo sadaļu ar uzturēšanas pieteikumiem, dokumentiem un īrnieku atbalsta plūsmām.',
                    ],
                    [
                        'title' => 'Saziņas centrs',
                        'text' => 'Izmantojiet klienta zonu paziņojumiem, apstiprinājumiem un nākotnes pašapkalpošanās rīkiem.',
                    ],
                ],
            ],
        ],
    ],

    'properties' => [
        'defaults' => [
            'country' => 'Latvija',
        ],
        'countries' => [
            'Latvija',
            'Lietuva',
            'Igaunija',
        ],
        'types' => [
            'garage' => 'Garāža',
            'office' => 'Birojs',
            'warehouse' => 'Noliktava',
            'apartment' => 'Dzīvoklis',
            'house' => 'Privātmāja',
            'land' => 'Zeme',
        ],
        'fields' => [
            'name' => 'Nosaukums',
            'notes' => 'Piezīmes',
            'address' => 'Adrese',
            'city' => 'Pilsēta',
            'country' => 'Valsts',
            'price' => 'Īpašuma cena',
            'type' => 'Īpašuma veids',
            'acquired_at' => 'Iegādes datums',
        ],
        'actions' => [
            'open_list' => 'Atvērt īpašumu sarakstu',
            'create' => 'Pievienot īpašumu',
            'create_first' => 'Pievienot pirmo īpašumu',
            'save' => 'Saglabāt īpašumu',
            'update' => 'Saglabāt izmaiņas',
            'edit' => 'Rediģēt',
            'delete' => 'Dzēst',
            'cancel' => 'Atcelt',
        ],
        'messages' => [
            'created' => 'Īpašums ":name" ir izveidots.',
            'updated' => 'Īpašums ":name" ir atjaunināts.',
            'deleted' => 'Īpašums ":name" ir dzēsts.',
        ],
        'owner_panel' => [
            'title' => 'Īpašumu pārvaldība',
            'description' => 'Īpašniekiem tagad ir pieejams atsevišķs īpašumu CRUD saraksts klientu zonā.',
        ],
        'index' => [
            'page_title' => 'Īpašumi',
            'heading' => 'Mani īpašumi',
            'description' => 'Pārvaldiet savus īpašumus, to tipus, cenas un iegādes informāciju vienuviet.',
            'actions_column' => 'Darbības',
            'all_option' => 'Visi',
            'results' => 'Atrasti :count ieraksti',
            'search_placeholder' => 'Meklēt...',
            'date_placeholder' => 'dd.mm.gggg',
            'sort_hint' => 'Klikšķiniet uz kolonnas nosaukuma, lai mainītu kārtošanu ASC vai DESC.',
            'auto_filter_hint' => 'Meklēšana, kārtošana un lapošana darbojas automātiski tabulā.',
        ],
        'empty' => [
            'title' => 'Īpašumi vēl nav pievienoti',
            'description' => 'Izveidojiet pirmo īpašuma ierakstu, lai sāktu pārvaldību klientu zonā.',
        ],
        'create' => [
            'page_title' => 'Jauns īpašums',
            'heading' => 'Pievienot īpašumu',
            'description' => 'Aizpildiet īpašuma pamatinformāciju, lai tas parādītos jūsu īpašumu sarakstā.',
        ],
        'edit' => [
            'page_title' => 'Rediģēt īpašumu',
            'heading' => 'Rediģēt īpašumu',
            'description' => 'Atjauniniet īpašuma ":name" informāciju.',
        ],
    ],

    'validation' => [
        'messages' => [
            'required' => 'Lauks :attribute ir obligāts.',
            'string' => 'Laukam :attribute jābūt teksta vērtībai.',
            'email' => 'Laukā :attribute jānorāda derīga e-pasta adrese.',
            'numeric' => 'Laukam :attribute jābūt skaitlim.',
            'date' => 'Laukā :attribute jānorāda derīgs datums.',
            'max.string' => 'Lauks :attribute nedrīkst būt garāks par :max rakstzīmēm.',
            'min.string' => 'Laukam :attribute jābūt vismaz :min rakstzīmes garam.',
            'min.numeric' => 'Laukam :attribute jābūt vismaz :min.',
            'confirmed' => 'Lauka :attribute apstiprinājums nesakrīt.',
            'unique' => 'Šāda :attribute vērtība jau tiek izmantota.',
            'in' => 'Laukam :attribute ir nederīga vērtība.',
        ],
        'attributes' => [
            'name' => 'vārds',
            'email' => 'e-pasta adrese',
            'role' => 'konta tips',
            'password' => 'parole',
            'current_password' => 'pašreizējā parole',
            'notes' => 'piezīmes',
            'address' => 'adrese',
            'city' => 'pilsēta',
            'country' => 'valsts',
            'price' => 'īpašuma cena',
            'type' => 'īpašuma veids',
            'acquired_at' => 'iegādes datums',
        ],
    ],
];
