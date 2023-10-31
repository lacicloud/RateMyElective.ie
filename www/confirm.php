<?php 
require("../functions.php");
$api = new RateMyElective;

if (isset($_GET["key"]) and !empty($_GET["key"]) and $_GET["key"] !== "1") {
	$result = $api->confirmUser($_GET["key"]);
	if ($result == "ERR_CONFIRM_OK") {
		header("Location: /index.php?confirm=ok");
	} else {
		header("Location: /index.php?confirm=ok");
	}
}

?>
