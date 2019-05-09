<?php
/**
 * @package    Core
 * @subpackage system
 * @deprecated
 */
require_once ( __DIR__ . "/vidiunSystemActions.class.php");

/**
 * system actions.
 *
 * @package    Core
 * @subpackage system
 * @deprecated
 */
class systemActions extends vidiunSystemActions
{
  /**
   * Executes index action
   *
   */
	public function executeDefSystem()
	{
		$this->forceSystemAuthentication();
		
	}
	
	
	public function executeEnvironment()
	{
		$this->forceSystemAuthentication();
		
	}
	
	
	public function executeCheckAttachmentImport()
	{
					
	}
	
	public function executeViewReports()
	{
		$this->forceSystemAuthentication();
		
		$c = new Criteria();
		$c->addDescendingOrderByColumn( flagPeer::CREATED_AT);
		$c->addJoin( flagPeer::VUSER_ID, vuserPeer::ID, Criteria::LEFT_JOIN );
		$this->reports = flagPeer::doSelectJoinvuser( $c );
	}

	public function executeDeleteReport()
	{
		$this->forceSystemAuthentication();
		
		$id = $this->getRequestParameter( 'id');
		if ( $id )
		{
			$report =  flagPeer::retrieveByPK( $id );
			$report->delete();
		}
		$this->redirect('system/viewReports');
		
	}
	
	
}
