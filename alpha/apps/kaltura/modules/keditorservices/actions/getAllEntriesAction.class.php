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
class getAllEntriesAction extends defVeditorservicesAction
{
	const LIST_TYPE_VSHOW = 1 ;
	const LIST_TYPE_VUSER = 2 ;
	const LIST_TYPE_ROUGHCUT = 4 ;
	const LIST_TYPE_EPISODE = 8 ;
	const LIST_TYPE_ALL = 15;
	
	protected function executeImpl ( vshow $vshow, entry &$entry )
	{
		$list_type = $this->getP ( "list_type" , self::LIST_TYPE_ALL );
		
		$vshow_entry_list = array();
		$vuser_entry_list = array();
		
		if ( $list_type & self::LIST_TYPE_VSHOW )
		{
			$c = new Criteria();
			$c->add ( entryPeer::TYPE , entryType::MEDIA_CLIP );
			$c->add ( entryPeer::MEDIA_TYPE , entry::ENTRY_MEDIA_TYPE_SHOW , Criteria::NOT_EQUAL );
			$c->add ( entryPeer::VSHOW_ID , $this->vshow_id );
			$vshow_entry_list = entryPeer::doSelectJoinvuser( $c );
		}

		if ( $list_type & self::LIST_TYPE_VUSER )
		{
			$c = new Criteria();
			$c->add ( entryPeer::TYPE , entryType::MEDIA_CLIP );
			$c->add ( entryPeer::MEDIA_TYPE , entry::ENTRY_MEDIA_TYPE_SHOW , Criteria::NOT_EQUAL );
			$c->add ( entryPeer::VUSER_ID , $this->getLoggedInUserIds(), Criteria::IN  );
			$vuser_entry_list = entryPeer::doSelectJoinvuser( $c );
		}		

		if ( $list_type & self::LIST_TYPE_EPISODE )
		{
			if ( $vshow->getEpisodeId() )
			{
				// episode_id will point to the "parent" vshow
				// fetch the entries of the parent vshow
				$c = new Criteria();
				$c->add ( entryPeer::TYPE , entryType::MEDIA_CLIP );
				$c->add ( entryPeer::MEDIA_TYPE , entry::ENTRY_MEDIA_TYPE_SHOW , Criteria::NOT_EQUAL );
				$c->add ( entryPeer::VSHOW_ID , $vshow->getEpisodeId() );
				$parent_vshow_entries = entryPeer::doSelectJoinvuser( $c );
				if ( count ( $parent_vshow_entries) )
				{
					$vshow_entry_list = vArray::append  ( $vshow_entry_list , $parent_vshow_entries );
				}			
			}
		}
		
		// fetch all entries that were used in the roughcut - those of other vusers 
		// - appeared under vuser_entry_list when someone else logged in

		if ( $list_type & self::LIST_TYPE_ROUGHCUT )
		{
			if ( $vshow->getHasRoughcut() )
			{
				$entry_ids_from_roughcut = myFlvStreamer::getAllAssetsIds ( $entry );
				
				$final_id_list = array();
				foreach ( $entry_ids_from_roughcut as $id )
				{
					$found = false;
					foreach ( $vshow_entry_list as $entry )
					{
						if ( $entry->getId() == $id )
						{
							$found = true; 
							break;
						}
					}
					if ( !$found )	$final_id_list[] = $id;
				}
				
				$c = new Criteria();
				$c->add ( entryPeer::ID , $final_id_list , Criteria::IN );
				$extra_entries = entryPeer::doSelectJoinvuser( $c );
				
				// merge the 2 lists into 1:
				$vshow_entry_list = vArray::append  ( $vshow_entry_list , $extra_entries );
			}
		}
		
		$this->vshow_entry_list = $vshow_entry_list;
		$this->vuser_entry_list = $vuser_entry_list;
		
	}
	
	protected function noSuchVshow ( $vshow_id )
	{
		$this->vshow_entry_list = array ();
		$this->vuser_entry_list = array ();
	}

}

?>