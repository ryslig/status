var ajaxWindow;

function ajax_popup(loc) {
	ajaxWindow = window.open(loc, "_blank");
	ajaxWindow.close();
}
function delete_status(id) {
	if (confirm("Are you sure you want to delete your status?")) {
		ajax_popup("/ajax/delete?id="+id);
		location.reload();
	}
}

function reply(id) {
	var update = prompt("What would you like to reply with?");
	if(update == null || update == "") {
		// user cancelled the promt
	} else {
		document.getElementById('status_retrozilla').value = update;
		document.getElementById('id_retrozilla').value = id;
		document.getElementById("form_retrozilla").submit();
	}
}

function follow_user(user) {
	ajax_popup("/ajax/follow?user="+user);
	location.reload();
}

function unfollow_user(user) {
	if (confirm("Are you sure you want to unfollow this user?")) {
		ajax_popup("/ajax/unfollow?user="+user);
		location.reload();
	}
}