<?php
/**
 * Export CSV service is used to manage CSV exports of objects
 *
 * @service exportcsv
 * @package api
 * @subpackage services
 */
class ExportCsvService extends VidiunBaseService
{
	const SERVICE_NAME = "exportCsv";
	
	
	
	/**
	 * Creates a batch job that sends an email with a link to download a CSV containing a list of users
	 *
	 * @action userExportToCsv
	 * @actionAlias user.exportToCsv
	 * @param VidiunUserFilter $filter A filter used to exclude specific types of users
	 * @param int $metadataProfileId
	 * @param VidiunCsvAdditionalFieldInfoArray $additionalFields
	 * @return string
	 *
	 * @throws APIErrors::USER_EMAIL_NOT_FOUND
	 * @throws MetadataErrors::INVALID_METADATA_PROFILE
	 * @throws MetadataErrors::METADATA_PROFILE_NOT_SPECIFIED
	 */
	public function userExportToCsvAction (VidiunUserFilter $filter = null, $metadataProfileId = null, $additionalFields = null)
	{
		if($metadataProfileId)
		{
			$metadataProfile = MetadataProfilePeer::retrieveByPK($metadataProfileId);
			if (!$metadataProfile || ($metadataProfile->getPartnerId() != $this->getPartnerId()))
				throw new VidiunAPIException(MetadataErrors::INVALID_METADATA_PROFILE, $metadataProfileId);
		}
		else
		{
			if($additionalFields->count)
				throw new VidiunAPIException(MetadataErrors::METADATA_PROFILE_NOT_SPECIFIED, $metadataProfileId);
		}
		
		if (!$filter)
			$filter = new VidiunUserFilter();
		$dbFilter = new vuserFilter();
		$filter->toObject($dbFilter);
		
		$vuser = $this->getVuser();
		if(!$vuser || !$vuser->getEmail())
			throw new VidiunAPIException(APIErrors::USER_EMAIL_NOT_FOUND, $vuser);
		
		$jobData = new vUsersCsvJobData();
		$jobData->setFilter($dbFilter);
		$jobData->setMetadataProfileId($metadataProfileId);
		$jobData->setAdditionalFields($additionalFields);
		$jobData->setUserMail($vuser->getEmail());
		$jobData->setUserName($vuser->getPuserId());
		
		vJobsManager::addExportCsvJob($jobData, $this->getPartnerId(), ExportObjectType::USER);
		
		return $vuser->getEmail();
	}
	
	
	/**
	 *
	 * Will serve a requested CSV
	 * @action serveCsv
	 *
	 *
	 * @param string $id - the requested file id
	 * @return string
	 */
	public function serveCsvAction($id)
	{
		$file_path = self::generateCsvPath($id, $this->getVs());
		
		return $this->dumpFile($file_path, 'text/csv');
	}
	
	/**
	 * Generic CSV file path generator - used from any action which calls the generateCsvPath
	 *
	 * @param string $id
	 * @param string $vs
	 * @return string
	 * @throws VidiunAPIException
	 */
	public static function generateCsvPath($id, $vs)
	{
		if(!preg_match('/^\w+\.csv$/', $id))
			throw new VidiunAPIException(VidiunErrors::INVALID_ID, $id);
		
		// VS verification - we accept either admin session or download privilege of the file
		if(!$vs->verifyPrivileges(vs::PRIVILEGE_DOWNLOAD, $id))
			VExternalErrors::dieError(VExternalErrors::ACCESS_CONTROL_RESTRICTED);
		
		$partner_id = vCurrentContext::getCurrentPartnerId();
		$folderPath = "/content/exportcsv/$partner_id";
		$fullPath = myContentStorage::getFSContentRootPath() . $folderPath;
		$file_path = "$fullPath/$id";
		
		return $file_path;
	}
	
}