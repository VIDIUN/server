<?php
/**
 * @package deployment
 * @subpackage jupiter.roles_and_permissions
 */

$script = realpath(dirname(__FILE__) . '/../../../../') . '/alpha/scripts/utils/permissions/addPermissionsAndItems.php';
//$script = realpath(dirname(__FILE__) . '/../../../../') . '/alpha/scripts/utils/permissions/removePermissionsAndItems.php';

$config = realpath(dirname(__FILE__)) . '/../../../permissions/service.cuepoint.cuepoint.ini';
passthru("php $script $config");

$config = realpath(dirname(__FILE__)) . '/../../../permissions/object.VidiunCuePoint.ini';
passthru("php $script $config");

$config = realpath(dirname(__FILE__)) . '/../../../permissions/object.VidiunAdCuePoint.ini';
passthru("php $script $config");

$config = realpath(dirname(__FILE__)) . '/../../../permissions/object.VidiunAnswerCuePoint.ini';
passthru("php $script $config");

$config = realpath(dirname(__FILE__)) . '/../../../permissions/object.VidiunThumbCuePoint.ini';
passthru("php $script $config");

$config = realpath(dirname(__FILE__)) . '/../../../permissions/object.VidiunAnnotationCuePoint.ini';
passthru("php $script $config");

$config = realpath(dirname(__FILE__)) . '/../../../permissions/object.VidiunCodeCuePoint.ini';
passthru("php $script $config");

$config = realpath(dirname(__FILE__)) . '/../../../permissions/object.VidiunEventCuePoint.ini';
passthru("php $script $config");

$config = realpath(dirname(__FILE__)) . '/../../../permissions/object.VidiunQuestionCuePoint.ini';
passthru("php $script $config");