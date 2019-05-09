<?php

define('VIDIUN_ROOT_PATH', realpath(dirname(__FILE__) . '/../../../../..'));
require_once(VIDIUN_ROOT_PATH . '/infra/VAutoloader.php');

define("VIDIUN_API_PATH", VIDIUN_ROOT_PATH . "/api_v3");

require_once(VIDIUN_ROOT_PATH . '/alpha/config/vConf.php');
// Autoloader
require_once(VIDIUN_ROOT_PATH.DIRECTORY_SEPARATOR."infra".DIRECTORY_SEPARATOR."VAutoloader.php");
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "vendor", "propel", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_API_PATH, "lib", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_API_PATH, "services", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "alpha", "plugins", "*")); // needed for testmeDoc
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "plugins", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "generator")); // needed for testmeDoc
VAutoloader::setClassMapFilePath(vConf::get("cache_root_path") . '/plugins/' . basename(__FILE__) . '.cache');
//VAutoloader::dumpExtra();
VAutoloader::register();

// Timezone
date_default_timezone_set(vConf::get("date_default_timezone")); // America/New_York

error_reporting(E_ALL);
VidiunLog::setLogger(new VidiunStdoutLogger());

$dbConf = vConf::getDB();
DbManager::setConfig($dbConf);
DbManager::initialize();

vCurrentContext::$ps_vesion = 'ps3';

$entryId = '0_bs1fapzx';

/*$matches = null;
if (preg_match ( "/x0y.*.err/" , '/pub/in/x0y.title.err' , $matches))
{
	print_r($matches);
	print_r(preg_split ("/\./", $matches[0]));
}
else
{
 echo 'non';
}
return;
if(isset($argv[1]))
	$entryId = $argv[1];

foreach($argv as $arg)
{
	$matches = null;
	if(preg_match('/(.*)=(.*)/', $arg, $matches))
	{
		$field = $matches[1];
//		$providerData->$field = $matches[2];
	}
}

		$fileTransferMgr = vFileTransferMgr::getInstance(vFileTransferMgrType::FTP);
		if(!$fileTransferMgr)
			throw new Exception("SFTP manager not loaded");
			
		$fileTransferMgr->login('ftp-int.vzw.real.com', 'vp_foxsports', 'X4ul3ap');
		print_r($fileTransferMgr->listDir("/pub/in"));
//		$fileTransferMgr->putFile($destFile, $srcFile, true);

		return;*/
$entry = entryPeer::retrieveByPKNoFilter($entryId);
$mrss = vMrssManager::getEntryMrss($entry);
file_put_contents('mrss.xml', $mrss);
VidiunLog::debug("MRSS [$mrss]");

$distributionJobData = new VidiunDistributionSubmitJobData();

$dbDistributionProfile = DistributionProfilePeer::retrieveByPK(3);
$distributionProfile = new VidiunDailymotionDistributionProfile();
$distributionProfile->fromObject($dbDistributionProfile);
$distributionJobData->distributionProfileId = $distributionProfile->id;


$distributionJobData->distributionProfile = $distributionProfile;

$dbEntryDistribution = EntryDistributionPeer::retrieveByPK(24);
$entryDistribution = new VidiunEntryDistribution();
$entryDistribution->fromObject($dbEntryDistribution);
$distributionJobData->entryDistributionId = $entryDistribution->id;
$distributionJobData->entryDistribution = $entryDistribution;

$myp = new DailymotionDistributionProfile();
print_r($myp->validateForSubmission($dbEntryDistribution, "submit"));


$providerData = new VidiunDailymotionDistributionJobProviderData($distributionJobData);
$distributionJobData->providerData = $providerData;

//file_put_contents('out.xml', $providerData->xml);
//VidiunLog::debug("XML [$providerData->xml]");

//return;
$engine = new DailymotionDistributionEngine();
$engine->submit($distributionJobData);


//$xml = new VDOMDocument();
//if(!$xml->loadXML($mrss))
//{
//	VidiunLog::err("MRSS not is not valid XML:\n$mrss\n");
//	exit;
//}
//
//$xslPath = 'submit.xsl';
//$xsl = new VDOMDocument();
//$xsl->load($xslPath);
//			
//// set variables in the xsl
//$varNodes = $xsl->getElementsByTagName('variable');
//foreach($varNodes as $varNode)
//{
//	$nameAttr = $varNode->attributes->getNamedItem('name');
//	if(!$nameAttr)
//		continue;
//		
//	$name = $nameAttr->value;
//	if($name && $distributionJobData->$name)
//	{
//		$varNode->textContent = $distributionJobData->$name;
//		$varNode->appendChild($xsl->createTextNode($distributionJobData->$name));
//		VidiunLog::debug("Set variable [$name] to [{$distributionJobData->$name}]");
//	}
//}
//
//$proc = new XSLTProcessor;
//$proc->registerPHPFunctions();
//$proc->importStyleSheet($xsl);
//
//$xml = $proc->transformToDoc($xml);
//if(!$xml)
//{
//	VidiunLog::err("Transform returned false");
//	exit;
//}
//
//$xml = $xml->saveXML();
//
//file_put_contents('out.xml', $xml);
//VidiunLog::debug("XML [$xml]");
