#!/bin/bash

set -e # Fail on errors

echo 'Evaluating config variables...'
for name in VARNISH_BACKEND_PORT VARNISH_BACKEND_HOST VARNISH_BACKEND_DOMAIN
do
    eval value=\$$name
    sed -i "s|{${name}}|${value}|g" /etc/varnish/default.vcl
done

if [[ -n "${VARNISH_SECRET}" ]]; then
    echo "${VARNISH_SECRET}" > /etc/varnish/secret
    VARNISH_VARNISHD_PARAMS="${VARNISH_VARNISHD_PARAMS} -S /etc/varnish/secret"
fi

echo 'Starting varnishd...'
varnishd -f /etc/varnish/default.vcl \
	-s malloc,${VARNISH_CACHE_SIZE} \
	-a :${VARNISH_PORT} \
	-T :${VARNISH_ADMIN_PORT} \
	${VARNISH_VARNISHD_PARAMS}

echo 'Streaming logs (varnishncsa)...'
varnishncsa ${VARNISH_VARNISHNCSA_PARAMS}