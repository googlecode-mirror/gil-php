<?php

defined('GILPATH') or define('GILPATH',dirname(__FILE__).'/');

require(GILPATH.'/GilFunctions.php');

if(isset($gilConfig)) $gilConfig = array_merge(require(GILPATH.'/GilConfig.php'), $gilConfig);
else $gilConfig = require(GILPATH.'/GilConfig.php');

GilDB::$_gilConfig = $gilConfig;

if($gilConfig['oo']) GilController::__init();