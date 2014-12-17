<?php
class DatabaseControllerAdvanced extends PageController {
	public $Pages;
	
	public static function Priority() {
		return (INT) 0;
	}
	
	public static function IsResponsible($Page, $Config, $Database) {
		$Pages = array();
		$PagesTable = $Config->Database->Prefix . $Config->DV->{'DatabaseController.Prefix'} . $Config->DV->{'DatabaseController.PagesTable'};
		try {
			$Database->STMT = $Database->PDO->query('SELECT COUNT(*) FROM `' . $PagesTable . '` WHERE `ResponsibleFor` = ' . $Database->PDO->quote($Page));
			if($Database->STMT !== false) {
				if($Database->STMT->fetchColumn() > 0) // See: http://php.net/manual/de/pdostatement.rowcount.php
					return true;
			} else
				throw new PDOException(implode(', ', $Database->PDO->errorInfo()));
		} catch(PDOException $e) {
			echo 'An error occurred while fetching data from database: ' . $e->getMessage();
			exit(1);
		}
		return false;
	}
	
	public function Construct() {
		$this->CurrentPage = (($this->RequestURI[1] == null) ? 'Index' : ucfirst($this->RequestURI[1]));
		$Database = $this->Database;
		$PagesTable = $this->Config->Database->Prefix . $this->Config->DV->{'DatabaseController.Prefix'} . $this->Config->DV->{'DatabaseController.PagesTable'};
		try {
			$Database->STMT = $Database->PDO->query('SELECT * FROM `' . $PagesTable . '` WHERE `ResponsibleFor` = ' . $Database->PDO->quote($this->CurrentPage));
			if($Database->STMT !== false) {
				$row = $Database->STMT->fetch(PDO::FETCH_OBJ);
				$this->Meta = $row->Meta;
				$this->MetaTitle = (($this->MetaTitle) ? sprintf($this->MetaTitle, $row->MetaTitle) : $row->MetaTitle);
				$this->Head = $row->Head;
				$this->PageTitle = $row->PageTitle;
				$this->PageContent = $row->PageContent;
				$this->FooterContents = $row->FooterContents;
				$this->BodyScripts = $row->BodyScripts;
				$this->GlobalTitle = (($row->GlobalTitleOverride) ? $row->GlobalTitleOverride : $this->GlobalTitle);
				$this->ActiveTemplate = (($row->TemplateOverride) ? $row->TemplateOverride : $this->ActiveTemplate);
				$this->ActiveSubTemplate = (($row->SubTemplateOverride) ? $row->SubTemplateOverride : $this->ActiveSubTemplate);
			} else
				throw new PDOException(implode(', ', $Database->PDO->errorInfo()));
		} catch(PDOException $e) {
			echo 'An error occurred while fetching data from database: ' . $e->getMessage();
			exit(1);
		}
		return true;
	}
}