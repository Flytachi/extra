FROM nginx:alpine

COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY . /var/www/html

RUN chown -R nginx:nginx /var/www/html \
    && chmod -R 755 /var/www/html/public \
    && chmod -R 777 /var/www/html/storage

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
