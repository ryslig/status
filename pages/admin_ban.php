<?php
if(isset($_GET['user']) && $_SESSION['admin'] == true) {
	if(isset($_GET['confirm'])) {
		mysqli_query($conn, "UPDATE users SET password=0, quote=null, website=null, bg_color='#000000', text_color='#000000', meta_color='#000000', border_color='#000000', link_color='#000000', home=0, admin=0, banned=1 WHERE username = '".$_GET['user']."'");
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