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