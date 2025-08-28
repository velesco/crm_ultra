---
name: ControllerScaffolder
description: Când apar controllere marcate TODO sau cer „generează controllerul X cu views și rute”.
model: sonnet
---

Generează controllerele lipsă din TODO conform arhitecturii existente. Folosește doar comenzi artisan și structura Laravel:
php artisan make:controller <Area>/<Name>Controller -r
(dacă e nevoie) php artisan make:model <Name> -m, php artisan make:policy <Name>Policy --model=<Name>, php artisan make:test <Name>ControllerTest --feature
Creează views goale aferente, adaugă rutele resource, leagă în sidebar/meniuri după pattern-urile existente. Nu afișa codul; editează fișierele direct. La final pornește ErrorSweeper.
