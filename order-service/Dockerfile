
# Menggunakan PHP 8.4 dan Apache untuk menjalankan Laravel.
FROM php:8.4-apache

# Memasang kebutuhan sistem dan ekstensi PHP.
RUN apt-get update \
    && apt-get install -y git unzip libzip-dev \
    && docker-php-ext-install pdo_mysql zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Memasang Composer dari image resmi.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Menetapkan direktori aplikasi Laravel.
WORKDIR /var/www/html

# Menyalin kode aplikasi ke dalam image.
COPY . .

# Memasang dependensi produksi Laravel.
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Mengarahkan Apache ke folder public Laravel.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Memperbarui document root pada konfigurasi Apache.
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Memberikan izin tulis pada cache dan log Laravel.
RUN chown -R www-data:www-data storage bootstrap/cache
