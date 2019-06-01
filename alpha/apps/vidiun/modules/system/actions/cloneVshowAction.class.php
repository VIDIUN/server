<?php
/**
 * @package    Core
 * @subpackage system
 * @deprecated
 */
require_once ( __DIR__ . "/vidiunSystemAction.class.php" );

/**
 * @package    Core
 * @subpackage system
 * @deprecated
 */
class cloneVshowAction extends vidiunSystemAction
{

	public function execute()
	{
		$this->forceSystemAuthentication();

		$source_vshow_id = $this->getP ( "source_vshow_id" );
		$target_vshow_id = $this->getP ( "target_vshow_id" );
		$vuser_names = $this->getP ( "vuser_names" );

		$reset = $this->getP ( "reset" );
		if ( $reset )
		{
			$source_vshow_id = null;
			$target_vshow_id = null;
			$vuser_names = null;
		}
		
		$mode = 0;// view
		if ( $source_vshow_id && $target_vshow_id && $vuser_names )
		{
			$mode = 1; // review
			$list_of_vuser_names = explode ( "," , $vuser_names );
			foreach ( $list_of_vuser_names  as &$name )
			{
				$name = trim($name);
			}

			$source_vshow = vshowPeer::retrieveByPK( $source_vshow_id ) ;
			$target_vshow = vshowPeer::retrieveByPK( $target_vshow_id ) ;

			$target_partner_id = $target_vshow->getPartnerId();
			$target_subp_id = $target_vshow->getSubpId();

			$c = new Criteria();
			// select only the vusers of the correct partner_id
			$c->add ( vuserPeer::SCREEN_NAME , $list_of_vuser_names , Criteria::IN );
			$c->setLimit( 10 );
			//$c->add ( vuserPeer::PARTNER_ID , $target_partner_id );
			$list_of_vusers = vuserPeer::doSelect( $c );
			$producer = vuserPeer::retrieveByPK( $target_vshow->getProducerId());;
			$list_of_vusers[] = $producer;

			$c->add ( vuserPeer::PARTNER_ID , $target_partner_id );
			$list_of_valid_vusers = vuserPeer::doSelect( $c );
			$list_of_valid_vusers[] = $producer;
			
			$c = new Criteria();
			$c->add ( entryPeer::VSHOW_ID , $source_vshow_id );
			$c->add ( entryPeer::TYPE , entryType::MEDIA_CLIP );
			$c->add ( entryPeer::STATUS , entryStatus::READY );
			$entries = entryPeer::doSelectJoinAll( $c );
			
			$entry_vusers = array();
			// assign each entry to a vuser
			foreach ( $entries as $entry )
			{
				$place_in_array = count ( $entry_vusers ) % count ($list_of_valid_vusers );
				$vuser = $list_of_valid_vusers[ $place_in_array ];
				$entry_vusers[$entry->getId()] = $vuser->getId();	
			}
			
			$clone = $this->getP ( "clone" );
			if ( $clone == 'true') 
			{
				$mode = 2; // clone
				
				$entry_id_map = array(); 	// will be used to map the source->target entries
				$entry_cache = array ();	// will be used to cache all relevat entries
				
				$new_entry_list = array();
				$failed_entry_list = array();
				foreach ( $entries as $entry )
				{
					try
					{
						$vuser_id = $entry_vusers[$entry->getId()] ;
						$override_fields = $entry->copy();
						$override_fields->setPartnerId ( $target_vshow->getPartnerId() );
						$override_fields->setSubpId( $target_vshow->getSubpId());
						$override_fields->setVuserId( $vuser_id );
						$override_fields->setCreatorVuserId( $vuser_id );
						
						$new_entry = myEntryUtils::deepClone( $entry , $target_vshow_id , $override_fields ,false );
						$new_entry_list[] = $new_entry;
						// will help fix the metadata entries
						$entry_id_map [$entry->getId()] = $new_entry->getId();

						$entry_cache[$entry->getId()]=$entry;
						$entry_cache[$new_entry->getId()]=$new_entry;
					}
					catch ( Exception $ex )
					{
						$failed_entry_list[] = $entry; 
					}

//					echo "entry [{$entry->getId()}] copied<br>"; flush();
				}
				
				// now clone the show_entry
				$new_show_entry = $target_vshow->getShowEntry();
				myEntryUtils::deepCloneShowEntry ( $source_vshow->getShowEntry() , $new_show_entry , $entry_id_map , $entry_cache ) ;
				$new_entry_list[] = $new_show_entry;
				$entries = $new_entry_list;
				$entry_vusers = null;
			}
			
//			echo "ended!<bR>";			flush();
		}
		
		$this->source_vshow_id = @$source_vshow_id;
		$this->target_vshow_id = @$target_vshow_id;
		$this->partner_id = @$target_partner_id;
		$this->source_vshow = @$source_vshow;
		$this->target_vshow = @$target_vshow;
		$this->vuser_names = @$vuser_names;
		$this->list_of_vusers = @$list_of_vusers;
		$this->entries = @$entries;
		$this->mode = $mode;
		$this->entry_vusers = @$entry_vusers;
		
//		echo "going to template!<bR>";		flush();
	}
}
?>