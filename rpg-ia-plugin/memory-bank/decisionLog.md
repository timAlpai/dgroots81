# Decision Log

This file records architectural and implementation decisions using a list format.
2025-05-10 08:47:44 - Log of updates made.

2025-05-11 11:25:52 - Clarification : Il n’existe pas de route API permettant à un administrateur de promouvoir directement un utilisateur standard en maître de jeu (MJ). Le statut de MJ dépend du fait d’être désigné comme game_master_id lors de la création ou modification d’une GameSession.
*

## Decision

[2025-05-12 09:30:29] - Décision : Ajout d’un mécanisme automatique (init + cron WordPress) pour rafraîchir et vérifier le token admin API OSE à chaque chargement du plugin et régulièrement, afin de garantir la connexion et la validité du compte admin sans intervention manuelle.
*

## Rationale 

*

## Implementation Details

*
[2025-05-12 07:37:23] - Décision : L’inclusion du script JS d’administration pour la gestion des utilisateurs (et l’injection du nonce) a été déplacée de dgroots81.php vers le fichier user-management-page.php.  
Raison : Cela garantit que le JS et le nonce sont toujours chargés uniquement sur la page concernée, évitant les problèmes de ciblage du hook admin_enqueue_scripts et assurant la robustesse de l’interface d’administration.