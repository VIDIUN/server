<?php
/**
 * Watches drop folder files and executes file handlers as required 
 *
 * @package plugins.dropFolder
 * @subpackage Scheduler
 */
class VAsyncDropFolderWatcher extends VPeriodicWorker
{
	/**
	 * @var VidiunDropFolderClientPlugin
	 */
	protected $dropFolderPlugin = null;

	private $currentDropFolderId;
	
			
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::DROP_FOLDER_WATCHER;
	}
	
	/* (non-PHPdoc)
	 * @see VBatchBase::run()
	*/
	public function run($jobs = null)
	{
		$this->dropFolderPlugin = VidiunDropFolderClientPlugin::get(self::$vClient);
		
		if(self::$taskConfig->isInitOnly())
			return $this->init();
		$folder = null;

		$numberOfFoldersEachRun = self::$taskConfig->numberOfFoldersEachRun;
		VidiunLog::log("Start running to watch $numberOfFoldersEachRun folders");
		for ($i = 0; $i < $numberOfFoldersEachRun; $i++)
		{
			try 
			{
				/* @var $folder VidiunDropFolder */
				$folder = $this->getExclusiveDropFolder();
				if (!$folder)
					continue;
				$this->impersonate($folder->partnerId);
				$engine = VDropFolderEngine::getInstance($folder->type);
				$engine->setMaximumExecutionTime(self::$taskConfig->maximumExecutionTime);
				$engine->watchFolder($folder);
				$this->unimpersonate();
				$this->freeExclusiveDropFolder($folder->id);		
									    
			}
			catch (vFileTransferMgrException $e)
			{
				$this->unimpersonate();
				if($e->getCode() == vFileTransferMgrException::cantConnect)
					$this->freeExclusiveDropFolder($folder->id,VidiunDropFolderErrorCode::ERROR_CONNECT, DropFolderPlugin::ERROR_CONNECT_MESSAGE);
				else if($e->getCode() == vFileTransferMgrException::cantAuthenticate)
					$this->freeExclusiveDropFolder($folder->id,VidiunDropFolderErrorCode::ERROR_AUTENTICATE, DropFolderPlugin::ERROR_AUTENTICATE_MESSAGE);
				else
					$this->freeExclusiveDropFolder($folder->id,VidiunDropFolderErrorCode::ERROR_GET_PHISICAL_FILE_LIST, DropFolderPlugin::ERROR_GET_PHISICAL_FILE_LIST_MESSAGE);

			}
			catch (VidiunException $e)
			{
				$this->unimpersonate();
				$this->freeExclusiveDropFolder($folder->id,VidiunDropFolderErrorCode::ERROR_GET_DB_FILE_LIST, DropFolderPlugin::ERROR_GET_DB_FILE_LIST_MESSAGE);

			}
			catch (Exception $e) 
			{
				$this->unimpersonate();
				if ($folder)
					$this->freeExclusiveDropFolder($folder->id,VidiunDropFolderErrorCode::DROP_FOLDER_APP_ERROR, DropFolderPlugin::DROP_FOLDER_APP_ERROR_MESSAGE.$e->getMessage());
			}
		}
		
	}
	
		
	private function getExclusiveDropFolder() 
	{
		$folderTag = self::$taskConfig->params->tags;
		$maxTimeForFolder = self::$taskConfig->params->maxTimeForFolder;
		if (strlen($folderTag) == 0)
			throw new VidiunException('Tags must be specify in configuration - cannot continue');

		$dropFolder = $this->dropFolderPlugin->dropFolder->getExclusiveDropFolder($folderTag, $maxTimeForFolder);
		if (!is_null($dropFolder))
			$this->currentDropFolderId = $dropFolder->id;
		return $dropFolder;
	}
	
	private function freeExclusiveDropFolder($dropFolderId, $errorCode = null, $errorDescription = null)
	{
		if (!$dropFolderId)
			return;
		if ($errorDescription)
			VidiunLog::err("Error with folder id [$dropFolderId] - $errorDescription");
		try 
		{
	    	$this->dropFolderPlugin->dropFolder->freeExclusiveDropFolder($dropFolderId, $errorCode, $errorDescription);
		}
		catch(Exception $e)
		{
			VidiunLog::err("Error when trying to free drop folder [$dropFolderId] - ".$e->getMessage());
		}	
	}	
			
	function log($message)
	{
		if(!strstr($message, 'VidiunDropFolderListResponse') && !strstr($message, 'VidiunDropFolderFileListResponse'))
			VidiunLog::info($message);
	}

	public function preKill()
	{
		if ($this->currentDropFolderId)
			$this->freeExclusiveDropFolder($this->currentDropFolderId);
	}
}
