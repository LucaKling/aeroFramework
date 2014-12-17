<?php
class NotFoundControllerAdvanced extends PageController {
	public static function IsResponsible($Page, $Config, $Database) {
		return true;
	}
	
	public static function Priority() {
		return (INT) -1;
	}
	
	public function Construct() {
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
		header('Status: 404 Not Found');
		$_SERVER['REDIRECT_STATUS'] = 404;
		http_response_code(404);
		$this->Meta = '';
		$this->MetaTitle = sprintf($this->MetaTitle, 'Fehler 404 - Seite nicht gefunden');
		$this->Head = '';
		$this->NavElements = '';
		$this->PageTitle = '404 - Seite nicht gefunden';
		$baseuri = $this->BaseURI;
		$uristring = implode('/', $this->RequestURI);
		if(strlen($uristring) > 35)
			$uristring = substr($uristring, 0, 16) . '...' . substr($uristring, -16);
		$this->PageContent = <<<EOF
<p>Die Seite <code>$uristring</code> gibt es nicht!</p><p><a style="text-decoration: none !important;" href="$baseuri"><i class="fa fa-fw fa-home"></i> Zur Startseite</a></p>
EOF;
		$this->FooterContents = '';
		$this->BodyScripts = '';
		return true;
	}
}