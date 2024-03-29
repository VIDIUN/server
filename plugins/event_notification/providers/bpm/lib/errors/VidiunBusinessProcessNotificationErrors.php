<?php

/**
 * @package plugins.businessProcessNotification
 * @subpackage api.errors
 */
class VidiunBusinessProcessNotificationErrors extends VidiunErrors
{
	const BUSINESS_PROCESS_SERVER_NOT_FOUND = "BUSINESS_PROCESS_SERVER_NOT_FOUND;ID;Business-Process server id [@ID@] not found";

    const BUSINESS_PROCESS_SERVER_DUPLICATE_SYSTEM_NAME = "BUSINESS_PROCESS_SERVER_DUPLICATE_SYSTEM_NAME;NAME;Business-Process server with system name [@NAME@] already exists.";
    
    const BUSINESS_PROCESS_CASE_NOT_FOUND = "BUSINESS_PROCESS_CASE_NOT_FOUND;;No business-process case found.";
}