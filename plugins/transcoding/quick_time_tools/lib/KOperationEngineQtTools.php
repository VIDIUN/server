<?php
/**
 * @package plugins.quickTimeTools
 * @subpackage lib
 */
class VOperationEngineQtTools  extends VSingleOutputOperationEngine
{
	protected $tmpFolder;
	
	public function configure(VidiunConvartableJobData $data, VidiunBatchJob $job)
	{
		parent::configure($data, $job);
		$this->tmpFolder = VBatchBase::$taskConfig->params->localTempPath;
	}
	
	public function operate(vOperator $operator = null, $inFilePath, $configFilePath = null)
	{
		$qtInFilePath = "$this->tmpFolder/$inFilePath.stb";

		if(symlink($inFilePath, $qtInFilePath))
			$inFilePath = $qtInFilePath;
		
		return parent::operate($operator, $inFilePath, $configFilePath);
	}
}
