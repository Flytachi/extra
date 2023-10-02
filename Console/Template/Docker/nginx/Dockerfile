FROM nginx:latest

RUN apt-get update && apt-get install -y nginx
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf

# Copy the Laravel app files to the working directory
COPY . /var/www/html

RUN chown -R www-data: /var/www/html
RUN chmod -R 777 /var/www/html/storage
RUN chmod -R 755 /var/www/html/public

# Expose port 80 for Nginx
EXPOSE 80
RUN nginx -t