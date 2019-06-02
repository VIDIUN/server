<?php

/**
 * @package infra
 * @subpackage Media
 */
class vImageUtils
{
	public static function getImageSize(FileSync $fileSync = null)
	{
		$width = $height = $type = $attr = null;
		
		if(!$fileSync)
		{
			return array($width, $height, $type, $attr);
		}
		
		if(!$fileSync->getEncryptionKey())
		{
			$key = vFileSyncUtils::getKeyForFileSync($fileSync);
			$filePath = vFileSyncUtils::getLocalFilePathForKey($key);
			list($width, $height, $type, $attr) = getimagesize($filePath);
		}
		else
		{
			$filePath = $fileSync->createTempClear();
			list($width, $height, $type, $attr) = getimagesize($filePath);
			$fileSync->deleteTempClear();
		}
		
		return array($width, $height, $type, $attr);
	}
}
