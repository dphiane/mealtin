## Projet personnel en Symfony. Réservation en ligne dans mon ancien restaurant

Fonctionnalités implémentées :

    Mise en place de l’inscription avec un authenticator personnalisé
    Login et logout avec AuthenticationUtils
    Réinitialisation de mot de passe
    Page de profil, modification des informations
    Formulaire de contact
    Réservation d’une table via formulaire
    Obligation de s'inscrire pour authentifier l’utilisateur
    Mise en place d’un calendrier avec la désactivation des jours de fermeture
    Désactivation des créneaux horaires si le service est plein
    Mail de confirmation de réservation et de modification
    Possibilité de modifier ou annuler les réservations
    Dans un souci d'efficacité des tests, l’utilisateur peut réserver plusieurs fois le même jour
    Mise en place d'un rate limiter

## Installation

1. Téléchargez le projet ou utilisez `git clone https://github.com/dphiane/symfony_mealtin.git`
2. Utilisez les commandes suivantes :
   - `composer install`
   - `php bin/console importmap:install`
3. Importer les données qui sont dans le dossier DB_mealtin qui contient les fichiers sql
4. Configurez le fichier `.env` :
   - Décommentez la ligne suivante si vous utilisez une base de données MySQL, et remplacez les informations comme le nom d'utilisateur, le mot de passe, le nom du schéma :
     ```
     DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8.0.32&charset=utf8mb4"
     ```
   - Commentez les autres lignes `DATABASE_URL`.
   - Décommentez la ligne `# MAILER_DSN=null://null` et insérez votre token mailer. Cela devrait ressembler à ceci :
     ```
     MAILER_DSN=mailtrap+api://YOUR_API_KEY_HERE@default
     ```
     ou
     ```
     MAILER_DSN=mailtrap+api://YOUR_API_KEY_HERE@send.api.mailtrap.io
     ```
   - Vous pouvez utiliser le mailer Mailtrap qui est gratuit.
     
5. Lancez le serveur avec la commande : symfony serve -d
