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
class createDefaultMetadataAction extends defVeditorservicesAction
{
	/**
	 * Executes index action
	 */
	protected function executeImpl( vshow $vshow, entry &$entry)
	{
		$this->xml_content = ""; 

		$vshow_id = $this->vshow_id;
		if ( $vshow_id == NULL || $vshow_id == 0 )		return sfView::SUCCESS;
		$metadata_creator = new myVshowMetadataCreator ();

		$this->show_metadata = $metadata_creator->createMetadata ( $vshow_id );

//		$vshow = vshowPeer:retrieveByPK( $vshow_id );
		$entry = entryPeer::retrieveByPK( $vshow->getShowEntryId() );


		// TODO - this should never happen
		if ( $entry == NULL )
		{
			// there is no show entry for this show !
			$entry = $vshow->createEntry ( entry::ENTRY_MEDIA_TYPE_SHOW , $vshow->getProducerId() );
		}
		
		$file_path = $entry->getFullDataPath();

		// check to see if the content of the file changed
		$current_metadata = vFile::getFileContent( $file_path );

		$comp_result = strcmp ( $this->show_metadata , $current_metadata  );
		if ( $comp_result != 0 )
		{
			$ext = pathinfo($file_path, PATHINFO_EXTENSION);
			if ( $ext != "xml")
			{
				// this is for the first time - override the template path by setting the data to NULL
				$entry->setData ( NULL );
				$file_path = pathinfo($file_path, PATHINFO_DIRNAME) . "/" . vFile::getFileNameNoExtension ( $file_path ) . ".xml";
			}

			// this will increment the name if needed
			$entry->setData ( $file_path );
			$file_path = $entry->getFullDataPath();

			$entry->save();

			vFile::fullMkdir($file_path);
			vFile::setFileContent( $file_path , $this->show_metadata );
			
			$this->xml_content = $this->show_metadata;
			
			
		}

	}

	protected function noSuchVshow ( $vshow_id )
	{
		$this->xml_content = "";
	}

}
?>