<?php
function __autoload($mClassName){
	if(file_exists(GILPATH."/".$mClassName.".php")){
		require(GILPATH."/".$mClassName.".php");//Load Core Dir first
	}
	elseif(file_exists(GILPATH."/extension/".$mClassName.".php")){
		require(GILPATH."/extension/".$mClassName.".php");//Load Extension Dir second
	}
	elseif(file_exists(APPMODELPATH."/".$mClassName.".php")){
		require(APPMODELPATH."/".$mClassName.".php");//Load User Defined Model Dir final
	}
	else{
		die("Error:Cannot found model {$mClassName}");
	}
}