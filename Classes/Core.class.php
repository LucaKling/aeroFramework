<?php
class Core {
	// Config vars
	public $Config;
	
	// Dir and file handling vars
	public $Filename;
	public $Dirname;
	
	// Template vars
	public $DefaultTemplate;
	public $DefaultSubTemplate;
	public $ActiveTemplate;
	public $ActiveSubTemplate;
	
	// Misc vars
	public $PageController;
	public $DocumentRoot;
	public $Protocol;
	public $Domain;
	public $URIPath;
	public $Rewrite;
	public $RequestURI;
	public $ForceLowerCase;
	public $Database;
	public $OS;
	
	function __construct() {
		// Loading arg
		foreach(func_get_arg(0) as $key => $value)
			$this->$key = $value;
		
		if(strtolower(substr(php_uname('s'), 0, 3)) == 'win') { // See: http://stackoverflow.com/questions/1482260/how-to-get-the-os-on-which-php-is-running
			$this->OS = 'windows';
			define('DS', '\\');
		} else {
			$this->OS = 'linux';
			define('DS', '/');
		}
		
		$this->Config = new Config(array('Dirname' => $this->Dirname));
		$this->Database = new Database(array('Config' => $this->Config));
		$this->Config->WrapDatabaseConfig(array('Database' => $this->Database));
		
		// Checking Domain, path and protocol
		$this->DocumentRoot = $_SERVER['DOCUMENT_ROOT'];
		if(in_array(substr($this->DocumentRoot, -1), array('/', '\\'))) {
			$this->DocumentRoot = substr($this->DocumentRoot, 0, -1);
		}
		$this->Protocol = (($this->Config->DV->{'Reachability.Protocol'} != null) ? $this->Config->DV->{'Reachability.Protocol'} : ((isset($_SERVER['HTTPS'])) ? (($_SERVER['HTTPS'] != null) ? 'https' : 'http') : 'http'));
		$this->Domain = (($this->Config->DV->{'Reachability.Domain'} != null) ? $this->Config->DV->{'Reachability.Domain'} : $_SERVER['HTTP_HOST']);
		$this->URIPath = $this->Config->DV->{'Reachability.URIPath'};
		if($this->URIPath == null || $this->URIPath == '/')
			$this->URIPath = substr(str_replace('\\', '/', $this->Dirname), strlen($this->DocumentRoot)+1);
		else {
			if(substr($this->URIPath, 0, 1) == '/')
				$this->URIPath = substr($this->URIPath, 1);
			if(substr($this->URIPath, -1) == '/')
				$this->URIPath = substr($this->URIPath, 0, -1);
		}
		$this->Rewrite = false;
		if(function_exists('apache_get_modules')) {
			if(in_array('mod_rewrite', apache_get_modules()))
				$this->Rewrite = true;
		} else if($this->Config->DV->{'Reachability.ForceRewrite'}) {
			$this->Rewrite = true;
		}
		if($this->Rewrite) {
			$this->RequestURI = ((empty($this->URIPath)) ? $_SERVER['REQUEST_URI'] : substr($_SERVER['REQUEST_URI'], strlen($this->URIPath)+1));
		} else {
			$this->RequestURI = $_SERVER['PATH_INFO'];
		}
		$this->RequestURI = explode('/', ((strlen($this->RequestURI) > 1 && substr($this->RequestURI, -1) == '/') ? substr($this->RequestURI, 0, -1) : $this->RequestURI));
		$this->ForceLowerCase = (($this->Config->DV->{'Reachability.ForceLowerCase'}) ? true : false);
		
		// Settings template vars
		$this->DefaultTemplate = $this->Config->DV->{'Template.Default'};
		$this->DefaultSubTemplate = $this->Config->DV->{'Template.DefaultSub'};
		$this->ActiveTemplate = $this->DefaultTemplate;
		$this->ActiveSubTemplate = $this->DefaultSubTemplate;
		
		$this->GetPageController($this->RequestURI[1]);
		
		$this->RenderTemplate(array('PageController' => $this->PageController, 'ActiveTemplate' => $this->PageController->ActiveTemplate, 'ActiveSubTemplate' => $this->PageController->ActiveSubTemplate));
		
		return true;
	}
	
	function GetPageController($Page, $Index = 0) {
		$Page = ((empty($Page)) ? 'Index' : ucfirst($Page));
		$PageController['Files'] = array();
		$PageController['Advanced'] = array();
		$PageController['Priority'] = array();
		$PageController['Simple'] = array();
		$PageController['Files'] = $this->GetFiles($this->Dirname . DS . 'PageController' . DS, '/[\/\\\][a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*Controller(?:|Advanced).php/');
		foreach($PageController['Files'] as $Key => $Value) {
			$Basename = basename($Value);
			require_once $Value;
			$Value = substr($Basename, 0, -4);
			if(substr($Value, -8) == 'Advanced') {
				$PageController['Advanced'][$Key] = $Value;
				$PageController['Priority'][$Key] = (INT) $Value::Priority();
			} else
				$PageController['Simple'][] = $Value;
		}
		unset($Key, $Value, $Basename);
		if(in_array($Page . 'Controller', $PageController['Simple'])) {
			$Controller = $Page . 'Controller';
			$this->PageController = new $Controller(array('Config' => $this->Config, 'Filename' => $this->Filename, 'Dirname' => $this->Dirname, 'Protocol' => $this->Protocol, 'Domain' => $this->Domain, 'URIPath' => $this->URIPath, 'Rewrite' => $this->Rewrite, 'RequestURI' => $this->RequestURI, 'ForceLowerCase' => $this->ForceLowerCase, 'Database' => $this->Database, 'ActiveTemplate' => $this->ActiveTemplate, 'DefaultTemplate' => $this->DefaultTemplate, 'ActiveSubTemplate' => $this->ActiveSubTemplate, 'DefaultSubTemplate' => $this->DefaultSubTemplate));
			return true;
		} else {
			arsort($PageController['Priority'], SORT_NATURAL);
			foreach($PageController['Priority'] as $Key => $Value) {
				$Controller = $PageController['Advanced'][$Key];
				if($Controller::IsResponsible($Page, $this->Config, $this->Database)) {
					$this->PageController = new $Controller(array('Config' => $this->Config, 'Filename' => $this->Filename, 'Dirname' => $this->Dirname, 'Protocol' => $this->Protocol, 'Domain' => $this->Domain, 'URIPath' => $this->URIPath, 'Rewrite' => $this->Rewrite, 'RequestURI' => $this->RequestURI, 'ForceLowerCase' => $this->ForceLowerCase, 'Database' => $this->Database, 'ActiveTemplate' => $this->ActiveTemplate, 'DefaultTemplate' => $this->DefaultTemplate, 'ActiveSubTemplate' => $this->ActiveSubTemplate, 'DefaultSubTemplate' => $this->DefaultSubTemplate));
					return true;
				}
			}
		}
		echo 'Fatal error: Could not find responsible controller!';
		exit(1);
		return false;
	}
	
	function RenderTemplate() {
		// Loading arg
		foreach(func_get_arg(0) as $key => $value)
			$$key = $value;
		
		if($ActiveSubTemplate != null) {
			if(is_file($this->Dirname . DS . 'Templates' . DS . $ActiveTemplate . DS . $ActiveSubTemplate . '.php')) {
				require_once $this->Dirname . DS . 'Templates' . DS . $ActiveTemplate . DS . $ActiveSubTemplate . '.php';
				return true;
			} else {
				echo 'Fatal error: Could not find responsible template!';
				exit(1);
			}
		} else {
			if(is_file($this->Dirname . DS . 'Templates' . DS . $ActiveTemplate . '.php')) {
				require_once $this->Dirname . DS . 'Templates' . DS . $ActiveTemplate . '.php';
				return true;
			} else if(is_file($this->Dirname . DS . 'Templates' . DS . $ActiveTemplate . DS . 'Template.php')) {
				require_once $this->Dirname . DS . 'Templates' . DS . $ActiveTemplate . DS . 'Template.php';
				return true;
			} else {
				echo 'Fatal error: Could not find responsible template!';
				exit(1);
			}
		}
		return false;
	}
	
	public static function GetFiles($Dir, $Pattern = false, $Recursive = true) {
		$RecursiveResult = array();
		$Result = array();
		if(substr($Dir, -1) != DS)
			$Dir .= DS;
		if(is_dir($Dir)) {
			if($DirHandler = opendir($Dir)) {
				while(($File = readdir($DirHandler)) !== false) {
					if(!in_array($File, array('.', '..'))) {
						if(is_dir($Dir . $File)) {
							$Result = array_merge($Result, self::GetFiles($Dir . $File . DS, $Pattern, $Recursive));
						} else {
							if($Pattern) {
								if(preg_match($Pattern, $Dir . $File)) {
									$Result[] = $Dir . $File;
								}
							} else
								$Result[] = $Dir . $File;
						}
					}
				}
				closedir($DirHandler);
				return $Result;
			}
		}
		return false;
	}
}