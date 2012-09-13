<?php
final class GilController {
	public function __construct() {
		$routeUrl = isset($_GET['r']) ? $_GET['r'] : '';
		list($controller, $action) = self::_routeParser($routeUrl);
		self::gilRun($controller, $action);
	}
	
	static public function gilRun($controller, $action) {
		if (file_exists ( APPCONTROLLERPATH . "/" . ucfirst ( $controller ) . "Controller.php" ))
			require_once (APPCONTROLLERPATH . "/" . ucfirst ( $controller ) . "Controller.php");
		else
			die ( "Error:Cannot found controller!" );
		$mClass = new ReflectionClass ( ucfirst ( $controller ) . 'Controller' );
		$mActionName = 'action' . ucfirst ( $action );
		if ($mClass->hasMethod ( $mActionName ))
			$renderDatas = $mClass->newInstance ()->$mActionName ();
		else
			die ( "Error:Cannot found {$controller}/{$action}!" );
		if ($renderDatas ['_show'] === false)
			return false;
		else
			return $this->render ( $controller, $action, $renderDatas );
	}
	
	static public function render($renderController, $renderAction, $renderDatas) {
		if (file_exists ( APPVIEWPATH . '/' . strtolower ( $renderController ) . '/' . strtolower ( $renderAction ) . '.php' )) {
			if (is_array ( $renderDatas ))
				extract ( $renderDatas );
			unset ( $renderDatas );
			include ( APPVIEWPATH . '/' . strtolower ( $renderController ) . '/' . strtolower ( $renderAction ) . '.php' );
		}
	
	}
	
	static private function _routeParser($routeUrl){
		global $gilConfig;
		$routeUrl = empty($routeUrl) ? $gilConfig['oo_config']['defaultController'].'/'.$gilConfig['oo_config']['defaultAction'] : $routeUrl;
        $routeArray = explode('/',$routeUrl);
        $mController=$routeArray[0];
        $mAction = isset($routeArray[1]) ? $routeArray[1] : $gilConfig['oo_config']['defaultAction'];
        return array($mController,$mAction);
	}
}