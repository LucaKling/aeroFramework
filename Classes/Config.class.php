<?php
class Config {
	function __construct() {
		// Loading arg
		foreach(func_get_arg(0) as $key => $value)
			$this->$key = $value;
		
		foreach(parse_ini_file($this->Dirname . '/Config/Main.config.php', true) as $key => $value)
			$this->$key = (object) $value;
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