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
class setRoughcutThumbnailAction extends defVeditorservicesAction
{
	protected function executeImpl( vshow $vshow, entry &$entry )
	{
		$this->res = "";
		
		$livuser_id = $this->getLoggedInUserId();

		// if we allow multiple rouchcuts - there is no reason for one suer to override someone else's thumbnail
		if ( $this->allowMultipleRoughcuts()  )
		{
			if ( $livuser_id != $entry->getVuserId())
			{
				// ERROR - attempting to update an entry which doesnt belong to the user
				return "<xml>!!</xml>";//$this->securityViolation( $vshow->getId() );
			}
		}

		$debug = @$_GET["debug"];
		/*
		$vshow_id = @$_GET["vshow_id"];
		$debug = @$_GET["debug"];
		
		$this->vshow_id = $vshow_id;

		if ( $vshow_id == NULL || $vshow_id == 0 ) return;

		$vshow = vshowPeer::retrieveByPK( $vshow_id );
		
		if ( ! $vshow ) 
		{
			$this->res = "No vshow " . $vshow_id ;
			return;	
		}

		// is the logged-in-user is not an admin or the producer - check if show can be published	
		$livuser_id = $this->getLoggedInUserId();
		$viewer_type = myVshowUtils::getViewerType($vshow, $livuser_id);
		if ( $viewer_type != VshowVuser::VSHOWVUSER_VIEWER_PRODUCER && ( ! $vshow->getCanPublish() ) ) 
		{
			// ERROR - attempting to publish a non-publishable show
			return "<xml>!</xml>";//$this->securityViolation( $vshow->getId() );
		}
		
		
		// ASSUME - the vshow & roughcut already exist
		$show_entry_id = $vshow->getShowEntryId();
		$roughcut = entryPeer::retrieveByPK( $show_entry_id );

		$roughcut = entryPeer::retrieveByPK( $entry_id );
		
 
		if ( ! $roughcut)
		{
			$this->res = "No roughcut for vshow " . $vshow->getId() ;
			return;	
		}
		*/		
//		echo "for entry: $show_entry_id current thumb path: " . $entry->getThumbnail() ;
		
		$entry->setThumbnail ( ".jpg");
		$entry->setCreateThumb(false);
		$entry->save();
		
		//$thumb_data = $_REQUEST["ThumbData"];

		if(isset($HTTP_RAW_POST_DATA))
			$thumb_data = $HTTP_RAW_POST_DATA;
		else
			$thumb_data = file_get_contents("php://input");

//		$thumb_data = $GLOBALS["HTTP_RAW_POST_DATA"];
		$thumb_data_size = strlen( $thumb_data );
		
		$bigThumbPath = myContentStorage::getFSContentRootPath() .  $entry->getBigThumbnailPath();
		
		vFile::fullMkdir ( $bigThumbPath );
		vFile::setFileContent( $bigThumbPath , $thumb_data );
		
		$path = myContentStorage::getFSContentRootPath() .  $entry->getThumbnailPath();
		
		vFile::fullMkdir ( $path );
		myFileConverter::createImageThumbnail( $bigThumbPath , $path );
		
		$roughcutPath = $entry->getFullDataPath();
		$xml_doc = new VDOMDocument();
		$xml_doc->load( $roughcutPath );
		
		if (myMetadataUtils::updateThumbUrl($xml_doc, $entry->getBigThumbnailUrl()))
			$xml_doc->save($roughcutPath);
			
		$this->res = $entry->getBigThumbnailUrl();
	}
	


}
