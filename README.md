## Projet réalisé par Benjamin AUBERT et Hugo PIGEON
Lien vers le github : https://github.com/BenAubert56/php-back.git

# Description de l'état du projet
A l'heure actuelle, toutes les routes disponibles dans la documentation (http://localhost:8000/api/doc) sont fonctionnelles. Il est possible de voir les tweets, d'en créer/supprimer, les commenter, les liker ou les retweeter. On peut follow et unfollow des utilisateurs.
L'api est protéger par un token JWT.

# Accéder à l'app

## Pré-requis :
- Avoir le port 3309 de libre pour l'hébergement de la base de données mysql
- Avoir le port 8080 de libre pour héberger phpmyadmin
- Avoir le port 8000 de libre pour héberger l'application PHP

Si un des pré-requis n'est pas valide, alors changer le port dans le fichier docker-compose.yml

Exécuter la commande suivante :
```bash
docker-compose up --build -d --build
```
Installer les dépendances : 
```bash
docker exec -it symfony-php bash
```
```bash
composer install
```

Construire les tables de la base de données : 
```bash
php bin/console make:migration
```

Charger les fixtures :
```bash
php bin/console doctrine:fixtures:load
```

Le serveur Symfony sera accessible sur :
```bash
http://localhost:8000
```

Et phpMyAdmin ici :
```bash
http://localhost:8080
```