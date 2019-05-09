<?php
/**
 * @package    Core
 * @subpackage VMC
 */
class varloginAction extends vidiunAction
{
	public function execute ( ) 
	{
		$this->beta = $this->getRequestParameter( "beta" );
		$this->vmc_login_version 	= vConf::get('vmc_login_version');
				
		sfView::SUCCESS;
	}
}
