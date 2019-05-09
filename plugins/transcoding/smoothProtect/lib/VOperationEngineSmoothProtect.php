<?php
/**
 * @package plugins.smoothProtect
 * @subpackage lib
 */
class VOperationEngineSmoothProtect  extends VSingleOutputOperationEngine
{

	public function __construct($cmd, $outFilePath)
	{
		parent::__construct($cmd,$outFilePath);
		VidiunLog::info(": cmd($cmd), outFilePath($outFilePath)");
	}

	/* ---------------------------
	 * getCmdLine
	 */
	protected function getCmdLine()
	{
		$exeCmd =  parent::getCmdLine();
// SmoothProtect.exe -input AAA.ism -output OUT_FOLDER -keyId KEY_ID -keySeed KEY_SEED -laUrl http://playready.directtaps.net/pr/svc/rightsmanager.asmx?
//		SmoothProtectPlugin::PARAMS_STUB
		$pathInfo = pathinfo($this->inFilePath);
		$ismFilePath = $pathInfo['dirname']."/".$pathInfo['filename'].".ism";
		$outFilePath = dirname($this->outFilePath);
			// SmoothPortect is Windows tool, it requires its path params to have Win's folder notation
		$outFilePath = str_replace("/", "\\", $outFilePath);
		$ismFilePath = str_replace("/", "\\", $ismFilePath);
		$paramsStr = " -input $ismFilePath -output $outFilePath ".$this->getLicenseParamsStr();
		$exeCmd = str_replace(SmoothProtectPlugin::PARAMS_STUB, $paramsStr, $exeCmd);
		VidiunLog::info($exeCmd);
		return $exeCmd;
	}

	/* ---------------------------
	 * operate
	 */
	public function operate(vOperator $operator = null, $inFilePath, $configFilePath = null)
	{
		$res = parent::operate($operator, $inFilePath, $configFilePath);
		if($res==false) {
			return false;
		}

		$inPathInfo=pathinfo($inFilePath);
		$inFileName = $inPathInfo['filename'];

		$outPathInfo=pathinfo($this->outFilePath);
		$outFileName = $outPathInfo['filename'];
		$outBaseName = $outPathInfo['basename'];
		$outFolderName = $outPathInfo['dirname'];
		
		$auxOutName = "$outFolderName//$inFileName";
		
		/*
		 * Update ISM manifest file with correct output file names
		 */
		$ismStr = file_get_contents($auxOutName.".ism");
		VidiunLog::info("Before file name update:\n$ismStr");
		$ismXml = new SimpleXMLElement($ismStr);
		$ismXml->head->meta['content'] = $outFileName.".ismc";
		if(isset($ismXml->body->switch->video)) {
			$extStr = pathinfo((string)$ismXml->body->switch->video['src'], PATHINFO_EXTENSION); 
			$ismXml->body->switch->video['src'] = $outBaseName;
		}
		if(isset($ismXml->body->switch->audio)) {
			$extStr = pathinfo((string)$ismXml->body->switch->audio['src'], PATHINFO_EXTENSION); 
			$ismXml->body->switch->audio['src'] = $outBaseName;
		}
		$ismStr = $ismXml->asXML();
		VidiunLog::info("After file name update:\n$ismStr");
		file_put_contents($auxOutName.".ism", $ismStr);
		
		/*
		 * Update the ISM/ISMC/ISMV file names to correct output file names
		 */
		rename($auxOutName.".ism",  "$outFolderName//$outFileName.ism");
		rename($auxOutName.".ismc", "$outFolderName//$outFileName.ismc");
		if(isset($extStr)){
			rename($auxOutName.".$extStr", "$outFolderName//$outBaseName");
		}
		else{
			rename($auxOutName, "$outFolderName//$outBaseName");
		}

		/*
		 * Notify batch job flow to bind the ISM/ISMC files to the asset
		 */
		$fsDescArr = array();
		$fsDesc = new VidiunDestFileSyncDescriptor();
		$fsDesc->fileSyncLocalPath = "$outFolderName//$outFileName.ism";
		$fsDesc->fileSyncObjectSubType = 3; //".ism";
		$fsDescArr[] = $fsDesc;
		$fsDesc = new VidiunDestFileSyncDescriptor();
		$fsDesc->fileSyncLocalPath = "$outFolderName//$outFileName.ismc";
		$fsDesc->fileSyncObjectSubType = 4; //".ismc";
		$fsDescArr[] = $fsDesc;
		$this->data->extraDestFileSyncs  = $fsDescArr;
		return $res;
	}

	/* ---------------------------
	 * getLicenseParamsStr
	*/
	private function getLicenseParamsStr()
	{
		// impersonite
		VBatchBase::impersonate($this->job->partnerId);
		
		$drmPlugin = VidiunDrmClientPlugin::get(VBatchBase::$vClient);
		if(!isset($drmPlugin)) {
			VidiunLog::err("FAILED to get drmPlugin");
			return false;
		}
		$profile=$drmPlugin->drmProfile->getByProvider(VidiunDrmProviderType::PLAY_READY);
		if(!isset($profile)) {
			VidiunLog::err("FAILED to get profile");
			return false;
		}
		$playReadyPlugin = VidiunPlayReadyClientPlugin::get(VBatchBase::$vClient);
		if(!isset($playReadyPlugin)) {
			VidiunLog::err("FAILED to get playReadyPlugin");
			return false;
		}
		$playReadyData = $playReadyPlugin->playReadyDrm->getEntryContentKey($this->job->entryId, true);
		if(!isset($playReadyData)) {
			VidiunLog::err("FAILED to get playReadyData");
			return false;
		}

		// un-impersonite
		VBatchBase::unimpersonate();
			
		$paramsStr = " -keyId $playReadyData->keyId -contentKey $playReadyData->contentKey -laUrl $profile->licenseServerUrl";
		VidiunLog::info($paramsStr);
		return $paramsStr;

/*
		$keyId = "b6e16839-eebd-4ff6-ab76-8d482d8d2b6a";
		$keySeed = "XVBovsmzhP9gRIZxWfFta3VVRPzVEWmJsazEJ46I";
		$laSrv = "http://playready.directtaps.net/pr/svc/rightsmanager.asmx?";
		$paramsStr = " -keyId $keyId -keySeed $keySeed -laUrl $laSrv";
		return $paramsStr;
*/
	}
}
