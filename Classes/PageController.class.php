<?php
class PageController {
	public $Dirname;
	public $BaseURI;
	public $BasePageURI;
	public $HomeURI;
	public $Meta;
	public $MetaTitle;
	public $Head;
	public $NavElements;
	public $GlobalTitle;
	public $PageTitle;
	public $PageContent;
	public $FooterContents;
	public $BodyScripts;
	public $IsResponsible;
	public $ClassName;
	public $ClassHash;
	public $PublicFolder;
	public $PublicFolderURI;
	public $PrivateFolder;
	public $CacheFolder;
	public $TableBase;
	public $SettingsTable;
	
	final public function __construct() {
		// Loading arg
		foreach(func_get_arg(0) as $key => $value)
			$this->$key = $value;
		
		$this->BaseURI = $this->Protocol . '://' . $this->Domain . ((strlen($this->URIPath) > 0) ? '/' . $this->URIPath : '');
		$this->BasePageURI = $this->BaseURI . ((!$this->Rewrite) ? '/' . $this->Filename : '');
		$this->HomeURI = $this->BaseURI;
		$this->GlobalTitle = $this->Config->DV->GlobalTitle;
		$this->MetaTitle = $this->Config->DV->MetaTitle;
		$this->ClassName = get_class($this);
		$this->ClassHash = md5($this->ClassName . $this->Config->DV->Salt);
		$this->PublicFolder = $this->Dirname . DS . 'Public' . DS . $this->ClassHash;
		if(!is_dir($this->PublicFolder))
			mkdir($this->PublicFolder, 0777, true);
		$this->PublicFolderURI = $this->BaseURI . '/' . basename($this->PublicFolder);
		$this->PrivateFolder = $this->Dirname . DS . 'Private' . DS . md5($this->ClassHash . $this->Config->DV->Salt); // Extra security (Public- differs from Private Folder)
		if(!is_dir($this->PrivateFolder))
			mkdir($this->PrivateFolder, 0777, true);
		$this->CacheFolder = $this->Dirname . DS . 'Cache' . DS . md5($this->ClassHash . $this->Config->DV->Salt . $this->Config->DV->Salt); // Also extra security
		if(!is_dir($this->CacheFolder))
			mkdir($this->CacheFolder, 0777, true);
		$this->TableBase = $this->Config->Database->Prefix . $this->ClassName . '_';
		$this->SettingsTable = $this->TableBase . 'Settings';
		$this->WrapDatabaseConfig();
		
		// Run user-defined construct function
		$this->Construct();
		
		return true;
	}
	
	final public function __destruct() {
		// Run user-defined destruct function
		$this->Destruct();
		return true;
	}
	
	final public function PrintBaseURI() {
		echo $this->BaseURI;
		return true;
	}
	
	final public function PrintHomeURI() {
		echo $this->HomeURI;
		return true;
	}
	
	final public function RedirectInternal($URI, $StatusCode = 303, $IsPage = true) {
		if($this->ForceLowerCase)
			$URI = strtolower($URI);
		$URI = ((substr($URI, 0, 1) != '/') ? '/' . $URI : $URI);
		Header('Location: ' . (($IsPage) ? $this->BasePageURI : $this->BaseURI) . $URI, $StatusCode);
		exit(0);
		return true;
	}
	
	final public function RedirectExternal($URI, $StatusCode = 303) {
		Header('Location: ' . $URI, $StatusCode);
		exit(0);
		return true;
	}
	
	final public function WrapDatabaseConfig() {
		$SettingsTable = $this->SettingsTable;
		if($this->TableExists($SettingsTable)) {
			try {
				$this->Database->STMT = $this->Database->PDO->query('SELECT `Name`, `Value` FROM `' . $SettingsTable . '`');
				if($this->Database->STMT !== false) {
					$this->ControllerConfig = new stdClass;
					while($row = $this->Database->STMT->fetch(PDO::FETCH_ASSOC)) {
						$this->ControllerConfig->$row['Name'] = $row['Value'];
					}
				} else
					throw new PDOException(implode(', ', $this->Database->PDO->errorInfo()));
			} catch(PDOException $e) {
				echo 'An error occurred while fetching data from database: ' . $e->getMessage();
				exit(1);
			}
		} else {
			try {
				$SQL = <<<EOF
CREATE TABLE IF NOT EXISTS `$SettingsTable` (
  `ID` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Value` longtext COLLATE utf8_unicode_ci,
  `Modified` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Created` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
ALTER TABLE `$SettingsTable`
  ADD PRIMARY KEY (`ID`), ADD UNIQUE KEY `Name` (`Name`);
ALTER TABLE `$SettingsTable`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
EOF;
				$this->Database->STMT = $this->Database->PDO->query($SQL);
				if($this->Database->STMT !== false) {
					$this->WrapDatabaseConfig();
					return true;
				} else
					throw new PDOException(implode(', ', $this->Database->PDO->errorInfo()));
			} catch(PDOException $e) {
				echo 'An error occurred while creating table: ' . $e->getMessage();
				exit(1);
			}
		}
		return false;
	}
	
	final public function SetSettingValue($SettingName, $SettingValue) {
		$SQL = '';
		if($this->SettingExists($SettingName))
			$SQL .= 'UPDATE `' . $this->SettingsTable . '` SET `Name` = ' . $this->Database->PDO->quote($SettingName) . ', `Value` = ' . $this->Database->PDO->quote($SettingValue) . ', `Modified` = ' . $this->Database->PDO->quote(microtime(true)) . ' WHERE `Name` = ' . $this->Database->PDO->quote($SettingName);
		else
			$SQL .= 'INSERT INTO `' . $this->SettingsTable . '` SET `Name` = ' . $this->Database->PDO->quote($SettingName) . ', `Value` = ' . $this->Database->PDO->quote($SettingValue) . ', `Modified` = null, `Created` = ' . $this->Database->PDO->quote(microtime(true));
		try {
			if($this->Database->PDO->query($SQL))
				return true;
		} catch(PDOException $e) {
				echo 'An error occurred while creating table: ' . $e->getMessage();
				exit(1);
		}
		return false;
	}
	
	final public function GetSetting($SettingName, $Column = 'Value') {
		if($this->SettingExists($SettingName)) {
			try {
				$this->Database->STMT = $this->Database->PDO->query('SELECT `' . $Column . '` FROM `' . $this->SettingsTable . '` WHERE `Name` = ' . $this->Database->PDO->quote($SettingName) . ' LIMIT 1');
				if($this->Database->STMT !== false) {
					return $this->Database->STMT->fetch(PDO::FETCH_OBJ)->{$Column};
			} else
				throw new PDOException(implode(', ', $this->Database->PDO->errorInfo()));
			} catch(PDOException $e) {
				echo 'An error occurred while fetching setting: ' . $e->getMessage();
				exit(1);
			}
		}
		return false;
	}
	
	final public function GetSettings($Column = '*') {
		try {
			$this->Database->STMT = $this->Database->PDO->query('SELECT ' . $Column . ' FROM `' . $this->SettingsTable . '` ORDER BY `ID` ASC');
			if($this->Database->STMT !== false)
				return $this->Database->STMT->fetchAll();
		else
			throw new PDOException(implode(', ', $this->Database->PDO->errorInfo()));
		} catch(PDOException $e) {
			echo 'An error occurred while fetching settings: ' . $e->getMessage();
			exit(1);
		}
	}
	
	final public function SettingExists($SettingName) {
		if($this->RowValueExists($this->SettingsTable, 'Name', $SettingName))
			return true;
		else
			return false;
	}
	
	final public function RowValueExists($TableName, $RowName, $RowValue) { // See: http://stackoverflow.com/questions/1676551/best-way-to-test-if-a-row-exists-in-a-mysql-table & http://stackoverflow.com/questions/8315835/check-if-username-exists-using-php-pdo
		try {
			$this->Database->STMT = $this->Database->PDO->query('SELECT EXISTS(SELECT 1 FROM `' . $TableName . '` WHERE `' . $RowName . '` = ' . $this->Database->PDO->quote($RowValue) . ' LIMIT 1)');
			if($this->Database->STMT !== false) {
				if($this->Database->STMT->fetchColumn())
					return true;
				else
					return false;
			} else
					throw new PDOException(implode(', ', $this->Database->PDO->errorInfo()));
		} catch(PDOException $e) {
				echo 'An error occurred while checking for row: ' . $e->getMessage();
				exit(1);
		}
		return false;
	}
	
	final public function TableExists($TableName) { // See: http://stackoverflow.com/questions/8829102/mysql-check-if-table-exists-without-using-select-from
		$this->Database->STMT = $this->Database->PDO->query('SELECT 1 FROM `' . $TableName . '` LIMIT 1');
		if($this->Database->STMT !== false)
			return true;
		else
			return false;
	}
	
	public static function Priority() {
		return (INT) 0;
	}
	
	public static function IsResponsible($Page, $Config, $Database) {
		return true;
	}
	
	public function Construct() {
		return true;
	}
	
	public function Destruct() {
		return true;
	}
	
	public function PrintMeta() {
		echo $this->Meta;
		return true;
	}
	
	public function PrintMetaTitle() {
		echo $this->MetaTitle;
		return true;
	}
	
	public function PrintHead() {
		echo $this->Head;
		return true;
	}
	
	public function PrintNavElements() {
		echo $this->NavElements;
		return true;
	}
	
	public function PrintGlobalTitle() {
		echo $this->GlobalTitle;
		return true;
	}
	
	public function PrintPageTitle() {
		echo $this->PageTitle;
		return true;
	}
	
	public function PrintPageContent() {
		echo $this->PageContent;
		return true;
	}
	
	public function PrintFooterContents() {
		echo $this->FooterContents;
		return true;
	}
	
	public function PrintBodyScripts() {
		echo $this->BodyScripts;
		return true;
	}
}