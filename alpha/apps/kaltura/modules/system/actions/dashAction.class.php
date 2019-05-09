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
class dashAction extends vidiunSystemAction
{
	/**
	 * Will give a good view of the batch processes in the system
	 */
	public function execute()
	{
		$this->systemAuthenticated();
		
	}
}

?>