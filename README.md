<div id="top"></div>

<!-- PROJECT LOGO -->
<br />
<div align="center">

<img src="Readme_ico.png" width="100" height="100">

<h3 align="center">MSI - BackEnd</h3>

   <p align="center">
      Logiciel de gestion de tickets en interne !
   </p>
</div>

![](Readme_img.png)

### Front-End
<p>
   La partie Front-End faite en Vue est disponible ici : <a href="http://gitea.groupemontroland.fr/sguillot/msi-front">clique ici !</a>
</p>

### Développer Avec

* [Php 8.1](https://www.php.net/releases/8.1/en.php)
* [Symfony 6](https://symfony.com/releases/6.1)

<!-- GETTING STARTED -->

## Mise en Place

Voici la démarche à faire pour installer le repo et mettre en place le site web.

### Prerequisites

1. Installer Php
2. Installer Composer
3. Installer Symfony CLI

### Configuration

1. Configurer les variables d'environnement (.env)
   ```dotenv
   DATABASE_URL="mysql://user_db:password@127.0.0.1:3306/name_db?serverVersion=mariadb-10.9.2&charset=utf8mb4"
   ```
   Vous devez modifier la variable **DATABASE_URL** qui contient la chaîne de connexion à la base de données.

### Installation

1. Cloner le Repo
   ```dotenv
   git clone http://gitea.groupemontroland.fr/epaul/msi_back.git
   ```
2. Aller dans le dossier
   ```dotenv
   cd msi_back/ 
   ```
3. Installer les paquets PHP
   ```dotenv
   composer install
   ```
4. Créer la base de données
   ```dotenv
   symfony console doctrine:database:create
   ```
5. Exécuter les migrations
   ```dotenv
   symfony console doctrine:migration:migrate
   ```
6. Ajouter Utilisateur pour connexion
   ```dotenv
   symfony console doctrine:fixtures:load
   ```
7. Générer les clés des tokens JWT
   ```dotenv
   symfony console lexik:jwt:generate-keypair
   ```
8. Ouvrez votre navigateur et aller à l'adresse suivante :
   ```dotenv
   localhost:8000
   ```
