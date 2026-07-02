function onFavoriteSubmit(packID) {
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("favoriteButton").innerHTML = this.responseText;
		}
	};
	xmlhttp.open("GET", "toggleFavorite.php?id=" + packID, true);
	xmlhttp.send();
}
