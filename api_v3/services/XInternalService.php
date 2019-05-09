<?php

/**
 * Internal Service is used for actions that are used internally in Vidiun applications and might be changed in the future without any notice.
 *
 * @service xInternal
 */
class XInternalService extends VidiunBaseService
{
	/**
	 * Creates new download job for multiple entry ids (comma separated), an email will be sent when the job is done
	 * This service support the following entries: 
	 * - MediaEntry
	 * 	   - Video will be converted using the flavor params id
	 *     - Audio will be downloaded as MP3
	 *     - Image will be downloaded as Jpeg
	 * - MixEntry will be flattened using the flavor params id
	 * - Other entry types are not supported
	 * 
	 * Returns the admin email that the email message will be sent to 
	 * 
	 * @action xAddBulkDownload
	 * @param string $entryIds Comma separated list of entry ids
	 * @param string $flavorParamsId
	 * @return string
	 */
	public function xAddBulkDownloadAction($entryIds, $flavorParamsId = "")
	{
		$flavorParamsDb = null;
		if ($flavorParamsId !== null && $flavorParamsId != "")
		{
			$flavorParamsDb = assetParamsPeer::retrieveByPK($flavorParamsId);
		
			if (!$flavorParamsDb)
				throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $flavorParamsId);
		}
		
		vJobsManager::addBulkDownloadJob($this->getPartnerId(), $this->getVuser()->getPuserId(), $entryIds, $flavorParamsId);
		
		return $this->getVuser()->getEmail();
	}
}
