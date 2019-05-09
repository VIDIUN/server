<?php 
/**
 * @package plugins.businessProcessNotification
 */
abstract class vBusinessProcessProvider
{
	/**
	 * @param VidiunBusinessProcessServer $server
	 * @return vBusinessProcessProvider
	 */
	public static function get($server)
	{
		/* @var $server VidiunBusinessProcessServer */
		return VidiunPluginManager::loadObject('vBusinessProcessProvider', $server->type, array($server));
	}
	
	/**
	 * @param boolean $enable 
	 */
	abstract public function enableDebug($enable);
	
	/**
	 * @return array<string, string> key is id, value is process name 
	 */
	abstract public function listBusinessProcesses();
	
	/**
	 * @param string $processId
	 * @param array<string, string> $variables key is variable name
	 * @return string caseId
	 */
	abstract public function startBusinessProcess($processId, array $variables);
	
	/**
	 * @return string caseId
	 */
	abstract public function abortCase($caseId);
	
	/**
	 * @param string $caseId
	 * @param string $eventId
	 * @param string $message
	 * @param array $variables
	 */
	abstract public function signalCase($caseId, $eventId, $message, array $variables = array());
	
	/**
	 * @param string $caseId
	 * @param string $filename
	 */
	abstract public function getCaseDiagram($caseId, $filename);
	
	/**
	 * @param string $caseId
	 * @return vBusinessProcessCase
	 */
	abstract public function getCase($caseId);
}