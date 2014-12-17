<?php
$Dirname = dirname(__FILE__);
foreach(glob($Dirname . '/Classes/*.class.php') as $Class)
	require_once $Class;
unset($Class);
new Core(array('Dirname' => $Dirname));