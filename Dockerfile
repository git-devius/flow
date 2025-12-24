FROM php:8.2-fpm

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    default-mysql-client \
    libzip-dev \
    zlib1g-dev \
    && docker-php-ext-install pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configuration PHP (inchangée)
RUN echo "upload_max_filesize = 10M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini

# -- NOUVELLE LIGNE --
# Copier la configuration de session personnalisée
COPY php/conf.d/sessions.ini /usr/local/etc/php/conf.d/sessions.ini

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier composer.json et installer les dépendances
COPY composer.json /var/www/html/composer.json
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Copier le reste de l'application
COPY . /var/www/html

# -- LIGNES MODIFIÉES --
# Créer les répertoires nécessaires avec les bonnes permissions
RUN mkdir -p /var/www/html/uploads \
    /var/www/html/queue/emails \
    /var/www/html/queue/emails/failed \
    /var/www/sessions \
    && chown -R www-data:www-data \
    /var/www/html/uploads \
    /var/www/html/queue \
    /var/www/sessions

# Rendre le script wait-for-db exécutable
RUN chmod +x /var/www/html/bin/wait-for-db.sh

EXPOSE 9000

CMD ["php-fpm"]