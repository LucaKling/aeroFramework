<?php
class Core {
	// Config vars
	public $Config;
	
	// Dir and file handling vars
	public $Dirname;
	
	// Template vars
	public $DefaultTemplate;
	public $DefaultSubTemplate;
	public $ActiveTemplate;
	public $ActiveSubTemplate;
	
	// Misc vars
	public $PageController;
	public $Protocol;
	public $Domain;
	public $URIPath;
	public $RequestURI;
	public $Database;
	public $OS;
	
	function __construct() {
		// Loading arg
		foreach(func_get_arg(0) as $key => $value)
			$this->$key = $value;
		
		if(strtolower(substr(php_uname('s'), 0, 3)) == 'win') {
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
		$this->Protocol = (($this->Config->DV->{'Reachability.Protocol'} != null) ? $this->Config->DV->{'Reachability.Protocol'} : 'http');
		$this->Domain = (($this->Config->DV->{'Reachability.Domain'} != null) ? $this->Config->DV->{'Reachability.Domain'} : $_SERVER['HTTP_HOST']);
		$this->URIPath = $this->Config->DV->{'Reachability.URIPath'};
		if($this->URIPath == null || $this->URIPath == '/') {
			$this->URIPath = substr($this->Dirname, strlen($_SERVER['DOCUMENT_ROOT']));
		} else {
			if(substr($this->URIPath, 0, 1) == '/')
				$this->URIPath = substr($this->URIPath, 1);
			if(substr($this->URIPath, -1) == '/')
				$this->URIPath = substr($this->URIPath, 0, -1);
		}
		$this->RequestURI = explode('/', ((empty($this->URIPath)) ? $_SERVER['REQUEST_URI'] : substr($_SERVER['REQUEST_URI'], strlen($this->URIPath)+1)));
		
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
		$PageController['Files'] = glob($this->Dirname . DS . 'PageController' . DS . '*Controller{,Advanced}.php', GLOB_BRACE);
		foreach($PageController['Files'] as $Key => $Value) {
			$Basename = basename($Value);
			if(!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*Controller(|Advanced).php/', $Basename)) {  // See: http://us2.php.net/manual/en/userlandnaming.php
				unset($PageController['Files'][$Key]);
				continue;
			}
			require_once $Value;
			$Value = substr($Basename, 0, -4);
			if(substr($Value, -8) == 'Advanced') {
				$PageController['Advanced'][$Key] = $Value;
				$PageController['Priority'][$Key] = (INT) $Value::Priority();
			} else
				$PageController['Simple'][] = $Value;
		}
		echo '<pre>', print_r($PageController), '</pre>';
		unset($Key, $Value, $Basename);
		if(in_array($Page . 'Controller', $PageController['Simple'])) {
			$Controller = $Page . 'Controller';
			$this->PageController = new $Controller(array('Config' => $this->Config, 'Dirname' => $this->Dirname, 'RequestURI' => $this->RequestURI, 'Protocol' => $this->Protocol, 'Domain' => $this->Domain, 'URIPath' => $this->URIPath, 'Database' => $this->Database, 'ActiveTemplate' => $this->ActiveTemplate, 'DefaultTemplate' => $this->DefaultTemplate, 'ActiveSubTemplate' => $this->ActiveSubTemplate, 'DefaultSubTemplate' => $this->DefaultSubTemplate));
			return true;
		} else {
			arsort($PageController['Priority'], SORT_NATURAL);
			foreach($PageController['Priority'] as $Key => $Value) {
				$Controller = $PageController['Advanced'][$Key];
				if($Controller::IsResponsible($Page, $this->Config, $this->Database)) {
					$this->PageController = new $Controller(array('Config' => $this->Config, 'Dirname' => $this->Dirname, 'RequestURI' => $this->RequestURI, 'Protocol' => $this->Protocol, 'Domain' => $this->Domain, 'URIPath' => $this->URIPath, 'Database' => $this->Database, 'ActiveTemplate' => $this->ActiveTemplate, 'DefaultTemplate' => $this->DefaultTemplate, 'ActiveSubTemplate' => $this->ActiveSubTemplate, 'DefaultSubTemplate' => $this->DefaultSubTemplate));
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
	
	function __destruct() {
		return true;
	}
}