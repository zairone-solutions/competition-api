FROM php:8.0.2-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libgd-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    ffmpeg \
    cron # Install cron

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN if ! id -u $user > /dev/null 2>&1; then \
    useradd -G www-data,root -u $uid -d /home/$user $user; \
    fi

RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

COPY ./.env /var/www/.env

WORKDIR /var/www

# Copy the Laravel cronjob script
COPY ./crontab /etc/cron.d/laravel-cron

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/laravel-cron

# Apply cron job
RUN crontab /etc/cron.d/laravel-cron

# Create the log file to be able to run tail
RUN touch /var/log/cron.log

# Set the user to root to run cron daemon
USER $user
