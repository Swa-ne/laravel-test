# Use an official PHP runtime as a base image
FROM php:8.1-apache

# Set the working directory in the container
WORKDIR /var/www/html

# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/html/

# Install PHP extensions and dependencies
RUN apt-get update && \
    apt-get install -y \
        git \
        zip \
        unzip && \
    docker-php-ext-install pdo pdo_mysql && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install application dependencies
RUN composer install

# Copy the application code into the container
COPY . /var/www/html/

# Change ownership of the application directory
RUN chown -R www-data:www-data /var/www/html/storage

# Expose port 80 to the outside world
EXPOSE 80

# Start the Apache web server
CMD ["apache2-foreground"]
