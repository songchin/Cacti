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
php -q tree_create.php --type=tree --name='cli test' --sort-method=manual
php -q tree_list.php --list-trees

clear
echo -----------------------
echo update trees
echo -----------------------
php -q tree_update.php --help
php -q tree_create.php --type=tree --name='cli test' --sort-method=manual
php -q tree_list.php --list-trees
php -q tree_update.php --type=tree --id=8 --name='New Title1'
php -q tree_list.php --list-trees
php -q tree_update.php --type=tree --id=8 --sort-method=natural
php -q tree_list.php --list-trees
php -q tree_delete.php --type=tree --id=8
php -q tree_list.php --list-trees


clear
echo -----------------------
echo add and remove tree nodes
echo -----------------------
php -q tree_create.php --type=node --tree-id=1 --node-type=header --name='Header Manual' --sort-method=manual
php -q tree_list.php  --list-nodes --tree-id=1 --node-type=header
php -q tree_create.php --type=node --tree-id=1 --node-type=header --name='Header Alpha' --sort-method=alpha --parent-node=40
php -q tree_list.php  --list-nodes --tree-id=1 --node-type=header
php -q tree_create.php --type=node --tree-id=1 --node-type=header --name='Header Numeric' --sort-method=numeric --parent-node=44
php -q tree_list.php  --list-nodes --tree-id=1 --node-type=header
php -q tree_create.php --type=node --tree-id=1 --node-type=header --name='Header Natural' --sort-children-type=4 --parent-node=45
php -q tree_list.php  --list-nodes --tree-id=1 --node-type=header

php -q tree_create.php --type=node --tree-id=1 --node-type=device --device-id=1 --device-group-type=2
php -q tree_create.php --type=node --tree-id=1 --node-type=device --device-id=5 --parent-node=46 --device-group-type=1
php -q tree_list.php  --list-nodes --tree-id=1 --node-type=device
php -q tree_list.php  --list-nodes --tree-id=1

php -q tree_create.php --type=node --tree-id=1 --node-type=graph --graph-id=64 --sort-children-type=2 --rra-id=2
php -q tree_create.php --type=node --tree-id=1 --node-type=graph --graph-id=65 --parent-node=46
php -q tree_list.php  --list-nodes --tree-id=1 --node-type=graph
php -q tree_list.php  --list-nodes --tree-id=1

php -q tree_delete.php --type=tree --id=5
php -q tree_list.php  --list-nodes --tree-id=1
php -q tree_delete.php --type=node --id=46
php -q tree_list.php  --list-nodes --tree-id=1
php -q tree_delete.php --type=node --id=40
php -q tree_list.php  --list-nodes --tree-id=1
php -q tree_delete.php --type=node --id=25
php -q tree_delete.php --type=node --id=50
php -q tree_list.php  --list-nodes --tree-id=1


clear
echo -----------------------
echo update tree nodes
echo -----------------------
php -q tree_create.php --type=node --tree-id=1 --node-type=header --name='Header Manual' --sort-method=manual
php -q tree_update.php --type=node --id=54 --name='Header Manual Update->Alpha' --sort-method=alpha
php -q tree_list.php  --list-nodes --tree-id=1 --node-type=header

php -q tree_create.php --type=node --tree-id=1 --node-type=graph --graph-id=64 --rra-id=2
php -q tree_list.php  --list-nodes --tree-id=1 --node-type=graph
php -q tree_update.php --type=node --id=55 --rra-id=3
php -q tree_list.php  --list-nodes --tree-id=1 --node-type=graph

php -q tree_create.php --type=node --tree-id=1 --node-type=device --device-id=1 --device-group-type=2
php -q tree_list.php  --list-nodes --tree-id=1 --node-type=device
php -q tree_update.php --type=node --id=56 --device-group-type=1
php -q tree_list.php  --list-nodes --tree-id=1 --node-type=device

php -q tree_create.php --type=node --tree-id=1 --node-type=device --device-id=5 --parent-node=54 --device-group-type=1
php -q tree_list.php  --list-nodes --tree-id=1
php -q tree_update.php --type=node --id=56 --parent-node=54
php -q tree_list.php  --list-nodes --tree-id=1
