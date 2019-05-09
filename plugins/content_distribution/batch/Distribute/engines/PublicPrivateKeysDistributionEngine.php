<?php
/**
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
abstract class PublicPrivateKeysDistributionEngine extends DistributionEngine
{
	
	abstract function getTempDirectory();

	/*
 	* Lazy saving of the key to a temporary path, the key will exist in this location until the temp files are purged 
	 */
	protected function getFileLocationForSFTPKey($distributionProfileId, $keyContent, $fileName)
	{
		$tempDirectory = $this->getTempDirectoryForProfile($distributionProfileId);
		$fileLocation = $tempDirectory . $fileName;
		$content = vFile::getFileContent($fileLocation);
		if (!$content || $content !== $keyContent)
		{
			vFile::safeFilePutContents($fileLocation, $keyContent, 0600);
		}
		return $fileLocation;
	}

	/*
 	* Creates and return the temp directory used for this distribution profile 
	 */
	protected function getTempDirectoryForProfile($distributionProfileId)
	{
		$tempFilePath = $this->tempDirectory . '/' . $this->getTempDirectory() . '/' . $distributionProfileId . '/';
		if (!file_exists($tempFilePath))		
			vFile::fullMkfileDir($tempFilePath);
		return $tempFilePath;
	}

}
