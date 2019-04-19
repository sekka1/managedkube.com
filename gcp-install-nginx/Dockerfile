FROM nginx:1.14

# Server section of the nginx.conf which /etc/nginx/nginx.conf includes in
COPY default.conf /etc/nginx/conf.d/default.conf

# The static page
COPY index.html /usr/share/nginx/html/index.html
