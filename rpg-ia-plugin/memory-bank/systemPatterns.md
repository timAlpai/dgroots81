# System Patterns *Optional*

This file documents recurring patterns and standards used in the project.
It is optional, but recommended to be updated as the project evolves.
2025-05-10 08:47:50 - Log of updates made.

*

## Coding Patterns

*   

## Architectural Patterns

*   

## Testing Patterns

*
[2025-05-12 08:33:13] - IMPORTANT : Les routes de l’API OSE (FastAPI) ne supportent pas le trailing slash (« / ») à la fin des URLs. Toute requête avec un slash final provoque un 307 Temporary Redirect, ce qui peut casser les appels DELETE, PUT, etc. Toujours générer les URLs d’API sans slash final pour éviter ce bug lors des prochaines générations de fonctions.