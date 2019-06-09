<?php
chdir(__DIR__ . '/../');
require_once(__DIR__ . '/../bootstrap.php');


$dropFoldersCriteria = new Criteria();
$dropFoldersCriteria->add(DropFolderPeer::STATUS, DropFolderStatus::ENABLED);
$dropFoldersCriteria->add(DropFolderPeer::TYPE, DropFolderType::SFTP);
$dropFolders = DropFolderPeer::doSelect($dropFoldersCriteria);

VidiunLog::debug("Drop folders count [" . count($dropFolders) . "]");
foreach($dropFolders as $dropFolder)
{
	/* @var $dropFolder SftpDropFolder */
	$dropFolderId = $dropFolder->getId();
	$passed = true;
	
	$sftp = vFileTransferMgr::getInstance(vFileTransferMgrType::SFTP_SEC_LIB);
	/* @var $sftp sftpSecLibMgr */
	
	$dropFolderPublicKeyFile = uniqid('sftp-pub-');
	$dropFolderPrivateKeyFile = uniqid('sftp-pvt-');
	try
	{
		if($dropFolder->getSshPrivateKey())
		{
			file_put_contents($dropFolderPublicKeyFile, $dropFolder->getSshPublicKey());
			file_put_contents($dropFolderPrivateKeyFile, $dropFolder->getSshPrivateKey());
			
			$sftp->loginPubKey($dropFolder->getSshHost(), $dropFolder->getSshUsername(), $dropFolderPublicKeyFile, $dropFolderPrivateKeyFile, $dropFolder->getSshPassPhrase(), $dropFolder->getSshPort());
			
			unlink($dropFolderPublicKeyFile);
			unlink($dropFolderPrivateKeyFile);
		}
		else
		{
			$sftp->login($dropFolder->getSshHost(), $dropFolder->getSshUsername(), $dropFolder->getSshPassword(), $dropFolder->getSshPort());
		}
	}
	catch (Exception $e)
	{
		VidiunLog::err("Drop folder [$dropFolderId] login failed: " . $e->getMessage());
		
		if(file_exists($dropFolderPublicKeyFile))
			unlink($dropFolderPublicKeyFile);
		if(file_exists($dropFolderPrivateKeyFile))
			unlink($dropFolderPrivateKeyFile);
			
		continue;
	}
	
	$path = $dropFolder->getPath();
	$elements = $sftp->listDir($path);
	foreach($elements as $element)
	{
		if($element == '.' || $element == '..')
			continue;
			
		try
		{
			if(!$sftp->fileExists("$path/$element"))
			{
				VidiunLog::debug("Drop folder [$dropFolderId] file [$path/$element] listed but does not exist");
				$passed = false;
			}
		}
		catch (Exception $e)
		{
			VidiunLog::debug("Drop folder [$dropFolderId] file [$path/$element] existance check failed: " . $e->getMessage());
			$passed = false;
		}
			
		try
		{
			$sftp->fileSize("$path/$element");
		}
		catch (Exception $e)
		{
			VidiunLog::debug("Drop folder [$dropFolderId] file [$path/$element] size check failed: " . $e->getMessage());
			$passed = false;
		}
		
		if(!$passed)
			break;
	}
	
	if($passed)
		VidiunLog::notice("Drop folder [$dropFolderId] passed");
	else
		VidiunLog::err("Drop folder [$dropFolderId] failed");
}





$storageProfileCriteria = new Criteria();
$storageProfileCriteria->add(StorageProfilePeer::STATUS, StorageProfile::STORAGE_STATUS_AUTOMATIC);
$storageProfileCriteria->add(StorageProfilePeer::PROTOCOL, StorageProfile::STORAGE_PROTOCOL_SFTP);
$storageProfiles = StorageProfilePeer::doSelect($storageProfileCriteria);

VidiunLog::debug("Storage profiles count [" . count($storageProfiles) . "]");
foreach($storageProfiles as $storageProfile)
{
	/* @var $storageProfile StorageProfile */
	$storageProfileId = $storageProfile->getId();
	$passed = true;
	
	$sftp = vFileTransferMgr::getInstance(vFileTransferMgrType::SFTP_SEC_LIB);
	/* @var $sftp sftpSecLibMgr */
	
	try
	{
		$sftp->login($storageProfile->getStorageUrl(), $storageProfile->getStorageUsername(), $storageProfile->getStoragePassword());
	}
	catch (Exception $e)
	{
		VidiunLog::err("Storage profile [$storageProfileId] login failed: " . $e->getMessage());
		continue;
	}
	
	$path = $storageProfile->getStorageBaseDir();
	$elements = $sftp->listDir($path);
	foreach($elements as $element)
	{
		if($element == '.' || $element == '..')
			continue;
			
		try
		{
			if(!$sftp->fileExists("$path/$element"))
			{
				VidiunLog::debug("Storage profile [$storageProfileId] file [$path/$element] listed but does not exist");
				$passed = false;
			}
		}
		catch (Exception $e)
		{
			VidiunLog::debug("Storage profile [$storageProfileId] file [$path/$element] existance check failed: " . $e->getMessage());
			$passed = false;
		}
			
		try
		{
			$sftp->fileSize("$path/$element");
		}
		catch (Exception $e)
		{
			VidiunLog::debug("Storage profile [$storageProfileId] file [$path/$element] size check failed: " . $e->getMessage());
			$passed = false;
		}
		
		if(!$passed)
			break;
	}
	
	if($passed)
		VidiunLog::notice("Storage profile [$storageProfileId] passed");
	else
		VidiunLog::err("Storage profile [$storageProfileId] failed");
}


$distributionProfileCriteria = new Criteria();
$distributionProfileCriteria->add(DistributionProfilePeer::STATUS, DistributionProfileStatus::ENABLED);
$distributionProfileCriteria->add(DistributionProfilePeer::PROVIDER_TYPE, FtpDistributionPlugin::getDistributionProviderTypeCoreValue(FtpDistributionProviderType::FTP));
$ftpDistributionProfiles = DistributionProfilePeer::doSelect($distributionProfileCriteria);

VidiunLog::debug("FTP Distribution profiles count [" . count($ftpDistributionProfiles) . "]");
foreach($ftpDistributionProfiles as $ftpDistributionProfile)
{
	/* @var $ftpDistributionProfile FtpDistributionProfile */
	$ftpDistributionProfileId = $ftpDistributionProfile->getId();
	if($ftpDistributionProfile->getProtocol() != vFileTransferMgrType::SFTP)
	{
		VidiunLog::debug("FTP Distribution profile [$ftpDistributionProfileId] different protocol [" . $ftpDistributionProfile->getProtocol() . "]");
		continue;
	}
		
	$passed = true;
	
	$sftp = vFileTransferMgr::getInstance(vFileTransferMgrType::SFTP_SEC_LIB);
	/* @var $sftp sftpSecLibMgr */
	
	$ftpDistributionProfilePublicKeyFile = uniqid('sftp-pub-');
	$ftpDistributionProfilePrivateKeyFile = uniqid('sftp-pvt-');
	try
	{
		if($ftpDistributionProfile->getSftpPrivateKey())
		{
			file_put_contents($ftpDistributionProfilePublicKeyFile, $ftpDistributionProfile->getSftpPublicKey());
			file_put_contents($ftpDistributionProfilePrivateKeyFile, $ftpDistributionProfile->getSftpPrivateKey());
			
			$sftp->loginPubKey($ftpDistributionProfile->getHost(), $ftpDistributionProfile->getUsername(), $ftpDistributionProfilePublicKeyFile, $ftpDistributionProfilePrivateKeyFile, $ftpDistributionProfile->getPassphrase(), $ftpDistributionProfile->getPort());
			
			unlink($ftpDistributionProfilePublicKeyFile);
			unlink($ftpDistributionProfilePrivateKeyFile);
			
		}
		else
		{
			$sftp->login($ftpDistributionProfile->getHost(), $ftpDistributionProfile->getUsername(), $ftpDistributionProfile->getPassword(), $ftpDistributionProfile->getPort());
		}
	}
	catch (Exception $e)
	{
		VidiunLog::err("FTP Distribution profile [$ftpDistributionProfileId] login failed: " . $e->getMessage());
		
		if(file_exists($ftpDistributionProfilePublicKeyFile))
			unlink($ftpDistributionProfilePublicKeyFile);
		if(file_exists($ftpDistributionProfilePrivateKeyFile))
			unlink($ftpDistributionProfilePrivateKeyFile);
			
		continue;
	}
	
	$path = $ftpDistributionProfile->getBasePath();
	$elements = $sftp->listDir($path);
	foreach($elements as $element)
	{
		if($element == '.' || $element == '..')
			continue;
			
		try
		{
			if(!$sftp->fileExists("$path/$element"))
			{
				VidiunLog::debug("FTP Distribution profile [$ftpDistributionProfileId] file [$path/$element] listed but does not exist");
				$passed = false;
			}
		}
		catch (Exception $e)
		{
			VidiunLog::debug("FTP Distribution profile [$ftpDistributionProfileId] file [$path/$element] existance check failed: " . $e->getMessage());
			$passed = false;
		}
			
		try
		{
			$sftp->fileSize("$path/$element");
		}
		catch (Exception $e)
		{
			VidiunLog::debug("FTP Distribution profile [$ftpDistributionProfileId] file [$path/$element] size check failed: " . $e->getMessage());
			$passed = false;
		}
		
		if(!$passed)
			break;
	}
	
	if($passed)
		VidiunLog::notice("FTP Distribution profile [$ftpDistributionProfileId] passed");
	else
		VidiunLog::err("FTP Distribution profile [$ftpDistributionProfileId] failed");
}




$distributionProfileCriteria = new Criteria();
$distributionProfileCriteria->add(DistributionProfilePeer::STATUS, DistributionProfileStatus::ENABLED);
$distributionProfileCriteria->add(DistributionProfilePeer::PROVIDER_TYPE, HuluDistributionPlugin::getDistributionProviderTypeCoreValue(HuluDistributionProviderType::HULU));
$huluDistributionProfiles = DistributionProfilePeer::doSelect($distributionProfileCriteria);

VidiunLog::debug("Hulu Distribution profiles count [" . count($huluDistributionProfiles) . "]");
foreach($huluDistributionProfiles as $huluDistributionProfile)
{
	/* @var $huluDistributionProfile HuluDistributionProfile */
	$huluDistributionProfileId = $huluDistributionProfile->getId();
		
	$passed = true;
	
	$sftp = vFileTransferMgr::getInstance(vFileTransferMgrType::SFTP_SEC_LIB);
	/* @var $sftp sftpSecLibMgr */
	
	try
	{
		$sftp->login($huluDistributionProfile->getSftpHost(), $huluDistributionProfile->getSftpLogin(), $huluDistributionProfile->getSftpPass());
	}
	catch (Exception $e)
	{
		VidiunLog::err("Hulu Distribution profile [$huluDistributionProfileId] login failed: " . $e->getMessage());
		continue;
	}
	
	$path = '/home/' . $huluDistributionProfile->getSftpLogin() . '/upload/';
	$elements = $sftp->listDir($path);
	foreach($elements as $element)
	{
		if($element == '.' || $element == '..')
			continue;
			
		try
		{
			if(!$sftp->fileExists("$path/$element"))
			{
				VidiunLog::debug("Hulu Distribution profile [$huluDistributionProfileId] file [$path/$element] listed but does not exist");
				$passed = false;
			}
		}
		catch (Exception $e)
		{
			VidiunLog::debug("Hulu Distribution profile [$huluDistributionProfileId] file [$path/$element] existance check failed: " . $e->getMessage());
			$passed = false;
		}
			
		try
		{
			$sftp->fileSize("$path/$element");
		}
		catch (Exception $e)
		{
			VidiunLog::debug("Hulu Distribution profile [$huluDistributionProfileId] file [$path/$element] size check failed: " . $e->getMessage());
			$passed = false;
		}
		
		if(!$passed)
			break;
	}
	
	if($passed)
		VidiunLog::notice("Hulu Distribution profile [$huluDistributionProfileId] passed");
	else
		VidiunLog::err("Hulu Distribution profile [$huluDistributionProfileId] failed");
}




$distributionProfileCriteria = new Criteria();
$distributionProfileCriteria->add(DistributionProfilePeer::STATUS, DistributionProfileStatus::ENABLED);
$distributionProfileCriteria->add(DistributionProfilePeer::PROVIDER_TYPE, QuickPlayDistributionPlugin::getDistributionProviderTypeCoreValue(QuickPlayDistributionProviderType::QUICKPLAY));
$quickPlayDistributionProfiles = DistributionProfilePeer::doSelect($distributionProfileCriteria);

VidiunLog::debug("Quick Play Distribution profiles count [" . count($quickPlayDistributionProfiles) . "]");
foreach($quickPlayDistributionProfiles as $quickPlayDistributionProfile)
{
	/* @var $quickPlayDistributionProfile QuickPlayDistributionProfile */
	$quickPlayDistributionProfileId = $quickPlayDistributionProfile->getId();
	$passed = true;
	
	$sftp = vFileTransferMgr::getInstance(vFileTransferMgrType::SFTP_SEC_LIB);
	/* @var $sftp sftpSecLibMgr */
	
	try
	{
		$sftp->login($quickPlayDistributionProfile->getSftpHost(), $quickPlayDistributionProfile->getSftpLogin(), $quickPlayDistributionProfile->getSftpPass());
	}
	catch (Exception $e)
	{
		VidiunLog::err("Quick Play Distribution profile [$quickPlayDistributionProfileId] login failed: " . $e->getMessage());
		continue;
	}
	
	$path = $quickPlayDistributionProfile->getSftpBasePath();
	$elements = $sftp->listDir($path);
	foreach($elements as $element)
	{
		if($element == '.' || $element == '..')
			continue;
			
		try
		{
			if(!$sftp->fileExists("$path/$element"))
			{
				VidiunLog::debug("Quick Play Distribution profile [$quickPlayDistributionProfileId] file [$path/$element] listed but does not exist");
				$passed = false;
			}
		}
		catch (Exception $e)
		{
			VidiunLog::debug("Quick Play Distribution profile [$quickPlayDistributionProfileId] file [$path/$element] existance check failed: " . $e->getMessage());
			$passed = false;
		}
			
		try
		{
			$sftp->fileSize("$path/$element");
		}
		catch (Exception $e)
		{
			VidiunLog::debug("Quick Play Distribution profile [$quickPlayDistributionProfileId] file [$path/$element] size check failed: " . $e->getMessage());
			$passed = false;
		}
		
		if(!$passed)
			break;
	}
	
	if($passed)
		VidiunLog::notice("Quick Play Distribution profile [$quickPlayDistributionProfileId] passed");
	else
		VidiunLog::err("Quick Play Distribution profile [$quickPlayDistributionProfileId] failed");
}




$distributionProfileCriteria = new Criteria();
$distributionProfileCriteria->add(DistributionProfilePeer::STATUS, DistributionProfileStatus::ENABLED);
$distributionProfileCriteria->add(DistributionProfilePeer::PROVIDER_TYPE, YouTubeDistributionPlugin::getDistributionProviderTypeCoreValue(YouTubeDistributionProviderType::YOUTUBE));
$youTubeDistributionProfiles = DistributionProfilePeer::doSelect($distributionProfileCriteria);

VidiunLog::debug("YouTube Distribution profiles count [" . count($youTubeDistributionProfiles) . "]");
foreach($youTubeDistributionProfiles as $youTubeDistributionProfile)
{
	/* @var $youTubeDistributionProfile YouTubeDistributionProfile */
	$youTubeDistributionProfileId = $youTubeDistributionProfile->getId();
	$passed = true;
	
	$sftp = vFileTransferMgr::getInstance(vFileTransferMgrType::SFTP_SEC_LIB);
	/* @var $sftp sftpSecLibMgr */
	
	$youTubeDistributionProfilePublicKeyFile = uniqid('sftp-pub-');
	$youTubeDistributionProfilePrivateKeyFile = uniqid('sftp-pvt-');
	try
	{
		file_put_contents($youTubeDistributionProfilePublicKeyFile, $youTubeDistributionProfile->getSftpPublicKey());
		file_put_contents($youTubeDistributionProfilePrivateKeyFile, $youTubeDistributionProfile->getSftpPrivateKey());
		
		$sftp->loginPubKey($youTubeDistributionProfile->getSftpHost(), $youTubeDistributionProfile->getSftpLogin(), $youTubeDistributionProfilePublicKeyFile, $youTubeDistributionProfilePrivateKeyFile);
		
		unlink($youTubeDistributionProfilePublicKeyFile);
		unlink($youTubeDistributionProfilePrivateKeyFile);
			
	}
	catch (Exception $e)
	{
		VidiunLog::err("YouTube Distribution profile [$youTubeDistributionProfileId] login failed: " . $e->getMessage());
		
		if(file_exists($youTubeDistributionProfilePublicKeyFile))
			unlink($youTubeDistributionProfilePublicKeyFile);
		if(file_exists($youTubeDistributionProfilePrivateKeyFile))
			unlink($youTubeDistributionProfilePrivateKeyFile);
			
		continue;
	}
	
	$path = '/';
	$elements = $sftp->listDir($path);
	foreach($elements as $element)
	{
		if($element == '.' || $element == '..')
			continue;
			
		try
		{
			if(!$sftp->fileExists("$path/$element"))
			{
				VidiunLog::debug("YouTube Distribution profile [$youTubeDistributionProfileId] file [$path/$element] listed but does not exist");
				$passed = false;
			}
		}
		catch (Exception $e)
		{
			VidiunLog::debug("YouTube Distribution profile [$youTubeDistributionProfileId] file [$path/$element] existance check failed: " . $e->getMessage());
			$passed = false;
		}
			
		try
		{
			$sftp->fileSize("$path/$element");
		}
		catch (Exception $e)
		{
			VidiunLog::debug("YouTube Distribution profile [$youTubeDistributionProfileId] file [$path/$element] size check failed: " . $e->getMessage());
			$passed = false;
		}
		
		if(!$passed)
			break;
	}
	
	if($passed)
		VidiunLog::notice("YouTube Distribution profile [$youTubeDistributionProfileId] passed");
	else
		VidiunLog::err("YouTube Distribution profile [$youTubeDistributionProfileId] failed");
}






$batchJobCriteria = new Criteria();
$batchJobCriteria->add(BatchJobPeer::STATUS, BatchJob::BATCHJOB_STATUS_FINISHED);
$batchJobCriteria->add(BatchJobPeer::JOB_TYPE, BatchJobType::IMPORT);
$batchJobCriteria->add(BatchJobPeer::DATA, '%sftp%', Criteria::LIKE);
$batchJobCriteria->addDescendingOrderByColumn(BatchJobPeer::ID);
$batchJobCriteria->setLimit(20);
$batchJobs = BatchJobPeer::doSelect($batchJobCriteria);

while(count($batchJobs))
{
	VidiunLog::debug("Import Batch Jobs count [" . count($batchJobs) . "]");
	
	$batchJobId = null;
	foreach($batchJobs as $batchJob)
	{
		/* @var $batchJob BatchJob */
		$batchJobId = $batchJob->getId();
		$batchJobPartnerId = $batchJob->getPartnerId();
		
		$jobData = $batchJob->getData();
		if(!($jobData instanceof vImportJobData))
			continue;
			
		$parsedUrl = parse_url($jobData->getSrcFileUrl());
		
		$host = isset($parsedUrl['host']) ? $parsedUrl['host'] : null;	
		if (!$host) 
		{
		    VidiunLog::err("Import Batch Job [$batchJobPartnerId::$batchJobId] Missing host");
		    continue;
		}
	
		$remotePath = isset($parsedUrl['path']) ? $parsedUrl['path'] : null;
		if (!$remotePath) 
		{
		    VidiunLog::err("Import Batch Job [$batchJobPartnerId::$batchJobId] Missing path");
		    continue;
		}
		
		$host = preg_replace('/:\d+$/', '', $host);
		$port = isset($parsedUrl['port']) ? $parsedUrl['port'] : null;
		$username = isset($parsedUrl['user']) ? $parsedUrl['user'] : null;
		$password = isset($parsedUrl['pass']) ? $parsedUrl['pass'] : null;
		
		$privateKey = null;
		$publicKey = null;
		if($jobData instanceof vSshImportJobData)
		{
			$privateKey = $jobData->getPrivateKey() ? $jobData->getPrivateKey() : null;
			$publicKey  = $jobData->getPublicKey() ? $jobData->getPublicKey() : null;
			$passPhrase = $jobData->getPassPhrase() ? $jobData->getPassPhrase() : null;
		}
		
		VidiunLog::debug("Import Batch Job [$batchJobPartnerId::$batchJobId] host [$host] port [$port] remotePath [$remotePath] username [$username] password [$password]");
		if ($privateKey || $publicKey) {
		    VidiunLog::debug("Private Key: $privateKey");
		    VidiunLog::debug("Public Key: $publicKey");
		}
		
		$sftp = vFileTransferMgr::getInstance(vFileTransferMgrType::SFTP_SEC_LIB);
		
		$publicKeyFile = uniqid('sftp-pub-');
		$privateKeyFile = uniqid('sftp-pvt-');
		try
		{
			if (!$privateKey || !$publicKey) 
			{
			    $sftp->login($host, $username, $password, $port);
			}
			else 
			{
				file_put_contents($publicKeyFile, $publicKey);
				file_put_contents($privateKeyFile, $privateKey);
				
			    $sftp->loginPubKey($host, $username, $publicKeyFile, $privateKeyFile, $passPhrase, $port);
			    
				unlink($publicKeyFile);
				unlink($privateKeyFile);
			}
		}
		catch (Exception $e)
		{
			VidiunLog::err("Import Batch Job [$batchJobPartnerId::$batchJobId] login failed: " . $e->getMessage());
			
			if(file_exists($publicKeyFile))
				unlink($publicKeyFile);
			if(file_exists($privateKeyFile))
				unlink($privateKeyFile);
				
			continue;
		}
		
		try
		{
			$fileExists = $sftp->fileExists($remotePath);
			if (!$fileExists) 
			{
			    VidiunLog::err("Import Batch Job [$batchJobPartnerId::$batchJobId] remote file [$remotePath] does not exist");
			    continue;
			}
		}
		catch (Exception $e)
		{
		    VidiunLog::err("Import Batch Job [$batchJobPartnerId::$batchJobId] remote file [$remotePath] existance check failed: " . $e->getMessage());
		    continue;
		}
		
		try
		{
			$fileSize = $sftp->fileSize($remotePath);
		}
		catch (Exception $e)
		{
		    VidiunLog::err("Import Batch Job [$batchJobPartnerId::$batchJobId] remote file [$remotePath] size check failed: " . $e->getMessage());
		    continue;
		}
		
		VidiunLog::notice("Import Batch Job [$batchJobPartnerId::$batchJobId] passed");
	}
	
	$batchJobCriteria->add(BatchJobPeer::ID, $batchJobId, Criteria::LESS_THAN);
}



VidiunLog::debug("Done");
