<?php
/**
 * @package    Core
 * @subpackage system
 * @deprecated
 */
require_once ( __DIR__ . "/vidiunSystemAction.class.php" );

/**
 * @package    Core
 * @subpackage system
 * @deprecated
 */
class blockedEmailsAction extends vidiunSystemAction
{
	public function execute()
	{
		$this->forceSystemAuthentication();		
	}
}
?>