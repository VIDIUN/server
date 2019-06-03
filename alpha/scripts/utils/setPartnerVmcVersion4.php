<?php
define ('DEBUG', false);
chdir(__DIR__.'/../');
require_once(__DIR__ . '/../bootstrap.php');

$partner_id = $argv[1];
$should_do_flavors_to_web = @$argv[2];
$force_upgrade = @$argv[3];

$partner = PartnerPeer::retrieveByPK($partner_id);
if (!$partner){
	VidiunLog::err("No such partner id");
	die;	
}

if ($partner->getVmcVersion() == '4'){
	VidiunLog::err("Partner already have VMC V4");
	die;
} else if ($partner->getVmcVersion() == '3'){
	if (DEBUG){
		VidiunLog::debug("This was a dry-run, Partner have VMC V3");
		die;
	}
	else{
		$partner->setVmcVersion('4');
		$partner->save();
		VidiunLog::debug("Partner was modified to VMC V4");
		die;
	}
}

handleFlvSrcAssetsWithMbrTag($partner, $should_do_flavors_to_web, $force_upgrade);

$defaultConversionProfileId = $partner->getDefaultConversionProfileId();
if($defaultConversionProfileId)
{
	VidiunLog::debug("Partner already have Default Conversion Profile id: $defaultConversionProfileId");
	if(DEBUG){
		VidiunLog::debug("This was dry-run, exiting");
		die;
	}
	else{
		$partner->setVmcVersion('4');
    	$partner->save();
		VidiunLog::debug("Partner was modified to VMC V4");
		die;
	}    
}

$currentConversionProfileType = $partner->getCurrentConversionProfileType();
if($currentConversionProfileType){
	// convert old conversion profile to new (or check if has new)
	$criteria = new Criteria();
	$criteria->add(ConversionProfilePeer::PARTNER_ID, $partner_id);
	$criteria->addDescendingOrderByColumn(ConversionProfilePeer::UPDATED_AT);
	$oldCp = ConversionProfilePeer::doSelectOne($criteria);
	if($oldCp && !$oldCp->getConversionProfile2Id())
	{
		if (DEBUG){
			VidiunLog::debug("This was dry-run, going to convert old conversion profile to new, existing");
			die;
		}
		else{
			VidiunLog::debug("Converting old conversion profile to new");
			myConversionProfileUtils::createConversionProfile2FromConversionProfile($oldCp);
		}
	}
	if (DEBUG){
		VidiunLog::debug("This was dry-run, going to set partner with DefaultConversionProfileId: ". $partner->getDefaultConversionProfileId());
		die;
	}
	else{
		// set new id as defaultConversionProfileId
		$partner->setDefaultConversionProfileId($oldCp->getConversionProfile2Id());
		VidiunLog::debug("Partner was set with DefaultConversionProfileId: ". $partner->getDefaultConversionProfileId());
		$partner->setVmcVersion('4');
		$partner->save();
		die;
	}
}
else{
	// no currentConversionProfileType, lets see what on default
	$defConversionProfileType = $partner->getFromCustomData('defConversionProfileType');
	if(!is_null($defConversionProfileType))
	{
		$oldCp = myConversionProfileUtils::getConversionProfile($partner->getId(), $defConversionProfileType);
		if(!$oldCp->getConversionProfile2Id() && $oldCp->getPartnerId() == $partner->getId())
		{
			if (DEBUG){
				VidiunLog::debug("This was dry-run, going to convert old default conversion profile according to defConversionProfileType");
				die;
			}
			else{
				myConversionProfileUtils::createConversionProfile2FromConversionProfile($oldCp);
				// set new id on defaultConversionProfileId
				$partner->setDefaultConversionProfileId($oldCp->getConversionProfile2Id());
				$partner->setVmcVersion('4');
				$partner->save();
				VidiunLog::debug("converted old default conversion profile. new DefaultConversionProfileId is: ".$partner->getDefaultConversionProfileId());
				die;
			}
		}
	}
}

// if we didn't exit so far, copy from template
if (DEBUG){
	VidiunLog::debug("This was dry-run, going to copy from template_partner_id");
	die;
}
else{
	$sourcePartner = PartnerPeer::retrieveByPK(vConf::get('template_partner_id'));
	myPartnerUtils::copyConversionProfiles($sourcePartner, $partner);
	VidiunLog::debug("copied from template partner. DefaultConversionProfileId: ".$partner->getDefaultConversionProfileId());
	$partner->setVmcVersion('4');
	$partner->save();
	die;
}

function handleFlvSrcAssetsWithMbrTag(Partner $partner, $should_do_flavors_to_web = NULL, $force_upgrade = NULL){
	$c = new Criteria();
	$c->add(assetPeer::PARTNER_ID, $partner->getId());
	$c->add(assetPeer::TAGS, 'mbr');
	$c->add(assetPeer::FILE_EXT, 'flv');
	$c->add(assetPeer::FLAVOR_PARAMS_ID, 0);
	
	assetPeer::setDefaultCriteriaFilter(false);
	$flavorsCount = assetPeer::doCount($c);	
	assetPeer::setDefaultCriteriaFilter(true);
		
	if($flavorsCount && $should_do_flavors_to_web != 'convert_flavors' && $should_do_flavors_to_web != 'skip_flavors'){
	VidiunLog::debug("found $flavorsCount flavors with only 'mbr' tag.\n
						if you want to convert them run: php {$argv[0]} {$argv[1]} convert_flavors \n
						if you don't want to convert them, run: php {$argv[0]} {$argv[1]} skip_flavors");
		if($force_upgrade == 'force'){
			convertFlavorsTags($partner, $c);
			if(DEBUG){
				VidiunLog::debug("This was dry-run, exiting");
				die;
			}
			else{
				VidiunLog::debug("$flavorsCount flavors were fixed, going to upgrade partner");
			}
		}
		else{
			die;
		}
	}
	else if($flavorsCount && $should_do_flavors_to_web == 'skip_flavors'){
		VidiunLog::debug("Not converting flavors tags and going on");
		if(DEBUG)
		{
			VidiunLog::debug("This was a dry-run, exiting");
			die;
		}	
	}
	else if($flavorsCount && $should_do_flavors_to_web == 'convert_flavors')
	{
		convertFlavorsTags($partner, $c);
		if(DEBUG)
		{
			VidiunLog::debug("This was a dry-run, exiting");
			die;
		}
	}
	else
	{
		VidiunLog::debug("There was no flavor tags to convert");
	}
}

function convertFlavorsTags(Partner $partner, Criteria $c)
{
	assetPeer::setDefaultCriteriaFilter(false);
	$flavors = assetPeer::doSelect($c);
	
	foreach($flavors as $flavor)
	{
		if(DEBUG)
		{
			VidiunLog::debug("select tags, partner_id, is_original, file_ext, id from flavor_asset where id = '{$flavor->getId()}';");
			VidiunLog::debug("update flavor_asset set tags = 'mbr,web' where id = '{$flavor->getId()}';");
		}
		else
		{
			$flavor->setTags('mbr,web');
			$flavor->save();
		}
	}
	assetPeer::setDefaultCriteriaFilter(true);
}