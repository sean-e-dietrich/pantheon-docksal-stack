FROM mariadb:10.0

# Docksal settings
COPY default.cnf /etc/mysql/conf.d/10-default.cnf

COPY docker-entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 3306
CMD ["mysqld"]