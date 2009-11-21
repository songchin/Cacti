echo -----------------------
echo  list options
echo -----------------------
php -q device_list.php --help
php -q device_list.php --template=1
php -q device_list.php --port=161 --timeout=500

clear
echo -----------------------
echo add and remove devices
echo -----------------------
php -q device_create.php --help
php -q device_create.php --ip=example1.company.com --description=foobar --template=1
php -q device_create.php --ip=example2.company.com --description=foobar --template=1 --community=secret
php -q device_create.php --ip=example3.company.com --description=foobar --template=1 --site-id=1 --poller-id=3
php -q device_list.php --description=foobar
php -q device_delete.php --help
php -q device_delete.php --ip=example1.company.com
php -q device_delete.php --description=foobar --force


clear
echo -----------------------
echo update
echo -----------------------
php -q device_create.php --ip=example1.company.com --description=foobar --template=1
php -q device_list.php --description=foobar

php -q device_update.php --help
php -q device_update.php --description=foobar --community=:secret
php -q device_list.php --description=foobar

php -q device_create.php --ip=example2.company.com --description=foobar --template=1 --community=secret
php -q device_create.php --ip=example3.company.com --description=foobar --template=1 --site-id=1 --poller-id=3
php -q device_update.php --community=secret#topsecret --delim=#
php -q device_update.php --description=foobar --notes=:test
php -q device_update.php --template=1 --version=:1 --timeout=:1000
php -q device_list.php --description=foobar
php -q device_delete.php --description=foobar --force
php -q device_list.php --description=foobar

