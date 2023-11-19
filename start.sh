#!/bin/sh

# Define the configuration file path
CONF_FILE="/etc/mysql/mariadb.conf.d/50-server.cnf"

# Check if the file exists
if [ -f "$CONF_FILE" ]; then
    # Backup the original file
    cp "$CONF_FILE" "$CONF_FILE.bak"

    # Modify the bind address using sed
    sed -i "s/bind-address.*/bind-address = $MYSQL_ROOT_HOST/" "$CONF_FILE"

    echo "Bind address in $CONF_FILE has been updated to $MYSQL_ROOT_HOST"
else
    echo "Error: $CONF_FILE does not exist."
fi

# Start MariaDB
service mariadb start
echo "mysql password : $MYSQL_ROOT_PASSWORD"
echo "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY '$MYSQL_ROOT_PASSWORD' WITH GRANT OPTION;" | mysql
echo "FLUSH PRIVILEGES;" | mysql
# Create the database
echo "CREATE DATABASE IF NOT EXISTS db" | mysql

# Import the database
mysql db < /var/www/html/database.sql

# Start Apache in the foreground
apache2ctl -D FOREGROUND
