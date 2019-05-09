<?php
/**
 * @service captureSpace
 * @package plugins.captureSpace
 * @subpackage api.services
 */
class CaptureSpaceService extends VidiunBaseService
{
	/**
	 * Returns latest version and URL
	 *
	 * @action clientUpdates
	 * @param string $os
	 * @param string $version
	 * @param VidiunCaptureSpaceHashAlgorithm $hashAlgorithm
	 * @return VidiunCaptureSpaceUpdateResponse
	 * @vsIgnored
	 * 
	 * @throws CaptureSpaceErrors::ALREADY_LATEST_VERSION
	 * @throws CaptureSpaceErrors::NO_UPDATE_IS_AVAILABLE
	 */
	function clientUpdatesAction ($os, $version, $hashAlgorithm = VidiunCaptureSpaceHashAlgorithm::MD5)
	{
		$hashValue = vCaptureSpaceVersionManager::getUpdateHash($os, $version, $hashAlgorithm);
		if (!$hashValue) {
			throw new VidiunAPIException(CaptureSpaceErrors::NO_UPDATE_IS_AVAILABLE, $version, $os);
		}
			
		$path = "/api_v3/service/captureSpace_captureSpace/action/serveUpdate/os/$os/version/$version";
		$downloadUrl = myPartnerUtils::getCdnHost(null) . $path;
		
		$info = new VidiunCaptureSpaceUpdateResponseInfo();
		$info->url = $downloadUrl;
		$info->hash = new VidiunCaptureSpaceUpdateResponseInfoHash();
		$info->hash->algorithm = $hashAlgorithm;
		$info->hash->value = $hashValue;
		
		$response = new VidiunCaptureSpaceUpdateResponse();
		$response->info = $info;
		
		return $response;
	}

	/**
	 * Serve installation file
	 *
	 * @action serveInstall
	 * @param string $os
	 * @return file
	 * @vsIgnored
	 * 
	 * @throws CaptureSpaceErrors::NO_INSTALL_IS_AVAILABLE
	 */
	public function serveInstallAction($os)
	{
		$filename = vCaptureSpaceVersionManager::getInstallFile($os);
		if (!$filename) {
			throw new VidiunAPIException(CaptureSpaceErrors::NO_INSTALL_IS_AVAILABLE, $os);
		}
		$actualFilePath = vCaptureSpaceVersionManager::getActualFilePath($filename);
		if (!$actualFilePath)
			throw new VidiunAPIException(CaptureSpaceErrors::NO_INSTALL_IS_AVAILABLE, $os);

		$mimeType = vFile::mimeType($actualFilePath);
		header("Content-Disposition: attachment; filename=\"$filename\"");
		return $this->dumpFile($actualFilePath, $mimeType);
	}


	/**
	 * Serve update file
	 *
	 * @action serveUpdate
	 * @param string $os
	 * @param string $version
	 * @return file
	 * @vsIgnored
	 * 
	 * @throws CaptureSpaceErrors::NO_UPDATE_IS_AVAILABLE
	 */
	public function serveUpdateAction($os, $version)
	{
		$filename = vCaptureSpaceVersionManager::getUpdateFile($os, $version);
		if (!$filename) {
			throw new VidiunAPIException(CaptureSpaceErrors::NO_UPDATE_IS_AVAILABLE, $version, $os);
		}
		
		$actualFilePath = myContentStorage::getFSContentRootPath() . "/content/third_party/capturespace/$filename";
		if (!file_exists($actualFilePath)) {
			throw new VidiunAPIException(CaptureSpaceErrors::NO_UPDATE_IS_AVAILABLE, $version, $os);
		}
		
		$mimeType = vFile::mimeType($actualFilePath);
		header("Content-Disposition: attachment; filename=\"$filename\"");
		return $this->dumpFile($actualFilePath, $mimeType);
	}
}


