echo -----------------------
echo  list options
echo -----------------------
php -q perms_list.php --help
php -q perms_list.php --list-groups
php -q perms_list.php --list-users
php -q perms_list.php --list-trees
php -q perms_list.php --list-realms
php -q perms_list.php --list-realms --user-id=1
php -q perms_list.php --list-realms --realm-id=7
php -q perms_list.php --list-realms --user-id=1 --realm-id=101
php -q perms_list.php --list-perms
php -q perms_list.php --list-perms --user-id=1
php -q perms_list.php --list-perms --item-type=graph
php -q perms_list.php --list-perms --item-id=54
php -q perms_list.php --list-perms --user-id=1 --item-type=graph
echo This will throw errors
php -q perms_list.php --list-realms --user-id=bar
php -q perms_list.php --list-realms --realm-id=foo

clear
echo -----------------------
echo add and remove perms
echo -----------------------
php -q perms_create.php --help
php -q perms_create.php --item-type=graph --item-id=54 --user-id=3,1
php -q perms_list.php --list-perms --item-type=graph
php -q perms_delete.php --item-type=graph --item-id=54 --user-id=3,1
php -q perms_list.php --list-perms --item-type=graph

php -q perms_create.php --item-type=device --item-id=5 --user-id=3,1
php -q perms_list.php --list-perms --item-type=device
php -q perms_delete.php --item-type=device --item-id=5 --user-id=3,1
php -q perms_list.php --list-perms --item-type=device

php -q perms_create.php --item-type=graph_template --item-id=2 --user-id=3,1
php -q perms_list.php --list-perms --item-type=graph_template
php -q perms_delete.php --item-type=graph_template --item-id=2 --user-id=3,1
php -q perms_list.php --list-perms --item-type=graph_template


clear
echo -----------------------
echo update
echo -----------------------
php -q perms_create.php --perms-type=cg --device-id=1 --perms-template-id=11
php -q perms_list.php --perms-id=49

php -q perms_update.php --help
php -q perms_update.php --perms-id=49 --perms-title='New Title1'
php -q perms_list.php --perms-id=49

php -q perms_create.php --perms-type=cg --device-id=1 --perms-template-id=11 --force
php -q perms_list.php --perms-template-id=11
php -q perms_update.php --perms-template-id=11 --perms-title='New Title2'
php -q perms_list.php --perms-template-id=11

php -q perms_update.php --device-id=1 --perms-template-id=11 --perms-title='New Title3'
php -q perms_list.php --device-id=1 --perms-template-id=11

php -q perms_delete.php --device-id=1 --perms-template-id=11 --force
php -q perms_list.php --device-id=1
