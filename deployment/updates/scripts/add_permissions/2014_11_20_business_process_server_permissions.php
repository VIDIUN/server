<?php
/**
 * @package deployment
 * @subpackage gemini.roles_and_permissions
 *
 * Adds userId parameter permissions
 *
 * No need to re-run after server code deploy
 */

$script = realpath(dirname(__FILE__) . '/../../../../') . '/alpha/scripts/utils/permissions/addPermissionsAndItems.php';

$config = realpath(dirname(__FILE__) . '/../../../') . '/permissions/partner.-2.ini';
passthru("php $script $config");

$config = realpath(dirname(__FILE__) . '/../../../') . '/permissions/service.businessProcessNotification.businessProcessServer.ini';
passthru("php $script $config");

$config = realpath(dirname(__FILE__) . '/../../../') . '/permissions/service.businessProcessNotification.businessProcessCase.ini';
passthru("php $script $config");

$config = realpath(dirname(__FILE__) . '/../../../') . '/permissions/object.VidiunBusinessProcessNotificationTemplate.ini';
passthru("php $script $config");
