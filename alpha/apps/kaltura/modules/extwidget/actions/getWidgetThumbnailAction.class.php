<?php
/**
 * @package Core
 * @subpackage externalWidgets
 */
class getWidgetThumbnailAction extends sfAction
{
	/**
	 * Will forward to the the thumbnail of the vshows using the widget id
	 */
	public function execute()
	{
		$widget_id = $this->getRequestParameter( "wid" );
		$widget = widgetPeer::retrieveByPK( $widget_id );

		if ( !$widget )
		{
			die();	
		}
		
		// because of the routing rule - the entry_id & vmedia_type WILL exist. be sure to ignore them if smaller than 0
		$vshow_id= $widget->getVshowId();
		
		if ($vshow_id)
		{
			$vshow = vshowPeer::retrieveByPK($vshow_id);
			if ($vshow->getShowEntry())
				$this->redirect($vshow->getShowEntry()->getBigThumbnailUrl());
		}
	}
}
