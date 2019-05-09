<?php
/**
 * @package    Core
 * @subpackage vEditorServices
 */
class flvproviderAction extends sfAction
{
	public function execute()
	{
		requestUtils::handleConditionalGet();

		// set the memory size to be able to serve big files in a single chunk
		ini_set( "memory_limit","64M" );
		// set the execution time to be able to serve big files in a single chunk
		ini_set ( "max_execution_time" , 240 );
		
		
		$meta = $this->getRequestParameter( "meta" , false );
		$file_info = $this->getRequestParameter( "file_info" );
		$this->entry_id = 0;
		$this->vshow_id = 0;
		$version = $this->getRequestParameter( "version" , null ); // returned the version feature to allow rollback
		$addPadding = false;
		
		if ( !empty ( $file_info ) )
		{
			$file_info_arr = explode ( "-" , $file_info );

			// the format of file_info is assumed <vshow_id>-<video|audio|voice>-<1|2|3>
			// OR
			// e<entry_id>-<video|audio|voice>-<1|2|3>

			if ( count ( $file_info_arr ) == 0 )
			{
				$this->error = "Invalid request format [$file_info]" ;
				return sfView::ERROR;
			}
			
			if ($file_info_arr[0][0] == 'e')
				$this->entry_id = substr($file_info_arr[0], 1);
			else
				$this->vshow_id = $file_info_arr[0];

			if ( count ( $file_info_arr ) == 1 )
			{
				// on this case we assume that the single info parameter is an entry id
				// we redirect to it !
				$entry = entryPeer::retrieveByPK( $this->entry_id );
				if ( ! $entry ) 
				{
					// very bad - no such entry !!	
					echo "no entry " . $this->entry_id ;
					die;
				}
				
				$dataKey = $entry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA); // replaced__getDataPath
				$path = vFileSyncUtils::getReadyLocalFilePathForKey($dataKey);
				
				$host = requestUtils::getHost();
				
				$this->redirect( $host . $path );
			}
			
							
			$this->timeline = $file_info_arr[1];
			if ( count ( $file_info_arr ) > 2 )
			{
				// this migth include a .flv suffix
				$last_token = $file_info_arr[2];
				$last_token_srr  =  explode ( "." , $last_token );
				$this->streamNum = $last_token_srr[0];

				if ( count ( $file_info_arr ) > 3)
        			$version = $file_info_arr[3];
        							
				if ( count ( $file_info_arr ) > 4 && $file_info_arr[4] == "padding")
					$addPadding = true;
			}
			else
				$this->streamNum = 3;
			
		}
		else
		{
			$this->vshow_id = @$_GET["vshow_id"];
			$this->entry_id = @$_GET["entry_id"];
			$this->timeline = @$_GET["timeline"];
			$this->streamNum = $this->getRequestParameter('num', 3);
		}
		
		$entry = null;
		
		if ($this->entry_id) // first try to retrieve the entry if we have it
		{
			$entry = entryPeer::retrieveByPK($this->entry_id);
			if (!$entry)
			{
				$this->error = "No such entry " . $this->entry_id ;
				return sfView::ERROR;
			}
							
			$this->vshow_id = $entry->getVshowId();
		}
		
		$vshow = vshowPeer::retrieveByPK($this->vshow_id);

		if (!$vshow)
		{
			$this->error = "No such vshow " . $this->vshow_id ;
			return sfView::ERROR;
		}

		if (!$entry) // if we received only the vshow (old widgets) retrieve the entry
			$entry = entryPeer::retrieveByPK($vshow->getShowEntryId());
			
		if (!$entry)
		{
			$this->error = "No such entry for vshow " . $this->vshow_id ;
			return sfView::ERROR;
		}
			
		// update the widget log only for video && stream 1
		if ( $this->timeline == "video" && $this->streamNum == 1 )
		{
			$referer = @$_SERVER['HTTP_REFERER'];

			//since we're using a cdn this is useless
			//$vshow->incPlays();
			//WidgetLog::incPlaysIfExists( $this->vshow_id , $this->entry_id );
		}
		
		$dataKey = $entry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA, $version); // replaced__getDataPath
		$this->flv_streamer = new myFlvStreamer( $dataKey, $this->timeline, $this->streamNum, $addPadding );
		
		$this->total_length = $this->flv_streamer->getTotalLength( true ); // $total_length;
		
		//$this->getController()->setRenderMode ( sfView::RENDER_CLIENT );
		
		
		myStatisticsMgr::saveAllModified();
		
		//if ( $meta )		return "Meta";
		return sfView::SUCCESS;
	}

}
?>
