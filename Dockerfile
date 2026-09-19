# Use PHP 8.2 Apache as the base image
FROM php:8.2-apache

# Install system dependencies for Composer, Zip, and GD (Image processing for webp)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd zip

# Enable Apache mod_rewrite (Required for MVC routing)
RUN a2enmod rewrite

# Change Apache default port from 80 to 8000 (Koyeb standard)
RUN sed -i 's/80/8000/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Set working directory
WORKDIR /var/www/html

# Copy project files to the container
COPY . /var/www/html/

# Install Composer globally inside the container
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies (Generates vendor folder inside the container)
RUN composer install --no-dev --optimize-autoloader

# Fix directory permissions for Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 8000
