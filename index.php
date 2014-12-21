<?php
$File = __FILE__;
$Filename = basename($File);
$Dirname = dirname($File);
foreach(glob($Dirname . '/Classes/*.class.php', GLOB_NOSORT) as $Class)
	require_once $Class;
unset($Class);
new Core(array('Filename' => $Filename, 'Dirname' => $Dirname));