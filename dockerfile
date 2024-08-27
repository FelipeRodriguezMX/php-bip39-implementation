FROM php:8.2-apache

ENV ACCEPT_EULA=Y

# Install system dependencies
RUN apt update && \
    apt-get install -y nano wget gnupg2 unzip p7zip-full libxml2-dev && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libgmp-dev \
    && docker-php-ext-install soap gmp

# Install and configure Xdebug
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug
COPY ./config-xdebug/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/

# Install SOAP extension
RUN docker-php-ext-install soap
RUN docker-php-ext-install gmp


# Set working directory
WORKDIR /var/www/proyecto/

# Copy application files
COPY . /var/www/proyecto

# Enable mod_rewrite and configure Apache
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite && \
    echo "display_errors = Off" >> /usr/local/etc/php/php.ini && \
    echo "log_errors = On" >> /usr/local/etc/php/php.ini

# Expose port 80 for Apache (default)
EXPOSE 9003

