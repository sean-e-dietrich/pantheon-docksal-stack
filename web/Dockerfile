FROM nginx:1.8

COPY nginx.sh /opt/nginx.sh

COPY config /opt/config/

# Define working directory.
WORKDIR /var/www/

ENTRYPOINT ["/opt/nginx.sh"]

CMD ["-g daemon off;"]