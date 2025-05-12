# Tutoriels pour le Plugin WordPress RPG-IA

Ce document contient une série de tutoriels pour vous aider à utiliser efficacement le plugin WordPress RPG-IA.

## Table des matières

1. [Installation et configuration](#installation-et-configuration)
2. [Création de votre premier personnage](#création-de-votre-premier-personnage)
3. [Organisation d'une session de jeu](#organisation-dune-session-de-jeu)
4. [Jouer votre première partie](#jouer-votre-première-partie)
5. [Maîtriser une partie en tant que MJ](#maîtriser-une-partie-en-tant-que-mj)
6. [Personnalisation de l'interface](#personnalisation-de-linterface)
7. [Utilisation des shortcodes](#utilisation-des-shortcodes)
8. [Intégration avec d'autres plugins](#intégration-avec-dautres-plugins)

## Installation et configuration

### Tutoriel vidéo

[Lien vers la vidéo d'installation (à venir)]

### Instructions pas à pas

1. **Téléchargement du plugin**

   Commencez par télécharger le fichier ZIP du plugin depuis le [site officiel de RPG-IA](https://dgroots81.mandragore.ai).

   ![Téléchargement du plugin](https://dgroots81.mandragore.ai/images/tutorials/download-plugin.jpg)

2. **Installation via le tableau de bord WordPress**

   - Connectez-vous à votre tableau de bord WordPress
   - Naviguez vers "Extensions" > "Ajouter"
   - Cliquez sur "Téléverser une extension"
   - Sélectionnez le fichier ZIP téléchargé
   - Cliquez sur "Installer maintenant"
   - Une fois l'installation terminée, cliquez sur "Activer l'extension"

   ![Installation du plugin](https://dgroots81.mandragore.ai/images/tutorials/install-plugin.jpg)

3. **Configuration de l'API backend**

   - Après l'activation, allez dans "RPG-IA" > "Réglages"
   - Entrez l'URL de votre API backend RPG-IA
   - Cliquez sur "Tester la connexion" pour vérifier que tout fonctionne
   - Enregistrez vos réglages

   ![Configuration de l'API](https://dgroots81.mandragore.ai/images/tutorials/api-config.jpg)

4. **Vérification des pages créées**

   Le plugin crée automatiquement plusieurs pages. Vérifiez qu'elles existent en allant dans "Pages" dans le menu WordPress :
   
   - Tableau de bord RPG-IA
   - Personnages RPG-IA
   - Sessions RPG-IA
   - Interface de jeu RPG-IA

   Si ces pages n'ont pas été créées automatiquement, vous pouvez les créer manuellement en utilisant les shortcodes correspondants.

## Création de votre premier personnage

### Tutoriel vidéo

[Lien vers la vidéo de création de personnage (à venir)]

### Instructions pas à pas

1. **Accéder au formulaire de création**

   - Connectez-vous à votre compte
   - Accédez au "Tableau de bord RPG-IA"
   - Cliquez sur "Créer un personnage" ou allez dans "Personnages" puis "Nouveau personnage"

   ![Accès au formulaire](https://dgroots81.mandragore.ai/images/tutorials/character-form-access.jpg)

2. **Remplir les informations générales**

   - Donnez un nom à votre personnage
   - Choisissez une classe parmi les options disponibles (guerrier, magicien, clerc, voleur, nain, elfe, halfelin)
   - Notez que chaque classe a des avantages et des capacités spécifiques

   ![Informations générales](https://dgroots81.mandragore.ai/images/tutorials/character-general-info.jpg)

3. **Définir les caractéristiques**

   Vous pouvez soit :
   - Laisser le système générer aléatoirement les caractéristiques (3d6 pour chaque)
   - Attribuer manuellement des points (en respectant les limites 3-18)
   
   Les caractéristiques sont :
   - Force (FOR) : puissance physique et capacité au combat rapproché
   - Intelligence (INT) : connaissances et capacité à apprendre
   - Sagesse (SAG) : intuition et perception
   - Dextérité (DEX) : agilité et réflexes
   - Constitution (CON) : endurance et résistance
   - Charisme (CHA) : personnalité et leadership

   ![Caractéristiques](https://dgroots81.mandragore.ai/images/tutorials/character-abilities.jpg)

4. **Choisir l'équipement**

   - Chaque classe a un équipement de départ par défaut
   - Vous pouvez modifier cette sélection selon vos préférences
   - N'oubliez pas de respecter les restrictions de classe (par exemple, les magiciens ne peuvent pas porter d'armure lourde)

   ![Équipement](https://dgroots81.mandragore.ai/images/tutorials/character-equipment.jpg)

5. **Compléter la biographie et l'apparence**

   - Rédigez une courte biographie pour votre personnage
   - Décrivez son apparence physique
   - Ces informations aideront l'IA à mieux intégrer votre personnage dans l'histoire

   ![Biographie](https://dgroots81.mandragore.ai/images/tutorials/character-bio.jpg)

6. **Enregistrer votre personnage**

   - Vérifiez toutes les informations
   - Cliquez sur "Enregistrer"
   - Votre personnage apparaîtra maintenant dans votre liste de personnages

   ![Enregistrement](https://dgroots81.mandragore.ai/images/tutorials/character-save.jpg)

## Organisation d'une session de jeu

### Tutoriel vidéo

[Lien vers la vidéo d'organisation de session (à venir)]

### Instructions pas à pas

1. **Créer une nouvelle session**

   - Accédez à "Sessions" dans le menu RPG-IA
   - Cliquez sur "Nouvelle session"
   - Remplissez le formulaire de création de session

   ![Création de session](https://dgroots81.mandragore.ai/images/tutorials/session-create.jpg)

2. **Configurer les informations générales**

   - Donnez un nom à votre session
   - Rédigez une description qui explique le contexte de l'aventure
   - Ces informations seront visibles par les joueurs potentiels

   ![Informations de session](https://dgroots81.mandragore.ai/images/tutorials/session-info.jpg)

3. **Définir les paramètres de jeu**

   - Choisissez le système de règles (actuellement OSE - Old School Essentials)
   - Sélectionnez le niveau de difficulté
   - Ces paramètres influenceront la façon dont l'IA gère les défis

   ![Paramètres de jeu](https://dgroots81.mandragore.ai/images/tutorials/session-parameters.jpg)

4. **Sélectionner ou créer un scénario**

   - Vous pouvez choisir un scénario prédéfini dans la liste déroulante
   - Ou créer un nouveau scénario en cliquant sur "+ Nouveau"
   - Le scénario définit la trame narrative de l'aventure

   ![Sélection de scénario](https://dgroots81.mandragore.ai/images/tutorials/session-scenario.jpg)

5. **Gérer les joueurs**

   - Définissez le nombre maximum de joueurs
   - Invitez des joueurs spécifiques en saisissant leur nom d'utilisateur
   - Vous pouvez également laisser la session ouverte pour que n'importe qui puisse la rejoindre

   ![Gestion des joueurs](https://dgroots81.mandragore.ai/images/tutorials/session-players.jpg)

6. **Finaliser la création**

   - Vérifiez tous les paramètres
   - Cliquez sur "Enregistrer"
   - Votre session est maintenant créée et apparaît dans la liste des sessions

   ![Finalisation](https://dgroots81.mandragore.ai/images/tutorials/session-finalize.jpg)

## Jouer votre première partie

### Tutoriel vidéo

[Lien vers la vidéo de jeu (à venir)]

### Instructions pas à pas

1. **Rejoindre une session**

   - Accédez à la liste des sessions
   - Trouvez une session qui vous intéresse
   - Cliquez sur "Rejoindre"
   - Sélectionnez le personnage que vous souhaitez utiliser

   ![Rejoindre une session](https://dgroots81.mandragore.ai/images/tutorials/join-session.jpg)

2. **Comprendre l'interface de jeu**

   L'interface de jeu est divisée en plusieurs zones :
   
   - **Zone de narration** (en haut) : Affiche le texte généré par l'IA
   - **Zone d'action** (en bas à gauche) : Permet de soumettre des actions
   - **Zone d'information du personnage** (en bas à droite) : Affiche vos statistiques
   - **Zone d'information de la session** (en bas) : Montre les autres joueurs

   ![Interface de jeu](https://dgroots81.mandragore.ai/images/tutorials/game-interface.jpg)

3. **Soumettre votre première action**

   - Choisissez un type d'action dans le menu déroulant (dialogue, combat, exploration, etc.)
   - Rédigez une description détaillée de ce que vous voulez faire
   - Cliquez sur "Soumettre"
   - L'IA traitera votre action et générera une réponse

   ![Soumission d'action](https://dgroots81.mandragore.ai/images/tutorials/submit-action.jpg)

4. **Interagir avec l'environnement**

   Exemples d'actions efficaces :
   
   - **Exploration** : "J'examine attentivement les murs de la pièce, cherchant des passages secrets ou des mécanismes cachés."
   - **Dialogue** : "Je m'approche du tavernier et lui demande poliment s'il a entendu parler de disparitions récentes dans la région."
   - **Combat** : "Je dégaine mon épée et me place en position défensive, prêt à parer la première attaque du gobelin."

   ![Exemples d'actions](https://dgroots81.mandragore.ai/images/tutorials/action-examples.jpg)

5. **Utiliser l'inventaire**

   - Cliquez sur "Inventaire" dans la zone d'information du personnage
   - Consultez vos objets équipés et ceux dans votre sac
   - Utilisez les objets en cliquant sur "Utiliser" à côté de l'objet

   ![Utilisation de l'inventaire](https://dgroots81.mandragore.ai/images/tutorials/inventory-use.jpg)

6. **Communiquer avec les autres joueurs**

   - Utilisez l'onglet "Chat" pour envoyer des messages aux autres joueurs
   - Coordonnez vos actions pour plus d'efficacité
   - Discutez de la stratégie à adopter

   ![Communication](https://dgroots81.mandragore.ai/images/tutorials/player-chat.jpg)

7. **Consulter le journal**

   - Accédez à l'onglet "Journal" pour voir l'historique des actions
   - Filtrez par type d'événement si nécessaire
   - Ajoutez des notes personnelles pour vous souvenir d'informations importantes

   ![Journal](https://dgroots81.mandragore.ai/images/tutorials/game-journal.jpg)

## Maîtriser une partie en tant que MJ

### Tutoriel vidéo

[Lien vers la vidéo de maîtrise de jeu (à venir)]

### Instructions pas à pas

1. **Accéder au tableau de bord MJ**

   - Créez une session comme expliqué précédemment
   - Une fois la session créée, vous avez accès au tableau de bord MJ
   - Cliquez sur "Mode MJ" depuis la page de détails de la session

   ![Tableau de bord MJ](https://dgroots81.mandragore.ai/images/tutorials/gm-dashboard.jpg)

2. **Préparer votre scénario**

   - Accédez à "Scénarios" depuis le tableau de bord MJ
   - Créez un nouveau scénario ou modifiez un existant
   - Ajoutez des scènes, des PNJ, des monstres et des objets

   ![Préparation de scénario](https://dgroots81.mandragore.ai/images/tutorials/scenario-prep.jpg)

3. **Gérer les scènes**

   - Organisez les scènes dans l'ordre logique de votre aventure
   - Chaque scène peut avoir un type (combat, exploration, social, etc.)
   - Ajoutez des descriptions détaillées pour aider l'IA

   ![Gestion des scènes](https://dgroots81.mandragore.ai/images/tutorials/scene-management.jpg)

4. **Créer des PNJ et monstres**

   - Ajoutez des personnages non-joueurs importants
   - Créez des fiches pour les monstres que les joueurs rencontreront
   - Plus vous fournissez de détails, plus l'IA pourra créer une expérience immersive

   ![Création de PNJ](https://dgroots81.mandragore.ai/images/tutorials/npc-creation.jpg)

5. **Utiliser l'interface MJ pendant le jeu**

   Pendant la session, vous disposez de plusieurs outils :
   
   - **Contrôles de session** : Pause, reprise, fin de session
   - **Gestion des scènes** : Changement de scène en cours de jeu
   - **Supervision des joueurs** : Suivi des statistiques et de l'état des personnages
   - **Ressources** : Accès rapide aux PNJ, monstres, objets et cartes

   ![Interface MJ en jeu](https://dgroots81.mandragore.ai/images/tutorials/gm-interface.jpg)

6. **Intervenir dans la narration**

   - Consultez la narration générée par l'IA
   - Ajoutez du texte personnalisé pour enrichir l'histoire
   - Remplacez complètement la réponse de l'IA si nécessaire

   ![Intervention narrative](https://dgroots81.mandragore.ai/images/tutorials/narrative-override.jpg)

7. **Déclencher des événements**

   - Utilisez les boutons d'événements pour créer des situations spécifiques
   - Options disponibles : Combat, Rencontre, Piège, Récompense
   - Ces événements s'intégreront naturellement dans la narration

   ![Déclenchement d'événements](https://dgroots81.mandragore.ai/images/tutorials/event-triggers.jpg)

8. **Équilibrer le jeu**

   - Observez comment les joueurs interagissent avec l'IA
   - Ajustez la difficulté si nécessaire
   - Intervenez pour aider les joueurs en difficulté ou pour augmenter le défi

   ![Équilibrage du jeu](https://dgroots81.mandragore.ai/images/tutorials/game-balance.jpg)

## Personnalisation de l'interface

### Tutoriel vidéo

[Lien vers la vidéo de personnalisation (à venir)]

### Instructions pas à pas

1. **Utiliser les options de personnalisation intégrées**

   - Accédez à "RPG-IA" > "Réglages" > "Apparence"
   - Modifiez les couleurs, polices et autres éléments visuels
   - Prévisualisez les changements avant de les appliquer

   ![Options de personnalisation](https://dgroots81.mandragore.ai/images/tutorials/customization-options.jpg)

2. **Ajouter des CSS personnalisés**

   Pour les utilisateurs avancés :
   
   - Accédez à "Apparence" > "Personnaliser" > "CSS additionnel"
   - Ajoutez vos règles CSS personnalisées
   - Utilisez les variables CSS du plugin pour maintenir la cohérence

   ```css
   /* Exemple de CSS personnalisé */
   :root {
       --rpgia-primary-color: #8e44ad;
       --rpgia-secondary-color: #2c3e50;
       --rpgia-font-family: 'Merriweather', serif;
   }
   
   .rpgia-character-card {
       border-radius: 10px;
       box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
   }
   ```

   ![CSS personnalisé](https://dgroots81.mandragore.ai/images/tutorials/custom-css.jpg)

3. **Remplacer les templates**

   Pour une personnalisation avancée :
   
   - Créez un dossier `rpg-ia` dans votre thème
   - Copiez les templates que vous souhaitez modifier depuis le plugin
   - Modifiez-les selon vos besoins
   
   Structure de dossiers :
   ```
   your-theme/
   └── rpg-ia/
       ├── character-list.php
       ├── character-form.php
       ├── session-list.php
       └── game-interface.php
   ```

   ![Remplacement de templates](https://dgroots81.mandragore.ai/images/tutorials/template-override.jpg)

## Utilisation des shortcodes

### Tutoriel vidéo

[Lien vers la vidéo sur les shortcodes (à venir)]

### Instructions pas à pas

1. **Comprendre les shortcodes disponibles**

   Le plugin fournit plusieurs shortcodes pour intégrer des éléments RPG-IA dans vos pages :
   
   - `[rpgia_character id=X]` : Affiche les informations d'un personnage
   - `[rpgia_session id=Y]` : Affiche les informations d'une session
   - `[rpgia_dashboard]` : Affiche le tableau de bord du joueur
   - `[rpgia_game_interface session=Z]` : Affiche l'interface de jeu pour une session

   ![Shortcodes disponibles](https://dgroots81.mandragore.ai/images/tutorials/available-shortcodes.jpg)

2. **Afficher un personnage**

   - Créez une nouvelle page ou modifiez une page existante
   - Ajoutez le shortcode `[rpgia_character id=X]` où X est l'ID du personnage
   - Vous pouvez ajouter des paramètres supplémentaires :
     - `show_equipment="yes|no"` : Affiche ou masque l'équipement
     - `show_skills="yes|no"` : Affiche ou masque les compétences
     - `layout="full|compact"` : Définit la mise en page

   ![Shortcode de personnage](https://dgroots81.mandragore.ai/images/tutorials/character-shortcode.jpg)

3. **Afficher une session**

   - Créez une nouvelle page ou modifiez une page existante
   - Ajoutez le shortcode `[rpgia_session id=Y]` où Y est l'ID de la session
   - Paramètres supplémentaires :
     - `show_players="yes|no"` : Affiche ou masque la liste des joueurs
     - `show_scenario="yes|no"` : Affiche ou masque les informations du scénario
     - `join_button="yes|no"` : Affiche ou masque le bouton pour rejoindre

   ![Shortcode de session](https://dgroots81.mandragore.ai/images/tutorials/session-shortcode.jpg)

4. **Intégrer le tableau de bord**

   - Créez une nouvelle page ou modifiez une page existante
   - Ajoutez le shortcode `[rpgia_dashboard]`
   - Paramètres disponibles :
     - `show_stats="yes|no"` : Affiche ou masque les statistiques
     - `show_sessions="yes|no"` : Affiche ou masque les sessions actives
     - `show_characters="yes|no"` : Affiche ou masque les personnages

   ![Shortcode de tableau de bord](https://dgroots81.mandragore.ai/images/tutorials/dashboard-shortcode.jpg)

5. **Intégrer l'interface de jeu**

   - Créez une nouvelle page ou modifiez une page existante
   - Ajoutez le shortcode `[rpgia_game_interface session=Z]` où Z est l'ID de la session
   - Cela affichera l'interface de jeu complète pour la session spécifiée

   ![Shortcode d'interface de jeu](https://dgroots81.mandragore.ai/images/tutorials/game-interface-shortcode.jpg)

6. **Combiner plusieurs shortcodes**

   Vous pouvez combiner plusieurs shortcodes sur une même page pour créer une expérience personnalisée :
   
   ```
   <h2>Mon personnage actuel</h2>
   [rpgia_character id=123 layout="compact"]
   
   <h2>Ma session en cours</h2>
   [rpgia_session id=456 show_players="yes"]
   
   <h2>Commencer à jouer</h2>
   [rpgia_game_interface session=456]
   ```

   ![Combinaison de shortcodes](https://dgroots81.mandragore.ai/images/tutorials/combined-shortcodes.jpg)

## Intégration avec d'autres plugins

### Tutoriel vidéo

[Lien vers la vidéo d'intégration (à venir)]

### Instructions pas à pas

1. **Intégration avec BuddyPress**

   Si vous utilisez BuddyPress pour créer une communauté :
   
   - Installez et activez BuddyPress
   - Accédez à "RPG-IA" > "Réglages" > "Intégrations"
   - Activez l'intégration BuddyPress
   - Configurez les options d'affichage des personnages et sessions dans les profils

   ![Intégration BuddyPress](https://dgroots81.mandragore.ai/images/tutorials/buddypress-integration.jpg)

2. **Intégration avec bbPress**

   Pour intégrer RPG-IA avec les forums bbPress :
   
   - Installez et activez bbPress
   - Accédez à "RPG-IA" > "Réglages" > "Intégrations"
   - Activez l'intégration bbPress
   - Créez un forum dédié aux discussions RPG-IA

   ![Intégration bbPress](https://dgroots81.mandragore.ai/images/tutorials/bbpress-integration.jpg)

3. **Intégration avec WooCommerce**

   Si vous souhaitez vendre des scénarios ou des ressources :
   
   - Installez et activez WooCommerce
   - Accédez à "RPG-IA" > "Réglages" > "Intégrations"
   - Activez l'intégration WooCommerce
   - Créez des produits liés aux ressources RPG-IA

   ![Intégration WooCommerce](https://dgroots81.mandragore.ai/images/tutorials/woocommerce-integration.jpg)

4. **Intégration avec Elementor**

   Pour créer des mises en page personnalisées :
   
   - Installez et activez Elementor
   - Utilisez le widget RPG-IA dans vos conceptions Elementor
   - Créez des mises en page avancées pour vos pages de jeu

   ![Intégration Elementor](https://dgroots81.mandragore.ai/images/tutorials/elementor-integration.jpg)

5. **Intégration avec Discord**

   Pour la communication en temps réel :
   
   - Accédez à "RPG-IA" > "Réglages" > "Intégrations"
   - Activez l'intégration Discord
   - Configurez le webhook Discord
   - Les actions de jeu seront automatiquement publiées sur votre serveur Discord

   ![Intégration Discord](https://dgroots81.mandragore.ai/images/tutorials/discord-integration.jpg)