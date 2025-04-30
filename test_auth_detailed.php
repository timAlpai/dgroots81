<?php
/**
 * Script de test détaillé pour l'authentification
 * 
 * Ce script teste en détail le processus d'authentification et affiche chaque étape
 * pour aider à diagnostiquer les problèmes.
 */

// Charger les fonctions WordPress nécessaires
require_once(dirname(__FILE__) . '/rpg-ia-plugin/includes/class-rpg-ia-api-client.php');

// Configuration du test
$test_username = 'test_user_' . time();
$test_email = 'test_' . time() . '@example.com';
$test_password = 'Test@' . time();

// Créer un client API
$api_client = new RPG_IA_API_Client();

echo "=== TEST D'AUTHENTIFICATION DÉTAILLÉ ===\n\n";

// Étape 1: Vérifier la connexion à l'API
echo "Étape 1: Vérification de la connexion à l'API\n";
$api_status = $api_client->check_api_status();
if (is_wp_error($api_status)) {
    echo "ÉCHEC: Impossible de se connecter à l'API: " . $api_status->get_error_message() . "\n";
    exit;
} else {
    echo "SUCCÈS: Connexion à l'API réussie - Version: " . $api_status['version'] . "\n";
}

// Étape 2: Vérifier si l'utilisateur existe
echo "\nÉtape 2: Vérification de l'existence de l'utilisateur '$test_username'\n";
$user_exists = $api_client->check_user_exists($test_username);
if (is_wp_error($user_exists)) {
    echo "AVERTISSEMENT: Impossible de vérifier l'existence de l'utilisateur: " . $user_exists->get_error_message() . "\n";
    echo "Tentative de connexion pour vérifier l'existence...\n";
    
    $login_response = $api_client->login($test_username, $test_password);
    if (!is_wp_error($login_response) && isset($login_response['access_token'])) {
        echo "SUCCÈS: L'utilisateur existe et les identifiants sont corrects\n";
        $user_exists = true;
    } else {
        echo "INFO: Échec de connexion, l'utilisateur n'existe probablement pas\n";
        $user_exists = false;
    }
} else {
    echo "INFO: Résultat de la vérification: " . ($user_exists ? "L'utilisateur existe" : "L'utilisateur n'existe pas") . "\n";
}

// Étape 3: Créer l'utilisateur si nécessaire
if (!$user_exists) {
    echo "\nÉtape 3: Création de l'utilisateur de test\n";
    $register_response = $api_client->register($test_username, $test_email, $test_password);
    
    if (is_wp_error($register_response)) {
        echo "ÉCHEC: Impossible de créer l'utilisateur: " . $register_response->get_error_message() . "\n";
        
        // Vérifier si l'erreur est due au fait que l'utilisateur existe déjà
        if (strpos($register_response->get_error_message(), 'déjà utilisé') !== false) {
            echo "INFO: L'utilisateur semble déjà exister, tentative de connexion...\n";
        } else {
            echo "ERREUR CRITIQUE: Impossible de créer l'utilisateur pour une raison inconnue\n";
            exit;
        }
    } else {
        echo "SUCCÈS: Utilisateur créé avec l'ID " . $register_response['id'] . "\n";
        echo "INFO: Attente de 2 secondes pour s'assurer que l'utilisateur est bien enregistré...\n";
        sleep(2);
    }
} else {
    echo "\nÉtape 3: L'utilisateur existe déjà, création ignorée\n";
}

// Étape 4: Tester l'authentification
echo "\nÉtape 4: Test d'authentification avec l'utilisateur '$test_username'\n";
$login_response = $api_client->login($test_username, $test_password);

if (is_wp_error($login_response)) {
    echo "ÉCHEC: Authentification échouée: " . $login_response->get_error_message() . "\n";
    
    // Afficher les détails de l'erreur
    echo "Détails de l'erreur: \n";
    echo "Code: " . $login_response->get_error_code() . "\n";
    echo "Message: " . $login_response->get_error_message() . "\n";
    
    // Tester avec un utilisateur connu (si disponible)
    echo "\nTest avec un utilisateur connu (dgroots81):\n";
    $known_login = $api_client->login('dgroots81', 'password123');
    if (is_wp_error($known_login)) {
        echo "ÉCHEC: Authentification avec utilisateur connu échouée: " . $known_login->get_error_message() . "\n";
    } else {
        echo "SUCCÈS: Authentification avec utilisateur connu réussie\n";
    }
} else {
    echo "SUCCÈS: Authentification réussie\n";
    echo "Token: " . $login_response['access_token'] . "\n";
    
    // Définir le token pour les requêtes suivantes
    $api_client->set_token($login_response['access_token']);
    
    // Étape 5: Récupérer les informations de l'utilisateur
    echo "\nÉtape 5: Récupération des informations de l'utilisateur\n";
    $user_info = $api_client->get_current_user();
    
    if (is_wp_error($user_info)) {
        echo "ÉCHEC: Impossible de récupérer les informations de l'utilisateur: " . $user_info->get_error_message() . "\n";
    } else {
        echo "SUCCÈS: Informations de l'utilisateur récupérées\n";
        echo "ID: " . $user_info['id'] . "\n";
        echo "Nom d'utilisateur: " . $user_info['username'] . "\n";
        echo "Email: " . $user_info['email'] . "\n";
    }
}

// Étape 6: Tester l'endpoint de vérification d'existence
echo "\nÉtape 6: Test de l'endpoint de vérification d'existence\n";
$check_exists = $api_client->check_user_exists($test_username);
if (is_wp_error($check_exists)) {
    echo "ÉCHEC: Impossible de vérifier l'existence de l'utilisateur: " . $check_exists->get_error_message() . "\n";
} else {
    echo "SUCCÈS: Vérification d'existence réussie\n";
    echo "Résultat: " . ($check_exists ? "L'utilisateur existe" : "L'utilisateur n'existe pas") . "\n";
}

// Étape 7: Tester l'endpoint de vérification d'existence avec un utilisateur inexistant
$nonexistent_username = 'nonexistent_user_' . time();
echo "\nÉtape 7: Test de l'endpoint de vérification d'existence avec un utilisateur inexistant\n";
$check_nonexistent = $api_client->check_user_exists($nonexistent_username);
if (is_wp_error($check_nonexistent)) {
    echo "ÉCHEC: Impossible de vérifier l'existence de l'utilisateur: " . $check_nonexistent->get_error_message() . "\n";
} else {
    echo "SUCCÈS: Vérification d'existence réussie\n";
    echo "Résultat: " . ($check_nonexistent ? "L'utilisateur existe (ERREUR)" : "L'utilisateur n'existe pas (CORRECT)") . "\n";
}

echo "\n=== FIN DU TEST ===\n";