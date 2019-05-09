<?php
/**
 * @package plugins.vlc
 * @subpackage lib
 */
class VOperationEngineVlc  extends VSingleOutputOperationEngine
{

	public function __construct($cmd, $outFilePath)
	{
		parent::__construct($cmd,$outFilePath);
		VidiunLog::info(": cmd($cmd), outFilePath($outFilePath)");
	}

	protected function getCmdLine()
	{
		$exeCmd =  parent::getCmdLine();
		VidiunLog::info(print_r($this,true));
		return $exeCmd;
	}
}
