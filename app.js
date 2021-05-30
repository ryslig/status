if(navigator.userAgent.indexOf("RetroZilla") !== -1) {
	var retrozilla = true;
} else {
	if (window.XMLHttpRequest) {
		xhttp = new XMLHttpRequest();
	} else if(ActiveXObject("Microsoft.XMLHTTP")) {
		xhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
}

function ajax_popup(loc) {
	ajaxWindow = window.open(loc, "_blank");
	ajaxWindow.close();
}
	
function delete_status(id) {
	if (confirm("Are you sure you want to delete your status?")) {
		if(retrozilla == true) {
			ajax_popup("/ajax/delete?id="+id);
		} else {
			xhttp.open("GET", "/ajax/delete?id="+id, false);
			xhttp.send();
		}
		location.reload();
	}
}

function reply(id) {
	var update = prompt("What would you like to reply with?");
	if(update == null || update == "") {
		// user cancelled the promt
	} else {
		if(retrozilla == true) {
			document.getElementById('status_retrozilla').value = update;
			document.getElementById('id_retrozilla').value = id;
			document.getElementById("form_retrozilla").submit();
		} else {
			// setting up post request
			data = new FormData();
			data.set('status', update);
			data.set('reply', id);
			// send it, bruh!!
			xhttp.open("POST", '/home', false);
			xhttp.send(data);
			location.reload();
		}
	}
}

function follow_user(user) {
	if(retrozilla == true) {
		ajax_popup("/ajax/follow?user="+user);
	} else {
		xhttp.open("GET", "/ajax/follow?user="+user, false);
		xhttp.send();
	}
	location.reload();
}

function unfollow_user(user) {
	if (confirm("Are you sure you want to unfollow this user?")) {
		if(retrozilla == true) {
			ajax_popup("/ajax/unfollow?user="+user);
		} else {
			xhttp.open("GET", "/ajax/unfollow?user="+user, false);
			xhttp.send();
		}
		location.reload();
	}
}

function count_it() {
	document.getElementById('counter').innerHTML = 140 - document.getElementById('status').value.length;
	if(document.getElementById('status').value.length > 140) {
		document.getElementById('counter').style.color = "red";
	} else {
		document.getElementById('counter').style.color = "inherit";
	}
}

function reset_theme() {
	if (confirm("Are you sure you want to reset your theme colors?")) {
		document.getElementById("bg_color").value = "#FFFFFF";
		document.getElementById("text_color").value = "#000000";
		document.getElementById("meta_color").value = "#808080";
		document.getElementById("border_color").value = "#D3D3D3";
		document.getElementById("link_color").value = "#0000FF";
	}
}