<h2>pages:</h2>
<ul>
	<li><a href="/home">Home</a></li>
	<li><a href="/profile?user=<?php echo $_SESSION['username']; ?>">My Profile</a></li>
	<li><a href="/settings">Settings</a></li>
	<li><a href="/signout">Sign Out</a></li>
</ul>
<br><br>
<h2>statistics:</h2>
<ul>
	<li>Updates: <?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM updates WHERE author = '".$_SESSION['username']."'")); ?></li>
	<li>Following: <?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM follows WHERE follower = '".$_SESSION['username']."'")); ?></li>
	<li>Mentions: <?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM updates WHERE status LIKE '%@".$_SESSION['username']."%'")); ?></li>
</ul>