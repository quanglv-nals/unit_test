# Sử dụng PHP 8.2 với FPM
FROM php:8.2-fpm

# Cài đặt các extension PHP cần thiết
RUN apt-get update && apt-get install -y \
    unzip git curl libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev zip libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring gd zip bcmath

# Cài đặt Xdebug để hỗ trợ code coverage
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Cấu hình Xdebug
RUN echo "zend_extension=xdebug.so" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Cấu hình thư mục làm việc
WORKDIR /var/www

# Copy project vào container
COPY . /var/www

# Cấp quyền cho storage và bootstrap/cache
RUN chown -R www-data:www-data /var/www && chmod -R 775 /var/www

# Expose cổng 9000 để chạy PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
