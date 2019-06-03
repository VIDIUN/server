<?php
/**
 * @package plugins.segmenter
 * @subpackage lib
 */
class VOperationEngineSegmenter  extends VSingleOutputOperationEngine
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

	public function operate(vOperator $operator = null, $inFilePath, $configFilePath = null)
	{
//$this->outFilePath = "v:".$this->outFilePath;
		VidiunLog::debug("creating directory:".$this->outFilePath);
		vFile::fullMkfileDir($this->outFilePath, 0777, true);
		$res = parent::operate($operator, $inFilePath, $configFilePath);
		rename("$this->outFilePath//playlist.m3u8", "$this->outFilePath//playlist.tmp");
		self::parsePlayList("$this->outFilePath//playlist.tmp","$this->outFilePath//playlist.m3u8");
//		rename("out_dummy.m3u8", "$this->outFilePath//out_dummy.m3u8");
//		VidiunLog::info("operator($operator), inFilePath($inFilePath), configFilePath($configFilePath)");

		return $res;
	}

	private function parsePlayList($fileIn, $fileOut)
	{
		$fdIn = fopen($fileIn, 'r');
		if($fdIn==false)
			return false;
		$fdOut = fopen($fileOut, 'w');
		if($fdOut==false)
			return false;
		$strIn=null;
		while ($strIn=fgets($fdIn)){
			if(strstr($strIn,"---")){
				$i=strrpos($strIn,"/");
				$strIn = substr($strIn,$i+1);
			}
			fputs($fdOut,$strIn);
			echo $strIn;
		}
		fclose($fdOut);
		fclose($fdIn);
		return true;
	}
}
