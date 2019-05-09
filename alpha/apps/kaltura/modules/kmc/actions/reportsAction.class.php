<?php
/**
 * @package    Core
 * @subpackage VMC
 */
class reportsAction extends vidiunAction
{
	public function execute ( ) 
	{
		$this->vs = $this->getP ( "vs" );
		$this->partner_id = $this->getP ( "partner_id" );
				
		$this->subp_id = $this->getP ( "subp_id" );
		$this->uid = $this->getP ( "uid" );

		$this->screen_name = $this->getP ( "screen_name" );
		$this->email = $this->getP ( "email" );
		
		$this->beta = $this->getRequestParameter( "beta" );
		
		sfView::SUCCESS;
	}
}
