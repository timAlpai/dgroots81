# Progress

This file tracks the project's progress using a task list format.
2025-05-10 08:47:37 - Log of updates made.

*

## Completed Tasks

*   

## Current Tasks

[2025-05-12 09:20:18] - Correction du JS inline sur la page de gestion des utilisateurs : après inscription API réussie, le bouton « Inscrire à l’API » est désactivé et le bouton « Supprimer » activé sur la ligne concernée.
[2025-05-12 09:29:58] - Ajout d’un mécanisme automatique (init + cron) pour rafraîchir et vérifier le token admin API OSE à chaque chargement du plugin et régulièrement via WordPress cron.
[2025-05-12 09:32:41] - Adaptation du cron : la vérification/rafraîchissement du token admin API OSE s’effectue désormais toutes les 30 minutes (intervalle personnalisé), pour garantir la validité continue du token (TTL 60min).
[2025-05-12 09:35:18] - Adaptation du cron : la vérification/rafraîchissement du token admin API OSE s’effectue désormais toutes les 15 minutes (TTL token = 30min), pour garantir une validité continue sans risque d’expiration.
*   

## Next Steps

*