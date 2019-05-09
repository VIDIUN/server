<?php
/**
 * @package    Core
 * @subpackage vEditorServices
 */
class getRelatedVshowsAction extends vidiunAction
{
	public function execute ( )
	{ 		
		$vshow_id = $this->getRequestParameter( 'vshow_id' , '');
		$this->vshowdataarray = myVshowUtils::getRelatedShowsData( $vshow_id, null, 12 );
		$this->getResponse()->setHttpHeader ( "Content-Type" , "text/xml; charset=utf-8" );
		$this->getController()->setRenderMode ( sfView::RENDER_CLIENT );
	}
}

