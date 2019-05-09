<?php
/**
 * @package    Core
 * @subpackage VMC
 */
class signupAction extends vidiunAction
{
	public function execute ( ) 
	{
		$this->redirect("http://corp.vidiun.com/about/signup");
		sfView::SUCCESS;
	}
}
