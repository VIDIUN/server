<?php
define('VIDIUN_ROOT_PATH', '/opt/vidiun/app');
require_once(VIDIUN_ROOT_PATH . '/infra/VAutoloader.php');
define("VIDIUN_API_PATH", VIDIUN_ROOT_PATH . "/api_v3");
require_once(VIDIUN_ROOT_PATH . '/alpha/config/vConf.php');
// Autoloader
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "vendor", "propel", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_API_PATH, "lib", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_API_PATH, "services", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "alpha", "plugins", "*")); // needed for testmeDoc
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "plugins", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "generator")); // needed for testmeDoc
VAutoloader::setClassMapFilePath(vConf::get("cache_root_path") . '/plugins/' . basename(__FILE__) . '.cache');
VAutoloader::register();
require_once(VIDIUN_ROOT_PATH . '/vendor/google-api-php-client-1.1.2/src/Google/autoload.php');

// Timezone
date_default_timezone_set(vConf::get("date_default_timezone")); // America/New_York

error_reporting(E_ALL);
VidiunLog::setLogger(new VidiunStdoutLogger());

$dbConf = vConf::getDB();
DbManager::setConfig($dbConf);
DbManager::initialize();

function initClient(VidiunYoutubeApiDistributionProfile $distributionProfile)
{
	$options = array(
		CURLOPT_VERBOSE => true,
		CURLOPT_STDERR => STDOUT,
		CURLOPT_TIMEOUT => 90,
	);

	$client = new Google_Client();
	$client->getIo()->setOptions($options);
	$client->setLogger(new YoutubeApiDistributionEngineLogger($client));
	$client->setClientId($distributionProfile->googleClientId);
	$client->setClientSecret($distributionProfile->googleClientSecret);
	$client->setAccessToken(str_replace('\\', '', $distributionProfile->googleTokenData));

	return $client;
}


if (count($argv) < 3)
{
	echo "Usage: [youtube entry id] [YoutubeApiDistributionProfileId].".PHP_EOL;
	die("Not enough parameters" . "\n");
}

$youtubeEntryId = $argv[1];
$distributionProfileId = $argv[2];
$dbDistributionProfile = DistributionProfilePeer::retrieveByPK($distributionProfileId);
$objectIdentifier = null;
if(!$dbDistributionProfile instanceof YoutubeApiDistributionProfile)
{
	die($dbDistributionProfile . " is not a YoutubeApiDistributionProfile" . "\n");
}

$distributionProfile = new VidiunYoutubeApiDistributionProfile();
$distributionProfile->fromObject($dbDistributionProfile);

$googleClient = initClient($distributionProfile);
$youtube = new Google_Service_YouTube($googleClient);
$statusAnswer = $youtube->videos->listVideos("status", array('id' => $youtubeEntryId));
print_r($statusAnswer);