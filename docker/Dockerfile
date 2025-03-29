# Sử dụng PHP 8.2 với FPM
FROM php:8.2-fpm

# Cài đặt các extension PHP cần thiết
RUN apt-get update && apt-get install -y \
    unzip git curl libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev zip libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring gd zip bcmath

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Cấu hình thư mục làm việc
WORKDIR /var/www

# Copy project vào container
COPY . /var/www

# Cấp quyền cho storage và bootstrap/cache

# Expose cổng 9000 để chạy PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
