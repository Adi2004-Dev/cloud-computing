FROM php:8.3-apache
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
# Render/Railway/Cloud Run inject $PORT; make Apache listen on it (default 80)
CMD ["sh","-c","sed -i \"s/80/${PORT:-80}/g\" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
