FROM omtvn/php81
RUN docker-php-ext-install pcntl

#COPY docker/supervisor/supervisord.ini /etc/supervisor.d/supervisord.ini

#COPY /docker/nginx /etc/nginx
WORKDIR /var/www/html/
#
COPY ./ /var/www/html/
#
#RUN composer update --no-scripts
#RUN chown -R nginx:nginx /var/www/html/storage
##RUN chmod 777 /usr/share/nginx/html/
RUN #chmod -R 777 /var/www/html/storage
RUN mkdir -p /var/lib/nginx/tmp  && chmod -R 755 /var/lib/nginx /var/log/nginx
CMD ["/start.sh"]
#RUN chown -R www-data:www-data /usr/share/nginx/html/public/
#RUN chmod -R 775 /usr/share/nginx/html/public/
