# 📝 TodoApp - Gestionnaire de Tâches

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

TodoApp est une application web complète permettant de gérer une liste de tâches avec authentification utilisateur. Elle offre toutes les fonctionnalités CRUD (Create, Read, Update, Delete) dans une interface moderne et responsive.

## ✨ Fonctionnalités

### 🔐 Authentification
- **Inscription** : Création de nouveaux comptes utilisateurs
- **Connexion** : Système de login sécurisé avec sessions PHP
- **Gestion de sessions** : Chaque utilisateur accède uniquement à ses propres tâches

### 📋 Gestion des Tâches
- **Créer** une nouvelle tâche avec titre, date de début et date de fin
- **Afficher** toutes les tâches de l'utilisateur connecté
- **Modifier** les informations d'une tâche existante
- **Supprimer** une tâche
- **Marquer comme terminée** : Changer le statut d'une tâche
- **Réactiver** : Remettre une tâche terminée en cours

### 🔍 Filtres et Catégories
L'application propose 4 vues différentes :
- **📊 Toutes** : Affichage de toutes les tâches
- **⏳ En cours** : Tâches actives (non terminées et non expirées)
- **✅ Terminées** : Tâches marquées comme complétées
- **⏰ Expirées** : Tâches dont la date de fin est dépassée

### 🎨 Interface Utilisateur
- Design moderne avec **Bootstrap 5**
- Icônes avec **Font Awesome 6**
- Interface responsive adaptée à tous les appareils
- Badges de comptage pour chaque catégorie
- Codage couleur visuel (vert pour terminé, rouge pour expiré)
- Messages de confirmation et d'erreur

## 📂 Structure du Projet

```
TodoApp/
├── TodoAPP/
│   ├── BaseDeDonnees.php      # Configuration de la connexion à la base de données
│   ├── Login_Page.php          # Page de connexion
│   ├── signUpPage.php          # Page d'inscription
│   ├── Insert.php              # Création de nouvelles tâches
│   ├── Select.php              # Affichage et gestion des tâches
│   ├── Update.php              # Modification des tâches
│   ├── Delete.php              # Suppression des tâches
│   └── Style.css               # Styles CSS personnalisés
├── css/                        # Fichiers CSS additionnels
├── js/                         # Fichiers JavaScript
└── assets/                     # Images et ressources
```

## 🛠️ Technologies Utilisées

- **Backend** : PHP 7.x / 8.x
- **Base de données** : MySQL / MariaDB
- **Frontend** :
  - HTML5
  - CSS3
  - JavaScript
  - Bootstrap 5.3.0
  - Font Awesome 6.4.0

## 📋 Prérequis

Avant de commencer, assurez-vous d'avoir installé :

- **PHP** >= 7.4
- **MySQL** ou **MariaDB**
- **Serveur Web** (Apache, Nginx) ou **XAMPP/WAMP/MAMP**
- **Extension PHP** : `mysqli`

## 🚀 Installation

### 1. Cloner le repository

```bash
git clone https://github.com/Zineb-Azaroual/TodoApp.git
cd TodoApp
```

### 2. Configuration de la base de données

#### Créer la base de données

Connectez-vous à MySQL et créez la base de données :

```sql
CREATE DATABASE ToDoApp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ToDoApp;
```

#### Créer la table des utilisateurs

```sql
CREATE TABLE Utilisateurs (
    idUtilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nomUtilisateur VARCHAR(100) NOT NULL UNIQUE,
    emailUtilisateur VARCHAR(150) NOT NULL UNIQUE,
    motDePasse VARCHAR(255) NOT NULL,
    dateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Créer la table des tâches

```sql
CREATE TABLE Taches (
    idTache INT AUTO_INCREMENT PRIMARY KEY,
    TitreTache VARCHAR(255) NOT NULL,
    dateTacheDebut DATETIME NOT NULL,
    dateTacheFin DATETIME NOT NULL,
    statusTache TINYINT(1) DEFAULT 0 COMMENT '0=en cours, 1=terminée',
    idUtilisateur INT NOT NULL,
    dateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idUtilisateur) REFERENCES Utilisateurs(idUtilisateur) ON DELETE CASCADE,
    INDEX idx_utilisateur (idUtilisateur),
    INDEX idx_status (statusTache)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3. Configurer la connexion à la base de données

Modifiez le fichier `TodoAPP/BaseDeDonnees.php` avec vos paramètres :

```php
<?php
$conn = mysqli_connect('localhost', 'votre_utilisateur', 'votre_mot_de_passe', 'ToDoApp', 3306);

if (!$conn) {
    echo "Connection error: " . mysqli_connect_error();
    exit();
}
?>
```

> ⚠️ **Sécurité** : Ne commitez jamais vos identifiants réels. Utilisez des variables d'environnement en production.

### 4. Déploiement local

#### Avec XAMPP/WAMP/MAMP :

1. Copiez le dossier du projet dans le répertoire web (`htdocs` pour XAMPP)
2. Démarrez Apache et MySQL
3. Accédez à : `http://localhost/TodoApp/TodoAPP/Login_Page.php`

#### Avec le serveur PHP intégré :

```bash
cd TodoApp/TodoAPP
php -S localhost:8000
```

Accédez à : `http://localhost:8000/Login_Page.php`

## 📖 Utilisation

### Première utilisation

1. **Créer un compte** :
   - Accédez à la page d'inscription (`signUpPage.php`)
   - Remplissez le formulaire avec nom d'utilisateur, email et mot de passe
   - Cliquez sur "S'inscrire"

2. **Se connecter** :
   - Utilisez vos identifiants sur la page de connexion (`Login_Page.php`)
   - Vous serez redirigé vers le gestionnaire de tâches

3. **Créer votre première tâche** :
   - Cliquez sur le bouton "+ Ajouter tâche"
   - Remplissez le titre, date de début et date de fin
   - Validez le formulaire

### Gestion des tâches

- **Voir les tâches** : Utilisez les onglets pour filtrer par catégorie
- **Modifier** : Cliquez sur le bouton "Modifier" (icône crayon)
- **Terminer** : Cliquez sur "Terminer" pour marquer la tâche comme complétée
- **Réactiver** : Sur une tâche terminée, cliquez sur "Réactiver"
- **Supprimer** : Cliquez sur "Supprimer" (une confirmation sera demandée)

## 🔒 Sécurité

L'application implémente plusieurs mesures de sécurité :

- ✅ **Sessions PHP** : Authentification et isolation des données utilisateurs
- ✅ **Requêtes préparées** : Protection contre les injections SQL
- ✅ **Validation des entrées** : Vérification côté serveur
- ✅ **Échappement HTML** : Protection contre les attaques XSS avec `htmlspecialchars()`
- ✅ **Vérification des permissions** : Chaque utilisateur ne peut accéder qu'à ses propres tâches

### Recommandations pour la production :

- 🔐 Hasher les mots de passe avec `password_hash()` et `password_verify()`
- 🌐 Utiliser HTTPS
- 🔑 Stocker les identifiants de base de données dans des variables d'environnement
- 🛡️ Implémenter un système de CSRF tokens
- ⏱️ Ajouter une limitation de taux (rate limiting)

## 🎨 Personnalisation

### Modifier les couleurs

Les couleurs principales sont définies dans le fichier `TodoAPP/Select.php` (section `<style>`) :

```css
--bs-primary: #256db4;  /* Bleu principal */
```

### Ajouter des champs personnalisés

Pour ajouter de nouveaux champs aux tâches :

1. Modifier la table dans MySQL :
```sql
ALTER TABLE Taches ADD COLUMN descriptionTache TEXT;
```

2. Mettre à jour les formulaires (`Insert.php`, `Update.php`)
3. Adapter l'affichage dans `Select.php`

## 🐛 Résolution des problèmes

### Erreur de connexion à la base de données
- Vérifiez les identifiants dans `BaseDeDonnees.php`
- Assurez-vous que MySQL est démarré
- Vérifiez que la base de données `ToDoApp` existe

### Session non persistante
- Vérifiez que `session_start()` est appelé en début de chaque page
- Vérifiez les permissions du dossier des sessions PHP

### Interface non stylée
- Vérifiez votre connexion internet (Bootstrap et Font Awesome sont chargés via CDN)
- Vérifiez les chemins vers les fichiers CSS locaux

## 📝 Roadmap

Fonctionnalités futures envisagées :

- [ ] Ajout de catégories/tags pour les tâches
- [ ] Système de priorité (haute, moyenne, basse)
- [ ] Notifications par email pour les tâches expirées
- [ ] Export des tâches en PDF/CSV
- [ ] Mode sombre
- [ ] API REST pour une application mobile
- [ ] Calendrier visuel des tâches
- [ ] Partage de tâches entre utilisateurs

## 👥 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Forkez le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/AmazingFeature`)
3. Commitez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Poussez vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## 📄 Licence

Ce projet est distribué sous licence libre. Vous êtes libre de l'utiliser, le modifier et le distribuer.

## 📧 Contact

**Zineb Azaroual** - [@Zineb-Azaroual](https://github.com/Zineb-Azaroual)

Lien du projet : [https://github.com/Zineb-Azaroual/TodoApp](https://github.com/Zineb-Azaroual/TodoApp)

---

⭐ Si ce projet vous a été utile, n'hésitez pas à lui donner une étoile !
