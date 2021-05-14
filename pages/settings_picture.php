<?php
if(isset($_FILES['thumb']['size'])) {
	if(!empty($_FILES['thumb']['size'])) {
		if(getimagesize($_FILES['thumb']['tmp_name']) !== false) {
			if($_FILES['thumb']['size'] < 700000) {
				$mime = mime_content_type($_FILES['thumb']['tmp_name']);
				if($mime == 'image/gif' or 'image/jpeg' or 'image/png') {
					$destination = 'images/profiles/'.$_SESSION['username'].'.gif';
					if($mime !== 'image/gif') {
						if($mime == 'image/jpeg') $image = imagecreatefromjpeg($_FILES['thumb']['tmp_name']);
						if($mime == 'image/png') $image = imagecreatefrompng($_FILES['thumb']['tmp_name']);
						imagegif($image, $destination);
						$_SESSION['alert'] = "Image successfully updated!";
					} else {
						move_uploaded_file($_FILES['thumb']['tmp_name'], $destination);
						$_SESSION['alert'] = "Image successfully updated!";
					}
				} else { $_SESSION['alert'] = "Please upload a valid image"; }
			} else { $_SESSION['alert'] = "Your file is too large!"; }
		} else { $_SESSION['alert'] = "Please upload a valid image."; }
	} else { $_SESSION['alert'] = "Please upload a valid image."; }
	header('Location: /settings/picture');
}
?>
<h3>Change Profile Image:</h3>
<form method="post" action="/settings/picture" enctype="multipart/form-data">
	<label for="thumb">Upload Image:</label>
	<input type="file" name="thumb">
	<input type="submit" value="Save">
</form>
<p>Maximum size of 700k. JPEGs, PNGs, and GIFs only.</p>