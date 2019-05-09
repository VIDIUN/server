<?php
require_once('/opt/vidiun/app/alpha/scripts/bootstrap.php');
if ($argc < 3)
    die("Usage: php mergeDuplicateUsersByPusersList.php partnerId pusersIdsFilePath <realrun | dryrun>"."\n");

$partnerId = $argv[1] ;
$pusersFilePath = $argv[2];
$dryrun = true;
if($argc == 4 && $argv[3] == 'realrun')
    $dryrun = false;
VidiunStatement::setDryRun($dryrun);
VidiunLog::debug('dryrun value: ['.$dryrun.']');
$pusers = file ($pusersFilePath) or die ('Could not read file'."\n");

foreach ($pusers as $puserId) {
    $puserId = trim($puserId);
    $vusersArray = getAllDuplicatedVusersForPuser ($puserId, $partnerId);
    if (!$vusersArray){
        VidiunLog::debug('ERROR: couldn\'t find vusers with puser id ['.$puserId.']');
        continue;
    }
    $baseVuser = findVuserWithMaxEntries($vusersArray, $partnerId);
    mergeUsersToBaseUser($vusersArray, $baseVuser, $partnerId);
    VidiunLog::debug('finished handling puserId ['.$puserId.']');
}

function mergeUsersToBaseUser($vusersArray, $baseVuser, $partnerId){
    foreach ($vusersArray as $vuser){
        if($vuser->getId() != $baseVuser->getId()){
            changeVuserForEntries($vuser, $baseVuser, $partnerId);
            changeVuserForCuePoints($vuser, $baseVuser, $partnerId);
            changeVuserForCategoryVusers($vuser, $baseVuser, $partnerId);
            changeVuserForUserEntries($vuser, $baseVuser, $partnerId);
            changeVuserForVuserVgroup($vuser, $baseVuser, $partnerId);
            changeVuserForVvote($vuser, $baseVuser, $partnerId);
            deleteVuser($vuser);
            VidiunLog::debug('finished handling vuserId ['.$vuser->getId().']');
        }
    }
}

function getAllDuplicatedVusersForPuser ($puserId, $partnerId){
    VidiunLog::debug('retriving the vusers for partnerId ['.$partnerId.'] with puserId ['.$puserId.']');
    $Critiria = new Criteria();
    $Critiria->add(vuserPeer::PUSER_ID, $puserId);
    $Critiria->add(vuserPeer::PARTNER_ID, $partnerId);
    return vuserPeer::doSelect($Critiria);
}

function findVuserWithMaxEntries ($vusersArray, $partnerId){
    $baseVuser=null;
    $maxEntriesNum=0;
    foreach ($vusersArray as $vuser){
        $Critiria = VidiunCriteria::create(entryPeer::OM_CLASS);
        $Critiria->add(entryPeer::VUSER_ID, $vuser->getId());
        $Critiria->add(entryPeer::PARTNER_ID, $partnerId);
        $entries = entryPeer::doSelect($Critiria);
        $entriesNum = $Critiria->getRecordsCount();
        VidiunLog::debug('vuserId: ['.$vuser->getId().'] entries num: ['.$entriesNum.']');
        if($entriesNum >= $maxEntriesNum){
            $baseVuser = $vuser;
            $maxEntriesNum = $entriesNum;
        }
    }
    VidiunLog::debug('vuserId: ['.$baseVuser->getId().'] entries num: ['.$maxEntriesNum.'] - max value');
    return $baseVuser;
}

function changeVuserForEntries ($vuser, $baseVuser, $partnerId) {
    $Critiria = VidiunCriteria::create(entryPeer::OM_CLASS);
    $Critiria->add(entryPeer::VUSER_ID, $vuser->getId());
    $Critiria->add(entryPeer::PARTNER_ID, $partnerId);
    $entriesArray = entryPeer::doSelect($Critiria);
    foreach ($entriesArray as $entry) {
        VidiunLog::debug('set VuserId ['.$baseVuser->getId().'] instead of ['.$entry->getVuser()->getId().'] for entryId ['.$entry->getId().']');
        $entry->setVuserId($baseVuser->getId());
        $entry->save();
    }
}

function changeVuserForCuePoints ($vuser, $baseVuser, $partnerId)
{
    $Critiria = VidiunCriteria::create(CuePointPeer::OM_CLASS);
    $Critiria->add(CuePointPeer::VUSER_ID, $vuser->getId());
    $Critiria->add(CuePointPeer::PARTNER_ID, $partnerId);
    $cuePointsArray = CuePointPeer::doSelect($Critiria);
    foreach ($cuePointsArray as $cuePoint) {
        VidiunLog::debug('set VuserId [' . $baseVuser->getId() . '] instead of [' . $cuePoint->getVuserId() . '] for cuePointId [' . $cuePoint->getId() . ']');
        $cuePoint->setvuserId($baseVuser->getId());
        $cuePoint->save();
    }
}

function changeVuserForCategoryVusers ($vuser, $baseVuser, $partnerId) {
    $Critiria = new Criteria();
    $Critiria->add(categoryVuserPeer::VUSER_ID, $vuser->getId());
    $Critiria->add(categoryVuserPeer::PARTNER_ID, $partnerId);
    $categoryUserArray = categoryVuserPeer::doSelect($Critiria);
    foreach ($categoryUserArray as $categoryUser) {
        VidiunLog::debug('set VuserId ['.$baseVuser->getId().'] instead of ['.$categoryUser->getVuser()->getId().'] for categoryUserId ['.$categoryUser->getId().']');
        $categoryUser->setvuserId($baseVuser->getId());
        $categoryUser->save();
    }
}

function changeVuserForUserEntries($vuser, $baseVuser, $partnerId){
    $Critiria = new Criteria();
    $Critiria->add(UserEntryPeer::VUSER_ID, $vuser->getId());
    $Critiria->add(UserEntryPeer::PARTNER_ID, $partnerId);
    $Critiria->add(UserEntryPeer::STATUS, UserEntryStatus::ACTIVE);
    $userEntryArray = UserEntryPeer::doSelect($Critiria);
    foreach ($userEntryArray as $userEntry) {
        VidiunLog::debug('set VuserId ['.$baseVuser->getId().'] instead of ['.$userEntry->getVuser()->getId().'] for userEntry ['.$userEntry->getId().']');
        $userEntry->setvuserId($baseVuser->getId());
        $userEntry->save();
    }
}

function changeVuserForVvote($vuser, $baseVuser, $partnerId){
    $Critiria = new Criteria();
    $Critiria->add(vvotePeer::VUSER_ID, $vuser->getId());
    $Critiria->add(vvotePeer::PARTNER_ID, $partnerId);
    $vvotesArray = vvotePeer::doSelect($Critiria);
    foreach ($vvotesArray as $vvote) {
        VidiunLog::debug('set VuserId ['.$baseVuser->getId().'] instead of ['.$vvote->getVuserId().'] for vvote ['.$vvote->getId().']');
        $vvote->setvuserId($baseVuser->getId());
        $vvote->save();
    }
}

function changeVuserForVuserVgroup($vuser, $baseVuser, $partnerId){
    vCurrentContext::$partner_id = $partnerId;
    $Critiria = new Criteria();
    $Critiria->add(VuserVgroupPeer::VUSER_ID, $vuser->getId());
    $Critiria->add(VuserVgroupPeer::PARTNER_ID, $partnerId);
    $vuserVgroups = VuserVgroupPeer::doSelect($Critiria);

    //if we have a row for the vuser_vgroup for the base puser we are deleting the row for the deleted vuser, else we are changing the vuser in the relevant row
    foreach ($vuserVgroups as $vuserVgroup) {
        $C = new Criteria();
        $C->add(VuserVgroupPeer::VUSER_ID, $baseVuser->getId());
        $C->add(VuserVgroupPeer::PARTNER_ID, $partnerId);
        $C->add(VuserVgroupPeer::VGROUP_ID, $vuserVgroup->getVgroupId());
        $sameVgroupForVusers = VuserVgroupPeer::doSelectOne($C);
        if (!$sameVgroupForVusers){
            VidiunLog::debug('couldn\'t find vgroup with id ['.$vuserVgroup->getVgroupId().'] need to associate to one');
            VidiunLog::debug('set VuserId ['.$baseVuser->getId().'] instead of ['.$vuserVgroup->getVuserId().'] for vuser_vgroup ['.$vuserVgroup->getId().']');
            $vuserVgroup->setVuserId($baseVuser->getId());
        }
        else{
            VidiunLog::debug('set status ['.VuserVgroupStatus::DELETED.'] instead of ['.$vuserVgroup->getStatus().'] for vuser_vgroup ['.$vuserVgroup->getId().']');
            $vuserVgroup->setStatus(VuserVgroupStatus::DELETED);
        }
        $vuserVgroup->save();
    }
}

function deleteVuser ($vuser){
    VidiunLog::debug('set VuserId ['.$vuser->getId().'] status from ['.$vuser->getStatus().'] to ['.VuserStatus::DELETED.']');
    $vuser->setStatus(VuserStatus::DELETED);
    $vuser->save();
}
