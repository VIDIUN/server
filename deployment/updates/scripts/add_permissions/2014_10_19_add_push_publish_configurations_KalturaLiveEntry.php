<?php
/**
 * @package deployment
 * @subpackage falcon.roles_and_permissions
 *
 * Add permissions to playback base
 */


$script = realpath(dirname(__FILE__) . '/../../../../') . '/alpha/scripts/utils/permissions/addPermissionsAndItems.php';
$config = realpath(dirname(__FILE__)) . '/../../../permissions/object.VidiunLiveEntry.ini';
passthru("php $script $config");
