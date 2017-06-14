FROM nginx:1.9.0

COPY nginx.conf /etc/nginx/nginx.conf

RUN usermod -u 1000 www-data
RUN usermod -G staff www-data
