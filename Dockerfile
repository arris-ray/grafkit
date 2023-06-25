FROM php:8.2-cli

# Update env vars
ENV PATH="${PATH}:/app/vendor/bin"

# Create app directory
RUN mkdir -p /app
WORKDIR /app

# Install system utilities
RUN apt update -y \
    && apt install -y git unzip vim wget

# Install application depedencies
RUN apt install -y libyaml-dev libzip-dev \
    && printf "\n" | pecl install yaml \
      && echo "extension=yaml.so" > /usr/local/etc/php/conf.d/ext-yaml.ini \
      && docker-php-ext-enable yaml \
    && docker-php-ext-install zip

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
  && php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
  && php composer-setup.php \
  && php -r "unlink('composer-setup.php');" \
  && mv composer.phar /usr/local/bin/composer
