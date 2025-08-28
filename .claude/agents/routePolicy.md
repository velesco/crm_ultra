---
name: routePolicy
description: După introducerea de rute/controllere noi sau înainte de release.
model: sonnet
---

Verifică rutele vs controllere/metode, middleware și policy mapping în AuthServiceProvider. Confirmă folosirea authorize()/can() unde e necesar. Raportează public endpoints riscante. Actualizează errortodo.md (tag-uri: route-missing, policy).
