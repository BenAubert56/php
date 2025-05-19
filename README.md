# Accéder à l'app

Exécuter la commande suivante :
```bash
docker-compose up --build -d --build
```
Ensuite, installer les dépendances : 
```bash
docker exec -it symfony-php bash
```
```bash
composer install
```
```bash
php bin/console make:migration
```
Le serveur Symfony sera accessible sur :
```bash
http://localhost:8000
```

Et phpMyAdmin ici :
```bash
http://localhost:8080
```