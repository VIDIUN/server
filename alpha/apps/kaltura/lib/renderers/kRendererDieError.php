<?php
require_once(dirname(__file__) . '/../request/infraRequestUtils.class.php');
require_once(dirname(__file__) . '/vRendererBase.php');
/*
 * @package server-infra
* @subpackage renderers
*/
class vRendererDieError implements vRendererBase
{
	/**
	 * 
	 * @var string
	 */
	private $code;
	
	/**
	 *
	 * @var string
	 */
	private $message;
	
	public function __construct($code, $message)
	{
		$this->code = $code;
		$this->message = $message;
	}
	
	public function validate()
	{
		return true;
	}
	
	public function output()
	{
		header('X-Vidiun:error- ' . $this->code);
		header("X-Vidiun-App: exiting on error {$this->code} - {$this->message}");
		
		if (class_exists('VidiunLog') && isset($GLOBALS["start"])) 
			VidiunLog::debug("Dispatch took - " . (microtime(true) - $GLOBALS["start"]) . " seconds, memory: ".memory_get_peak_usage(true));
	}
}
