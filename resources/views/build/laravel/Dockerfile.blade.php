FROM gcr.io/rafter/php-7.4-grpc:latest

RUN curl -sL https://deb.nodesource.com/setup_13.x | bash -

# Install production dependencies
RUN apt-get update && apt-get install -y \
    libjpeg-dev \
    nodejs

# Install MySQL driver extensions
RUN docker-php-ext-install \
    pdo_mysql

# Enable PECL and PEAR extensions
RUN docker-php-ext-enable \
    grpc

# Configure php extensions
RUN docker-php-ext-configure \
    gd --with-jpeg

# Tell Apache that we want to use the public folder as our root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER 1
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Copy over package manifests so this step can be cached
COPY composer.json composer.lock package*.json /var/www/html/

WORKDIR /var/www/html

# Install initial composer dependencies
RUN composer install --prefer-dist --no-scripts --no-dev --no-autoloader

# Install Node dependencies
RUN npm ci

# Copy the rest of the project to the container image.
COPY . /var/www/html/

# Run composer install again to trigger Laravel's scripts
RUN composer install --no-dev --classmap-authoritative && php artisan event:cache

# Compile node things
RUN npm run prod && rm -rf node_modules

# Use the PORT environment variable in Apache configuration files.
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Make contents writeable to the web server
RUN chown -R www-data:www-data /var/www/html

# Enable Apache rewrites
RUN a2enmod rewrite headers

RUN chmod 755 docker-entrypoint.sh

ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
