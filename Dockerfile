# Use the official PHP image from Docker Hub
FROM php:8.1-apache

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy your application code into the container
COPY . .

# Install any PHP extensions (e.g., MySQL support)
RUN docker-php-ext-install pdo pdo_mysql

# Expose port 80 to access the web service
EXPOSE 80

# The default command to run Apache
CMD ["apache2-foreground"]
