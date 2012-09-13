<?php
class MainController{
	function actionIndex(){
		echo 'Hello, World!';
		$helloModel = new HelloModel();
		echo $helloModel -> test();
		$deliverSomething = 'Hello View World!';
		return array('deliverSomething'=>$deliverSomething);
	}
} 
?>