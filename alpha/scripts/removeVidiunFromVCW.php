 <?php

ini_set("memory_limit","256M");

require_once(__DIR__ . '/bootstrap.php');

if (!$argc)
{
	die('pleas provide partner id as input' . PHP_EOL . 
		'to run script: ' . basename(__FILE__) . ' X' . PHP_EOL . 
		'whereas X is partner id' . PHP_EOL);
}

$partnerId = $argv[0];

$dbConf = vConf::getDB();
DbManager::setConfig ( $dbConf );
DbManager::initialize ();

$c = new Criteria();
$c->add(uiConfPeer::SWF_URL, "%vcw%",Criteria::LIKE);
$c->add(uiConfPeer::OBJ_TYPE , uiConf::UI_CONF_TYPE_CW, Criteria::EQUAL);
$c->add(uiConfPeer::PARTNER_ID, $partnerId, Criteria::EQUAL);

$vcwUiconfs = uiConfPeer::doSelect($c);


if (!count($vcwUiconfs))
{
	exit;
}

$fileName = "/manual_uiconfs_paths.log";
$flog = fopen($fileName,'a+');
//Run a loop for each uiConf to get its filesync key, thus acquiring its confile
foreach ($vcwUiconfs as $vcwUiconf)
{
	/* @var $vcwUiconf uiConf */
	$vcwUiconfFilesyncKey = $vcwUiconf->getSyncKey(uiConf::FILE_SYNC_UICONF_SUB_TYPE_DATA);
	$vcwConfile = vFileSyncUtils::file_get_contents($vcwUiconfFilesyncKey, false , false);
	
	if (!$vcwConfile)
	{
		continue;
	}
		
	$vcwConfileXML = new SimpleXMLElement($vcwConfile);

	$path = '//provider[@id="vidiun" or @name="vidiun"]';
	
	$nodesToRemove = $vcwConfileXML->xpath($path);
	
	if (!count($nodesToRemove))
	{
		continue;
	}
	
	
	if ($vcwUiconf->getCreationMode() != uiConf::UI_CONF_CREATION_MODE_MANUAL)
	{
		//No point in this "for" loop if we can't save the UIConf.
		foreach ($nodesToRemove as $nodeToRemove)
		{
			$nodeToRemoveDom = dom_import_simplexml($nodeToRemove);

			$nodeToRemoveDom->parentNode->removeChild($nodeToRemoveDom);
		}
		$vcwConfile = $vcwConfileXML->saveXML();
		$vcwUiconf->setConfFile($vcwConfile);
		$vcwUiconf->save();
	}
	else
	{
		$confilePath = $vcwUiconf->getConfFilePath()."\n";
		fwrite($flog, $confilePath);
	}
	//$vcw_uiconf_filesync_key = $vcw_uiconf->getSyncKey(uiConf::FILE_SYNC_UICONF_SUB_TYPE_DATA);
	//vFileSyncUtils::file_put_contents($vcw_uiconf_filesync_key, $vcw_confile , false);
}
fclose($flog);
