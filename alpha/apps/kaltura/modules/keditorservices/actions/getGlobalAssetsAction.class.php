<?php
/**
 * @package    Core
 * @subpackage vEditorServices
 */
require_once ( __DIR__ . "/defVeditorservicesAction.class.php");

/**
 * fetch global assets - ones that
 * 
 * @package    Core
 * @subpackage vEditorServices
 */
class getGlobalAssetsAction extends defVeditorservicesAction
{
	protected function executeImpl( vshow $vshow, entry &$entry)
	{
		$asset_type = $this->getRequestParameter( "type" , entry::ENTRY_MEDIA_TYPE_VIDEO );

		if ( $asset_type > entry::ENTRY_MEDIA_TYPE_AUDIO || $asset_type < entry::ENTRY_MEDIA_TYPE_VIDEO )
/*		
		if ( ! in_array( $asset_type, 
			array ( entry::ENTRY_MEDIA_TYPE_VIDEO , 
					entry::ENTRY_MEDIA_TYPE_IMAGE , 
					entry::ENTRY_MEDIA_TYPE_TEXT , 
					entry::ENTRY_MEDIA_TYPE_HTML , 
					entry::ENTRY_MEDIA_TYPE_AUDIO ) ) ) */
		{
			// TODO - 
			// trying to fetch invalid media type	
		}
		
		$show_entry_id = $vshow->getShowEntryId();
		$intro_id = $vshow->getIntroId();
		
		
		$c = new Criteria();
		$c->add ( entryPeer::VUSER_ID , vuser::VUSER_VIDIUN );
		$c->add ( entryPeer::TYPE , vuser::VUSER_VIDIUN );
				
		$this->entry_list = entryPeer::doSelect( $c );
		if ( $this->entry_list == NULL )
			$this->entry_list = array ();
	}
	
	protected function noSuchVshow ( $vshow_id )
	{
		$this->entry_list = array ();
	}

}

?>