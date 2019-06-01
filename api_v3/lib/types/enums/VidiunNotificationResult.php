<?php 
/**
 * @package api
 * @subpackage enum
 */
class VidiunNotificationResult  extends VidiunEnum 
{
	const OK = 0; 
	const ERROR_RETRY = -1;
	const ERROR_NO_RETRY = -2;
	
}