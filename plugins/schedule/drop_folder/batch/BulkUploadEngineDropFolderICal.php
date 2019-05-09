<?php
/**
 * @package plugins.scheduleDropFolder
 * @subpackage batch
 */
class BulkUploadEngineDropFolderICal extends BulkUploadEngineICal
{
	/**
	 *
	 * @var VidiunDropFolder
	 */
	private $dropFolder = null;
	
	/**
	 *
	 * @var vFileTransferMgr
	 */
	private $fileTransferMgr = null;
	
	public function __construct(VidiunBatchJob $job)
	{
		parent::__construct($job);
		
		VBatchBase::impersonate($this->currentPartnerId);
		$dropFolderPlugin = VidiunDropFolderClientPlugin::get(VBatchBase::$vClient);
		VBatchBase::$vClient->startMultiRequest();
		$dropFolderFile = $dropFolderPlugin->dropFolderFile->get($this->job->jobObjectId);
		$dropFolderPlugin->dropFolder->get($dropFolderFile->dropFolderId);
		list($dropFolderFile, $this->dropFolder) = VBatchBase::$vClient->doMultiRequest();
		
		$this->fileTransferMgr = VDropFolderFileTransferEngine::getFileTransferManager($this->dropFolder);
		$this->data->filePath = $this->getLocalFilePath($dropFolderFile->fileName, $dropFolderFile->id);
		
		VBatchBase::unimpersonate();
	}
	
	/**
	 * Local drop folder - constract full path
	 * Remote drop folder - download file to a local temp directory and return the temp file path
	 * 
	 * @param string $fileName        	
	 * @param int $fileId        	
	 * @throws Exception
	 */
	protected function getLocalFilePath($fileName, $fileId)
	{
		$dropFolderFilePath = $this->dropFolder->path . '/' . $fileName;
		
		// local drop folder
		if($this->dropFolder->type == VidiunDropFolderType::LOCAL)
		{
			$dropFolderFilePath = realpath($dropFolderFilePath);
			return $dropFolderFilePath;
		}
		else
		{
			// remote drop folder
			$tempFilePath = tempnam(VBatchBase::$taskConfig->params->sharedTempPath, 'parse_dropFolderFileId_' . $fileId . '_');
			$this->fileTransferMgr->getFile($dropFolderFilePath, $tempFilePath);
			$this->setFilePermissions($tempFilePath);
			return $tempFilePath;
		}
	}
	
	protected function setFilePermissions($filepath)
	{
		$chmod = 0640;
		if(VBatchBase::$taskConfig->getChmod())
			$chmod = octdec(VBatchBase::$taskConfig->getChmod());
		
		VidiunLog::info("chmod($filepath, $chmod)");
		@chmod($filepath, $chmod);
		
		$chown_name = VBatchBase::$taskConfig->params->fileOwner;
		if($chown_name)
		{
			VidiunLog::info("Changing owner of file [$filepath] to [$chown_name]");
			@chown($filepath, $chown_name);
		}
	}
}