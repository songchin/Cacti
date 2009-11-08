echo -----------------------
echo  list options
echo -----------------------
php -q graph_list.php --help
php -q graph_list.php --device-id=1
php -q graph_list.php --graph-template-id=2
php -q graph_list.php --device-id=1 --graph-template-id=2

clear
echo -----------------------
echo add and remove cg type graphs
echo -----------------------
php -q graph_create.php --help
php -q graph_create.php --graph-type=cg --device-id=1 --graph-template-id=11
php -q graph_list.php --device-id=1 --graph-template-id=11
php -q graph_create.php --graph-type=cg --device-id=1 --graph-template-id=11 --force
php -q graph_list.php --device-id=1 --graph-template-id=11

php -q graph_delete.php --device-id=1 --graph-template-id=11 --force
php -q graph_list.php --device-id=1

clear
echo -----------------------
echo add and remove ds type graphs
echo -----------------------
php -q graph_create.php --graph-type=ds --device-id=1 --graph-template-id=2 --snmp-query-id=1 --snmp-query-type-id=14 --snmp-field=ifName --snmp-value=lo
php -q graph_list.php --device-id=1
php -q graph_delete.php --device-id=1 --graph-template-id=2 --force
php -q graph_list.php --device-id=1

clear
echo -----------------------
echo update
echo -----------------------
php -q graph_create.php --graph-type=cg --device-id=1 --graph-template-id=11
php -q graph_list.php --graph-id=49

php -q graph_update.php --help
php -q graph_update.php --graph-id=49 --graph-title='New Title1'
php -q graph_list.php --graph-id=49

php -q graph_create.php --graph-type=cg --device-id=1 --graph-template-id=11 --force
php -q graph_list.php --graph-template-id=11
php -q graph_update.php --graph-template-id=11 --graph-title='New Title2'
php -q graph_list.php --graph-template-id=11

php -q graph_update.php --device-id=1 --graph-template-id=11 --graph-title='New Title3'
php -q graph_list.php --device-id=1 --graph-template-id=11

php -q graph_delete.php --device-id=1 --graph-template-id=11 --force
php -q graph_list.php --device-id=1
