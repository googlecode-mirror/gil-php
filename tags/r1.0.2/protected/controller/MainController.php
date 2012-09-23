<?php
class MainController{
	function actionIndex(){
		echo 'Hello, World!';//输出这句，证明执行Controller成功
		$helloModel = new HelloModel();
		echo $helloModel -> test();
		$deliverSomething = 'Hello View World!';
		return array('deliverSomething'=>$deliverSomething);
	}
} 
?>