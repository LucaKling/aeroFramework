<?php
final class Database {
	// DB vars
	public $DBDriver;
	public $DBHost;
	public $DBPort;
	public $DBSocket;
	public $DBName;
	public $DBUser;
	public $DBPassword;
	public $DBCharset;
	public $DBOptions;
	public $PDO;
	public $STMT;

	function __construct() {
		// Loading arg
		foreach(func_get_arg(0) as $key => $value)
			$this->$key = $value;
		
		// Setting Database vars
		$this->DBDriver = $this->Config->Database->Driver;
		$this->DBHost = $this->Config->Database->Host;
		$this->DBPort = $this->Config->Database->Port;
		$this->DBSocket = (($this->Config->Database->Socket == '') ? false : $this->Config->Database->Socket);
		$this->DBName = $this->Config->Database->Name;
		$this->DBUser = $this->Config->Database->User;
		$this->DBPassword = $this->Config->Database->Password;
		$this->DBCharset = $this->Config->Database->Charset;
		$this->DBOptions = $this->Config->Database->Options;
		
		// Initiating Database Connection
		$this->InitiateDBConnection();
	}
	
	function InitiateDBConnection() {
		$this->DBOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $this->DBCharset;
		try {
			$this->PDO = new PDO($this->DBDriver . ':' . (($this->DBSocket) ? 'unix_socket=' . $this->DBSocket : 'host=' . $this->DBHost . ';' . 'port=' . $this->DBPort) . ';dbname=' . $this->DBName . ';charset=' . $this->DBCharset, $this->DBUser, $this->DBPassword, $this->DBOptions);
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