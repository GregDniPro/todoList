FROM php:8.2-fpm

# Update and install required packages
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    librabbitmq-dev \
    unzip

# Install PHP pdo extensions
RUN docker-php-ext-install pdo pdo_pgsql zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory
WORKDIR /var/www/html

# Expose the PHP server port
EXPOSE 9000

# Start the PHP server
CMD ["php-fpm", "-F"]
