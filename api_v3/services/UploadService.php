<?php

/**
 *
 * @service upload
 * @package api
 * @subpackage services
 * @deprecated Please use UploadToken service
 */
class UploadService extends VidiunEntryService
{
	/**
	 * 
	 * @action upload
	 * @param file $fileData The file data
	 * @return string Upload token id
	 */
	function uploadAction($fileData)
	{
		$vsUnique = md5($this->getVs()->toSecureString());
		
		$uniqueId = md5($fileData["name"]);
		
		$ext = pathinfo($fileData["name"], PATHINFO_EXTENSION);
		$token = $vsUnique."_".$uniqueId.".".$ext;
		
		$res = myUploadUtils::uploadFileByToken($fileData, $token, "", null, true);
	
		return $res["token"];
	}
	
	/**
	 * 
	 * @action getUploadedFileTokenByFileName
	 * @param string $fileName
	 * @return VidiunUploadResponse
	 */
	function getUploadedFileTokenByFileNameAction($fileName)
	{
		VidiunResponseCacher::disableConditionalCache();
		
		$res = new VidiunUploadResponse();
		$vsUnique = md5($this->getVs()->toSecureString());
		
		$uniqueId = md5($fileName);
		
		$ext = pathinfo($fileName, PATHINFO_EXTENSION);
		$token = $vsUnique."_".$uniqueId.".".$ext;
		
		$entryFullPath = myUploadUtils::getUploadPath($token, "", null , strtolower($ext)); // filesync ok
		if (!file_exists($entryFullPath))
			throw new VidiunAPIException(VidiunErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN);
			
		$res->uploadTokenId = $token;
		$res->fileSize = vFile::fileSize($entryFullPath);
		return $res; 
	}
}