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
class editPendingAction extends vidiunSystemAction
{

	public function execute()
	{
		$this->forceSystemAuthentication();
		
		$vshow_id = @$_REQUEST["vshow_id"];
		$this->vshow_id = $vshow_id;
		$this->vshow = NULL;
		
		$entry_id = @$_REQUEST["entry_id"];
		$this->entry_id = $entry_id;
		$this->entry = NULL;
		
		$this->message =  "";
		if ( !empty ( $vshow_id ))
		{
			$this->vshow = vshowPeer::retrieveByPK( $vshow_id );
			if (  ! $this->vshow )
			{
				$this->message = "Cannot find vshow [$vshow_id]";
			}
			else
			{
				$this->entry = $this->vshow->getShowEntry();
			} 
		}
		elseif ( !empty ( $vshow_id ))
		{
			$this->entry = entryPeer::retrieveByPK( $entry_id );
			if ( ! $this->entry )
			{
				$this->message = "Cannot find entry [$entry_id]";
			}
			else
			{
				$this->vshow = $this->$this->entry->getVshow();
			}
		}
		
		if ( $this->vshow )
		{
			$this->metadata = $this->vshow->getMetadata();
		}
		else
		{
			$this->metadata = "";
		}
		
		$pending_str = $this->getP ( "pending" );
		$remove_pending = $this->getP ( "remove_pending" );
		
		
		if ( $this->metadata && ( $remove_pending || $pending_str ) )
		{
			if  ( $remove_pending )				$pending_str = "";
			
			$xml_doc = new DOMDocument();
			$xml_doc->loadXML( $this->metadata );
			$metadata = vXml::getFirstElement( $xml_doc , "MetaData" );
			$should_save = vXml::setChildElement( $xml_doc , $metadata , "Pending" , $pending_str , true );
			if  ( $remove_pending )
				$should_save = vXml::setChildElement( $xml_doc , $metadata , "LastPendingTimeStamp" /*myMetadataUtils::LAST_PENDING_TIMESTAMP_ELEM_NAME*/ , "" , true );
			
			if ( $should_save )
			{
				$fixed_content = $xml_doc->saveXML();
				$file_name = realpath($this->entry->getFullDataPath());
				
				$res = file_put_contents( $file_name , $fixed_content ); // sync - NOTOK 
				
				$this->metadata = $fixed_content;
			}
		}
		
		$this->pending = $pending_str;
		
		$this->vshow_id = $vshow_id;
		$this->entry_id = $entry_id;
	}
}
?>