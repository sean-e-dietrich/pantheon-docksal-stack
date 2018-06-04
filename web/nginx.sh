#!/usr/bin/env bash
set -e

FRAMEWORK=${FRAMEWORK:-drupal}

# Emulate /srv/binding
#mkdir -p /srv/bindings/${PANTHEON_BINDING}
#ln -s /code "/srv/bindings/$PANTHEON_BINDING/" || true

if [[ -n "${WEB}" ]]; then
    WEB_PATH="/web"
fi
#export DOCKSAL_WEBROOT="/srv/bindings/${PANTHEON_BINDING}/code${WEB_PATH}"
export DOCKSAL_WEBROOT="/var/www${WEB_PATH}/"
# Make sure we have correct ownership
chown -Rf www-data:www-data /code || true

cp /opt/config/nginx.conf /etc/nginx/nginx.conf

envsubst '$DOCKSAL_WEBROOT' < /opt/config/${FRAMEWORK}.conf > /etc/nginx/conf.d/default.conf

# Run the NGINX
nginx "$@"