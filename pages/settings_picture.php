<?php
// functions lifted from https://stackoverflow.com/questions/6891352/crop-image-from-center-php/49851547#49851547
function cropAlign($image, $cropWidth, $cropHeight, $horizontalAlign = 'center', $verticalAlign = 'middle') {
    $width = imagesx($image);
    $height = imagesy($image);
    $horizontalAlignPixels = calculatePixelsForAlign($width, $cropWidth, $horizontalAlign);
    $verticalAlignPixels = calculatePixelsForAlign($height, $cropHeight, $verticalAlign);
    return imageCrop($image, [
        'x' => $horizontalAlignPixels[0],
        'y' => $verticalAlignPixels[0],
        'width' => $horizontalAlignPixels[1],
        'height' => $verticalAlignPixels[1]
    ]);
}

function calculatePixelsForAlign($imageSize, $cropSize, $align) {
    switch ($align) {
        case 'left':
        case 'top':
            return [0, min($cropSize, $imageSize)];
        case 'right':
        case 'bottom':
            return [max(0, $imageSize - $cropSize), min($cropSize, $imageSize)];
        case 'center':
        case 'middle':
            return [
                max(0, floor(($imageSize / 2) - ($cropSize / 2))),
                min($cropSize, $imageSize),
            ];
        default: return [0, $imageSize];
    }
}

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
						if(imagesx($image) <= imagesy($image)) {
							$image = imagescale($image, 100, -1, IMG_BICUBIC);
						} else {
							$image = imagescale($image, -1, 100, IMG_BICUBIC);
						}
						$image = cropAlign($image, 100, 100);
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