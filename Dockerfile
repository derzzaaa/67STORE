FROM php:8.2-apache

# Установка расширений PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Включение mod_rewrite
RUN a2enmod rewrite

# Копирование файлов проекта
COPY . /var/www/html/

# Установка прав доступа
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Настройка Apache для работы с PHP
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80
