<h2>pages:</h2>
<ul>
	<li><a href="/home">Home</a></li>
	<li><a href="/profile?user=<?php echo $_SESSION['username']; ?>">My Profile</a></li>
	<li><a href="/settings">Settings</a></li>
	<li><a href="/signout">Sign Out</a></li>
</ul>
<br>
<h2>statistics:</h2>
<ul>
	<li>Updates: <?php echo mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM updates WHERE author = '".$_SESSION['username']."'"))['0']; ?></li>
	<li>Following: <?php echo mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM follows WHERE follower = '".$_SESSION['username']."'"))['0']; ?></li>
	<li>Mentions: <?php echo mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM updates WHERE status LIKE '%@".$_SESSION['username']."%' OR reply IN(SELECT id FROM updates WHERE author = '".$_SESSION['username']."')"))['0']; ?></li>
</ul>