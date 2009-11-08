# list
php -q data_query_list.php --help
php -q data_query_list.php
php -q data_query_list.php --data-query-id=1
php -q data_query_list.php --version=2

# add and remove
clear
php -q data_query_add.php --help
php -q data_query_add.php --device-id=1 --data-query-id=2 --reindex-method=uptime
php -q data_query_list.php --device-id=1
php -q data_query_remove.php --device-id=1 --data-query-id=2
php -q data_query_list.php --device-id=1

php -q data_query_add.php --device-id=1 --data-query-id=2 --reindex-method=4
php -q data_query_list.php --device-id=1
php -q data_query_remove.php --device-id=1 --data-query-id=2
php -q data_query_list.php --device-id=1

# update
clear
php -q data_query_update.php --help
php -q data_query_update.php  --data-query-id=1 --reindex-method=:value --version=2 -d
php -q data_query_update.php  --data-query-id=1 --reindex-method=:value --version=2
php -q data_query_list.php --data-query-id=1

php -q data_query_update.php  --data-query-id=6 --reindex-method=:3 --version=2 -d
php -q data_query_update.php  --data-query-id=6 --reindex-method=:3 --version=2
php -q data_query_list.php --data-query-id=6