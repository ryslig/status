<?php
$timeline = new Timeline;
$timeline->type = "permalink";
$timeline->display(0);
?>
<style>
body {
	width: 650px;
	margin: auto;
	margin-top: 8em;
}	
table.timeline {
	font-size: 1.4em;
	margin: 0 auto;
}
.thumb {
	width: 60px;
	height: 60px;
}	
td {
	padding: 8px;
}
</style>
<?php
$sql = mysqli_fetch_array($conn->query("SELECT * FROM updates WHERE id = ".intval($_GET['id'])));
echo '<script>document.title = "'.$sql['author'].': '.htmlspecialchars($sql['status']).'";</script>';
?>