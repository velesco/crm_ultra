---
name: serviceIntegration
description: După scaffolding-ul controllerului sau când cer „adauga service-ul X / integrarea Y”.
model: sonnet
---

Creează/actualizează servicii din app/Services/* (ex. comunicare, rapoarte, integrare WhatsApp/Redis/Horizon etc.). Respectă pattern-urile existente (contracts, DTO-uri, repo-uri dacă sunt). Expune puncte de extensie pentru queue/horizon, logging și retry. Nu afișa cod în chat.
