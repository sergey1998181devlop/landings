FROM cr.yandex/crpbmo0llrf3kifjdq43/base-boostra-site:latest

COPY ./deploy/docker/apache2-conf/apache2.conf /etc/apache2/apache2.conf
COPY ./deploy/docker/apache2-conf/ports.conf /etc/apache2/ports.conf
COPY ./deploy/docker/apache2-conf/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY ./deploy/docker/apache2-conf/.dev-htpasswd /etc/apache2/.htpasswd
# Copy the custom php.ini to conf.d for additional configuration
COPY ./cgi-bin/php.ini /usr/local/etc/php/conf.d/custom-php.ini
#RUN a2enmod rewrite
COPY . .

RUN composer update && composer install

RUN chown www-data:www-data * -R
RUN ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/rewrite.load
RUN ln -s /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled/headers.load
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Включаем необходимые модули Apache
RUN a2enmod rewrite headers remoteip

# RUN service apache2 restart

RUN yes | pecl install xdebug-3.1.5 \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_autostart=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=PHPStorm" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_host=127.0.0.1" >> /usr/local/etc/php/conf.d/xdebug.ini \

# FOR USE PHP FPM

#EXPOSE 9000
#CMD ["php-fpm"]

EXPOSE 8088
#HEALTHCHECK --interval=10s --timeout=5s CMD curl --fail http://localhost:8088/ || exit 1
CMD ["apache2ctl", "-D",  "FOREGROUND"]
