<?php
class Config {
	function __construct() {
		// Loading arg
		foreach(func_get_arg(0) as $key => $value)
			$this->$key = $value;
		foreach(glob($this->Dirname . '/Config/*.config.php', GLOB_NOSORT) as $File) {
			$Name = explode('.', basename($File));
			$Name = $Name[0];
			$this->$Name = (object) parse_ini_file($File);
		}
		return true;
	}
	
	function WrapDatabaseConfig() {
		// Loading arg
		foreach(func_get_arg(0) as $key => $value)
			$$key = $value;
		$SettingsTable = $this->Database->Prefix . $this->Core->Prefix . $this->Core->SettingsTable;
		try {
			$Database->STMT = $Database->PDO->query('SELECT `Name`, `Value` FROM `' . $SettingsTable . '`');
			if($Database->STMT !== false) {
				$this->DV = new stdClass;
				while($row = $Database->STMT->fetch(PDO::FETCH_ASSOC)) {
					$this->DV->$row['Name'] = $row['Value'];
				}
			} else
				throw new PDOException(implode(', ', $Database->PDO->errorInfo()));
		} catch(PDOException $e) {
			echo 'An error occurred while fetching data from database: ' . $e->getMessage();
			exit(1);
		}
	}
	
	function __destruct() {
		return true;
	}
}