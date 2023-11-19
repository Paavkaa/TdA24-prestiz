# Use the PHP 8.1 with Apache image as the base
FROM node:latest AS node
FROM php:8.2-apache

COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /usr/local/bin/node /usr/local/bin/node
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

ENV MYSQL_ROOT_PASSWORD='root'
ENV MYSQL_ROOT_HOST=0.0.0.0
WORKDIR /var/www/html
# Install system dependencies and MariaDB
RUN apt-get update && \
    apt-get install -y mariadb-server && \
    apt-get install -y libzip-dev unzip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy the application code to the container
COPY my.cnf /etc/mysql/conf.d/
COPY . .
# Install PHP dependencies
RUN composer install --no-interaction --no-ansi --no-scripts


# Install Node.js and npm using the official setup script
RUN npm install -g npm@10.2.4
# Install TypeScript globally
RUN npm install -g typescript
RUN tsc --version
# Compile TypeScript to JavaScript
RUN tsc


# Update the Apache configuration to point to /var/www/html/www (where your index.php is)
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    ServerName localhost\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Copy start.sh and make it executable
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Start Apache and other services
CMD ["/start.sh"]
