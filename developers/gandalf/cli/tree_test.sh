echo -----------------------
echo  list options
echo -----------------------
php -q tree_list.php --help
php -q tree_list.php --list-trees
php -q tree_list.php --list-nodes --tree-id=1
php -q tree_list.php --list-nodes --tree-id=1 --node-type=device
php -q tree_list.php --list-nodes --tree-id=1 --node-type=header
php -q tree_list.php --list-nodes --tree-id=1 --node-type=header --parent-node=nn
php -q tree_list.php --list-nodes --tree-id=1 --node-type=graph
php -q tree_list.php --list-rras
echo This will throw errors
php -q tree_list.php --list-nodes --tree-id=foo

clear
echo -----------------------
echo add and remove tree
echo -----------------------
php -q tree_create.php --help
php -q tree_create.php --item-type=graph --item-id=54 --user-id=3,1
php -q tree_list.php --list-tree --item-type=graph
php -q tree_delete.php --item-type=graph --item-id=54 --user-id=3,1
php -q tree_list.php --list-tree --item-type=graph

php -q tree_create.php --item-type=device --item-id=5 --user-id=3,1
php -q tree_list.php --list-tree --item-type=device
php -q tree_delete.php --item-type=device --item-id=5 --user-id=3,1
php -q tree_list.php --list-tree --item-type=device

php -q tree_create.php --item-type=graph_template --item-id=2 --user-id=3,1
php -q tree_list.php --list-tree --item-type=graph_template
php -q tree_delete.php --item-type=graph_template --item-id=2 --user-id=3,1
php -q tree_list.php --list-tree --item-type=graph_template


clear
echo -----------------------
echo update
echo -----------------------
php -q tree_create.php --tree-type=cg --device-id=1 --tree-template-id=11
php -q tree_list.php --tree-id=49

php -q tree_update.php --help
php -q tree_update.php --tree-id=49 --tree-title='New Title1'
php -q tree_list.php --tree-id=49

php -q tree_create.php --tree-type=cg --device-id=1 --tree-template-id=11 --force
php -q tree_list.php --tree-template-id=11
php -q tree_update.php --tree-template-id=11 --tree-title='New Title2'
php -q tree_list.php --tree-template-id=11

php -q tree_update.php --device-id=1 --tree-template-id=11 --tree-title='New Title3'
php -q tree_list.php --device-id=1 --tree-template-id=11

php -q tree_delete.php --device-id=1 --tree-template-id=11 --force
php -q tree_list.php --device-id=1
