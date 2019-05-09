<?php
/**
 * Implements the IVidiuarLogger interface used by the VidiunClient for logging purposes and proxies the message to the VidiunLog
 *  
 * @package UI-infra
 * @subpackage Client
 */
class Infra_ClientLoggingProxy implements Vidiun_Client_ILogger
{
	public function log($msg)
	{
		VidiunLog::debug($msg);
	}
}