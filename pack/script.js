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

function submitReport(packID) {
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			alert("Report sent sucessfully! Thank you!");
		}
	};
	var reasonStr = "TOADD";
	//Todo: Ask the user for the reasoning
	xmlhttp.open("GET", "report.php?id=" + packID + "&reason=" + reasonStr, true);
	xmlhttp.send();
}