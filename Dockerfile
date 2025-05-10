FROM php:8.2-apache

# Installe les extensions nécessaires
RUN apt update && apt install -y \
    git unzip zip libicu-dev libonig-dev libzip-dev libpng-dev libjpeg-dev libpq-dev \
    && docker-php-ext-install intl mbstring pdo pdo_mysql zip

# Active mod_rewrite pour Symfony
RUN a2enmod rewrite

# Copie tout le code dans le container (sauf si bind mount utilisé)
WORKDIR /var/www/html
COPY . /var/www/html/

# On garge les uploads
RUN rm -rf /var/www/html/public/uploads/*

# CONFIGURATION APACHE
COPY php.ini /usr/local/etc/php/conf.d/99-custom-prod.ini

ARG PREPROD=false
COPY .htpasswd /tmp/.htpasswd
COPY apache-prod.conf /tmp/apache-prod.conf
COPY apache-preprod.conf /tmp/apache-preprod.conf

RUN if [ "$PREPROD" = "true" ]; then \
        cp /tmp/apache-preprod.conf /etc/apache2/sites-available/000-default.conf && \
        mv /tmp/.htpasswd /etc/apache2/.htpasswd ; \
    else \
        cp /tmp/apache-prod.conf /etc/apache2/sites-available/000-default.conf ; \
    fi


# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --prefer-dist --no-interaction --no-scripts

# Build des assets (si tu utilises Webpack Encore)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt install -y nodejs && \
    npm install && npm run build

# Permissions au répertoire public
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
