<?php

defined('GILPATH') or define('GILPATH',dirname(__FILE__).'/gilFramework');

require(GILPATH.'/GilFunctions.php');
$gilConfig = require(GILPATH.'/GilConfig.php');

if($gilConfig['oo']) require(GILPATH.'/GilController.php');//加载OO模式控制器