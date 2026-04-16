# Déploiement Flow sur Ubuntu avec Apache

Ce guide détaille les étapes pour installer et configurer l'application **Flow** sur un serveur Ubuntu 22.04+ fraîchement installé.

---

## 1. Pré-requis Système

Installez Apache, PHP 8.1+ et les modules nécessaires :

```bash
sudo apt update
sudo apt install apache2 mariadb-server php libapache2-mod-php php-mysql php-mbstring php-xml php-curl php-gd php-zip git curl -y
```

Activez le module de réécriture d'URL :

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## 2. Clonage et Dépendances

Clonez votre dépôt de code dans `/var/www/flow` :

```bash
cd /var/www
sudo git clone <URL_DU_DEPOT> flow
cd flow
```

Installez Composer (si non présent) puis les dépendances :

```bash
# Installation de composer localement
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Installation des vendor
composer install --no-dev --optimize-autoloader
```

---

## 3. Configuration de l'Environnement

Initialisez le fichier `.env` :

```bash
cp .env .env.example # Si un fichier exemple existe, sinon créez-le
```

Éditez le fichier pour la production :
- `APP_ENV=production`
- `DB_HOST`, `DB_PASSWORD`, etc.
- `BASE_URL=https://votre-domaine.com`

---

## 4. Permissions (Crucial)

Apache doit pouvoir écrire dans le dossier des uploads :

```bash
sudo chown -R www-data:www-data /var/www/flow
sudo chmod -R 775 /var/www/flow/uploads
```

---

## 5. Configuration Apache (VirtualHost)

Utilisez le fichier fourni dans `deploy/apache.conf` ou créez-en un nouveau :

```bash
sudo nano /etc/apache2/sites-available/flow.conf
```

**Contenu suggéré :**
```apache
<VirtualHost *:80>
    ServerName votre-domaine.com
    DocumentRoot /var/www/flow/public

    <Directory /var/www/flow/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/flow_error.log
    CustomLog ${APACHE_LOG_DIR}/flow_access.log combined
</VirtualHost>
```

Activez le site et désactivez le défaut :

```bash
sudo a2ensite flow.conf
sudo a2dissite 000-default.conf
sudo systemctl reload apache2
```

---

## 6. Installation et Configuration de la Base (MariaDB)

Sécurisez votre installation MariaDB :

```bash
sudo mysql_secure_installation
```

Connectez-vous à MySQL pour créer la base et l'utilisateur :

```bash
sudo mysql -u root
```

Puis exécutez ces requêtes SQL (adaptez `MOT_DE_PASSE`) :

```sql
CREATE DATABASE flowdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'flow_user'@'localhost' IDENTIFIED BY 'MOT_DE_PASSE';
GRANT ALL PRIVILEGES ON flowdb.* TO 'flow_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 7. Migration des Données

Utilisez maintenant le script fourni pour importer le schéma initial :

```bash
php bin/migrate.php
```
