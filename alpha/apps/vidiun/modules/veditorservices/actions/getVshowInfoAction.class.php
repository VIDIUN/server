<?php

/**
 * @package    Core
 * @subpackage vEditorServices
 */
class getVshowInfoAction extends defVeditorservicesAction
{
	protected function executeImpl ( vshow $vshow, entry &$entry )
	{
		if ($entry->getMediaType() == entry::ENTRY_MEDIA_TYPE_SHOW)
			$this->show_versions = array_reverse($entry->getAllversions());
		else
			$this->show_versions = array();
			
		$this->producer = vuser::getVuserById ( $vshow->getProducerId() );
		$this->editor = $entry->getVuser();
		$this->thumbnail = $entry ? $entry->getThumbnailPath() : "";
		
		// is the logged-in-user is an admin or the producer or the show can always be published...	
		$livuser_id = $this->getLoggedInUserId();
		$viewer_type = myVshowUtils::getViewerType($vshow, $livuser_id);
		$this->entry = $entry ? $entry : new entry() ; // create a dummy entry for the GUI
		$this->can_publish =  ( $viewer_type == VshowVuser::VSHOWVUSER_VIEWER_PRODUCER ||  $vshow->getCanPublish() ) ;
	}

	protected function noSuchVshow ( $vshow_id )
	{
		$this->vshow = new vshow();
		$this->producer = new vuser() ;
	}

}

?>