<?php
if(isset($_GET['user'])) {
	if(isset($_GET['confirm'])) {
		mysqli_query($conn, "UPDATE users SET fullname=null, password=null, quote=null, website=null, bg_color=null, text_color=null, meta_color=null, border_color=null, link_color=null, home=null, admin=null, banned=1 WHERE username = '".$_GET['user']."'");
		mysqli_query($conn, "DELETE FROM updates WHERE author = '".$_GET['user']."'");
		mysqli_query($conn, "DELETE FROM follows WHERE follower = '".$_GET['user']."'");
		mysqli_query($conn, "DELETE FROM follows WHERE following = '".$_GET['user']."'");
		unlink("images/profiles/".$_GET['user'].".gif");
		$_SESSION['alert'] = $_GET['user']." has been banned.";
		header('Location: /home');
	} else {
		echo '<script>
		if(confirm("Are you sure you want to ban this user?")) {
			window.location.replace("/admin/ban?user='.$_GET['user'].'&confirm=true");
		}
		</script>';
	}
}
?>