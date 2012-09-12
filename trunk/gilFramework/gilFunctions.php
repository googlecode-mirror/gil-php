<?php
function __autoload($mClassName){
	$mFileName = GILPATH."/".$mClassName.".php";
	if(file_exists($mFileName))
		require($mFileName);
	else die("Error:Cannot found model {$mClassName}");
}