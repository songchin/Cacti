$(function () {

	Panel = {};

	Panel.jsTree = $.tree_create();
	Panel.jsTree.init($("#graph_tree .tree"),{
		data : { type : "json", async : true, url : "lib/ajax/get_graph_tree_items.php?type=list" },
		languages : [ "en" ],
		callback : {
			onselect : function(n) {
				if(n.id) Panel.loadContent(n.id);
			},
			onload : function (t) {
				//t.select_branch(t.container.find("li:eq(0)"));
			}
		}
	});
	Panel.creating = 0;

	// Functions
	Panel.loadContent = function(id) {
		$.get("lib/ajax/get_graph_tree_content.php?id=" + id, function (data) {
			$("#graphs").html(data);
		});
	}
});

