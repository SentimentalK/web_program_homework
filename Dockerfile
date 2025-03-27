FROM php:8.3-apache AS builder

# install dependencies
RUN apt-get update && \
    apt-get install -y \
        libzip-dev \
        zip \
        unzip \
        git \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# create a working directory
WORKDIR /var/www/html
COPY composer.json composer.lock ./

# install dependencies
RUN composer install --no-dev --no-scripts --optimize-autoloader


FROM php:8.3-apache

# Copy the application files from the builder stage
COPY --from=builder /var/www/html/vendor /var/www/html/vendor

# Enable mod_rewrite and install dependencies
RUN a2enmod rewrite && \
    apt-get update && \
    apt-get install -y \
        sqlite3 \
        libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && docker-php-ext-enable pdo_sqlite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    sed -i '/<Directory "${APACHE_DOCUMENT_ROOT}">/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
