<?php
	$body = file_get_contents('php://input');
	$json = json_decode($body, true);
	echo $json["test_param"];