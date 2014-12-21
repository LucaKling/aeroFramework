<?php
final class Database {
	// DB vars
	public $DBDriver;
	public $DBPath;
	public $DBHost;
	public $DBPort;
	public $DBSocket;
	public $DBName;
	public $DBUser;
	public $DBPassword;
	public $DBCharset;
	public $DBOptions;
	public $DSN;
	public $PDO;
	public $STMT;

	function __construct() {
		// Loading arg
		foreach(func_get_arg(0) as $key => $value)
			$this->$key = $value;
		
		// Setting Database vars
		$this->DBDriver = (($this->Config->Database->Driver == '') ? 'mysql' : $this->Config->Database->Driver); // default: mysql
		$this->DBPath = (($this->Config->Database->Path == '') ? null : $this->Config->Database->Path); // default: null
		$this->DBHost = (($this->Config->Database->Host == '') ? 'localhost' : $this->Config->Database->Host); // default: localhost
		$this->DBPort = (($this->Config->Database->Port == '') ? 3306 : $this->Config->Database->Port); // default: 3306
		$this->DBSocket = (($this->Config->Database->Socket == '') ? null : $this->Config->Database->Socket); // default: null
		$this->DBName = (($this->Config->Database->Name == '') ? null : $this->Config->Database->Name); // default: null
		$this->DBUser = (($this->Config->Database->User == '') ? null : $this->Config->Database->User); // default: null
		$this->DBPassword = (($this->Config->Database->Password == '') ? null : $this->Config->Database->Password); // default: null
		$this->DBCharset = (($this->Config->Database->Charset == '') ? 'utf8' : $this->Config->Database->Charset); // default: utf8
		$this->DBOptions = (($this->Config->Database->Options == '') ? array() : $this->Config->Database->Options); // default: array
		
		// Initiating Database Connection
		$this->InitiateDBConnection();
	}
	
	function InitiateDBConnection() {
		$this->DBOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $this->DBCharset;
		try {
			$this->DSN = '';
			$this->DSN .= $this->DBDriver . ':';
			if($this->DBPath != null)
				$this->DSN .= $this->DBPath;
			else if($this->DBSocket != null)
				$this->DSN .= 'unix_socket=' . $this->DBSocket;
			else
				$this->DSN .= 'host=' . $this->DBHost . ';' . 'port=' . $this->DBPort;
			$this->DSN .= ';dbname=' . $this->DBName . ';charset=' . $this->DBCharset;
			$this->PDO = new PDO($this->DSN, $this->DBUser, $this->DBPassword, $this->DBOptions);
		} catch(PDOException $e) {
			echo 'Error while connecting to database: ' . $e->getMessage();
			exit(1);
			return false;
		}
		return true;
	}
	
	function CloseDBConnection() {
		if(($this->STMT = null) && ($this->PDO = null))
			return true;
		return false;
	}
	
	function __destruct() {
		if($this->closeDBConnection())
			return true;
		return false;
	}
}