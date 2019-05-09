<?php

/**
* @package plugins.dropFolder
* @subpackage model
*/
abstract class RemoteDropFolder extends DropFolder
{
	abstract protected function getRemoteFileTransferMgrType();
	
	/**
	 * @return vFileTransferMgrType
	 */
	public function getFileTransferMgrType()
	{
		return $this->getRemoteFileTransferMgrType();
	}
			
	public function loginByCredentialsType(vFileTransferMgr $fileTransferMgr)
	{
		return false;
	}
		
	/**
	 * @return vDropFolderImportJobData
	 */
	abstract public function getImportJobData();
	
	/**
	 * @return string
	 */
	abstract public function getFolderUrl();
	
	public function getLocalFilePath($fileName, $fileId, vFileTransferMgr $fileTransferMgr)
	{
		$dropFolderFilePath = $this->getPath().'/'.$fileName;
		$tempDirectory = sys_get_temp_dir();
		if (is_dir($tempDirectory)) 
		{
			$tempFilePath = tempnam($tempDirectory, 'parse_dropFolderFileId_'.$fileId.'_');		
			$fileTransferMgr->getFile($dropFolderFilePath, $tempFilePath);
			return $tempFilePath;
		}
		else
		{
			VidiunLog::err('Missing temporary directory');
			return null;			
		}
	}
	
	
}
