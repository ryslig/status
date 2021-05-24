<?php
	echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset
	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
	http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<url>
	<loc>https://status.ryslig.xyz/</loc>
	<changefreq>daily</changefreq>
	<priority>1.00</priority>
</url>
<url>
	<loc>https://status.ryslig.xyz/signup</loc>
	<changefreq>monthly</changefreq>
	<priority>0.8</priority>
</url>
<url>
	<loc>https://status.ryslig.xyz/signin</loc>
	<changefreq>monthly</changefreq>
	<priority>0.8</priority>
</url>
<?php

$sql = "SELECT `username` FROM `users` ORDER BY `username`";

if ($result = mysqli_query($conn, $sql)) {
	while ($row = mysqli_fetch_assoc($result)) {
		$time = mysqli_fetch_array(mysqli_query($conn, "SELECT `date` FROM `updates` WHERE `author` = '".$row['username']."' ORDER BY CAST(id as SIGNED INTEGER) DESC LIMIT 1"), MYSQLI_ASSOC);
		print "<url>
	<loc>https://status.ryslig.xyz/profile?user=".$row['username']."</loc>
	<lastmod>".date("c", strtotime($time['date']))."</lastmod>
	<changefreq>daily</changefreq>
	<priority>0.5</priority>
</url>\n";
	}
	mysqli_free_result($result);
}

?>
</urlset>