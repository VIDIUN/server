<?php
/**
 * @package    Core
 * @subpackage vEditorServices
 */
require_once ( __DIR__ . "/defVeditorservicesAction.class.php");

/**
 * @package    Core
 * @subpackage vEditorServices
 */
class setRoughcutNameAction extends defVeditorservicesAction
{
	protected function executeImpl( vshow $vshow, entry &$entry )
	{
		$this->res = "";
		
		$livuser_id = $this->getLoggedInUserId();
		
		if ( $livuser_id != $entry->getVuserId())
		{
			// ERROR - attempting to update an entry which doesnt belong to the user
			return "<xml>!</xml>";//$this->securityViolation( $vshow->getId() );
		}
		
		$name = @$_GET["RoughcutName"];
		
		$entry->setName($name);
		$entry->save();
		
		//myEntryUtils::createWidgetImage($entry, false);
		
		$this->name = $name;
	}
}

?>