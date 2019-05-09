<?php
/**
 * 
 * @package Scheduler
 * @subpackage Conversion
 *
 */
class VOperationEngineInletArmada  extends VSingleOutputOperationEngine
{
/*
	protected $url=null;
	protected $login=null;
	protected $passw=null;
	protected $prio=5;
*/
	public function __construct($cmd, $outFilePath)
	{
		parent::__construct($cmd,$outFilePath);
//		$this->prio=5;
		VidiunLog::info(": cmd($cmd), outFilePath($outFilePath)");
	}

	/*************************************
	 * 
	 */
	protected function getCmdLine()
	{
		$exeCmd =  parent::getCmdLine();
		VidiunLog::info(print_r($this,true));
		return $exeCmd;
	}

	/*************************************
	 * 
	 */
	public function operate(vOperator $operator = null, $inFilePath, $configFilePath = null)
	{
		VidiunLog::debug("operator==>".print_r($operator,1));

$encodingTemplateId = null;
$encodingTemplateName = null;
$cloneAndUpadate=false;
$srcPrefixWindows = null;
$srcPrefixLinux = null;
$trgPrefixWindows = null;

			// ---------------------------------
			// Evaluate and set various Inlet Armada session params
		if(VBatchBase::$taskConfig->params->InletStorageRootWindows) $srcPrefixWindows = VBatchBase::$taskConfig->params->InletStorageRootWindows;
		if(VBatchBase::$taskConfig->params->InletStorageRootLinux)   $srcPrefixLinux = VBatchBase::$taskConfig->params->InletStorageRootLinux;
		if(VBatchBase::$taskConfig->params->InletTmpStorageWindows)  $trgPrefixWindows = VBatchBase::$taskConfig->params->InletTmpStorageWindows;

		$url = VBatchBase::$taskConfig->params->InletArmadaUrl;
		$login = VBatchBase::$taskConfig->params->InletArmadaLogin;
		$passw = VBatchBase::$taskConfig->params->InletArmadaPassword;
		if(VBatchBase::$taskConfig->params->InletArmadaPriority)
			$priority = VBatchBase::$taskConfig->params->InletArmadaPriority;
		else
			$priority = 5;
			// ----------------------------------
			
		$inlet = new InletAPIWrap($url);
		VidiunLog::debug(print_r($inlet,1));
		$rvObj=new XmlRpcData;
		
		$rv=$inlet->userLogon($login, $passw, $rvObj);
		if(!$rv) {
			throw new VOperationEngineException("Inlet failure: login, rv(".(print_r($rvObj,true)).")");
		}
		VidiunLog::debug("userLogon - ".print_r($rvObj,1));
		
		$paramsMap = VDLUtils::parseParamStr2Map($operator->extra);
		foreach($paramsMap as $key=>$param){
			switch($key){
				case 'encodingTemplate':
				case 'encodingTemplateId':
					$encodingTemplateId=$param;
					break;
				case 'encodingTemplateName':
					$encodingTemplateId = $this->lookForJobTemplateId($inlet, $param);
					$encodingTemplateName=$param;
					break;
				case 'priority':
					$priority=$param;
					break;
				case 'cloneAndUpadate':
					$cloneAndUpadate=$param;
					break;
				default:
					break;
			}
		}
		
			// Adjust linux file path to Inlet Armada Windows path
		if(isset($srcPrefixWindows) && isset($srcPrefixLinux)) {
			$srcPrefixLinux = $this->addLastSlashInFolderPath($srcPrefixLinux, "/");
			$srcPrefixWindows = $this->addLastSlashInFolderPath($srcPrefixWindows, "\\");
			$srcFileWindows  = str_replace($srcPrefixLinux, $srcPrefixWindows, $inFilePath);
		}
		else
			$srcFileWindows  = $inFilePath;
			
		if(isset($trgPrefixWindows)){
			$trgPrefixLinux = $this->addLastSlashInFolderPath(VBatchBase::$taskConfig->params->localTempPath, "/");
			$trgPrefixWindows = $this->addLastSlashInFolderPath($trgPrefixWindows, "\\");
			$outFileWindows = str_replace($trgPrefixLinux, $trgPrefixWindows, $this->outFilePath);
		}
		else
			$outFileWindows = $this->outFilePath;
			
		$rv=$inlet->jobAdd(			
				$encodingTemplateId,		// job template id
				$srcFileWindows,		// String job_source_file, 
				$outFileWindows,		// String job_destination_file, 
				$priority,				// Int priority, 
				$srcFileWindows,			// String description, 
				array(),"",
				$rvObj);						
		if(!$rv) {
			throw new VOperationEngineException("Inlet failure: add job, rv(".print_r($rvObj,1).")");
		}
		VidiunLog::debug("jobAdd - encodingTemplate($encodingTemplateId), inFile($srcFileWindows), outFile($outFileWindows),rv-".print_r($rvObj,1));
		
		$jobId=$rvObj->job_id;
		$attemptCnt=0;
		while ($jobId) {
			sleep(60);
			$rv=$inlet->jobList(array($jobId),$rvObj);
			if(!$rv) {
				throw new VOperationEngineException("Inlet failure: job list, rv(".print_r($rvObj,1).")");
			}
			switch($rvObj->job_list[0]->job_state){
			case InletArmadaJobStatus::CompletedSuccess:
				$jobId=null;
				break;
			case InletArmadaJobStatus::CompletedUnknown:
			case InletArmadaJobStatus::CompletedFailure:
				throw new VOperationEngineException("Inlet failure: job, rv(".print_r($rvObj,1).")");
				break;
			}
			if($attemptCnt%10==0) {
				VidiunLog::debug("waiting for job completion - ".print_r($rvObj,1));
			}
			$attemptCnt++;
		}
//VidiunLog::debug("XXX taskConfig=>".print_r(VBatchBase::$taskConfig,1));
		VidiunLog::debug("Job completed successfully - ".print_r($rvObj,1));

		if($trgPrefixWindows) {
			$trgPrefixLinux = $this->addLastSlashInFolderPath(VBatchBase::$taskConfig->params->sharedTempPath, "/");
			$outFileLinux = str_replace($trgPrefixWindows, $trgPrefixLinux, $rvObj->job_list[0]->job_output_file);
//VidiunLog::debug("XXX str_replace($trgPrefixWindows, ".$trgPrefixLinux.", ".$rvObj->job_list[0]->job_output_file.")==>$outFileLinux");
		}
		else
			$outFileLinux = $rvObj->job_list[0]->job_output_file;
			
		if($outFileLinux!=$this->outFilePath) {
			VidiunLog::debug("copy($outFileLinux, ".$this->outFilePath.")");
			vFile::moveFile($outFileLinux, $this->outFilePath, true);
			//copy($outFileLinux, $this->outFilePath);
		}
		
		return true;
	}

	/*************************************
	 * 
	 */
	public function configure(VidiunConvartableJobData $data, VidiunBatchJob $job)
	{
		parent::configure($data, $job);
		
		$errStr=null;
		if(!VBatchBase::$taskConfig->params->InletArmadaUrl)
			$errStr="InletArmadaUrl";
		if(!VBatchBase::$taskConfig->params->InletArmadaLogin){
			if($errStr) 
				$errStr.=",InletArmadaLogin";
			else
				$errStr="InletArmadaLogin";
		}
		if(!VBatchBase::$taskConfig->params->InletArmadaPassword){
			if($errStr) 
				$errStr.=",InletArmadaPassword";
			else
				$errStr="InletArmadaPassword";
		}
		
		if($errStr)
			throw new VOperationEngineException("Inlet failure: missing credentials - $errStr");//, url(".$taskConfig->params->InletArmadaUrl."), login(."$taskConfig->params->InletArmadaLogin."),passw(".$taskConfig->params->InletArmadaPassword.")");
/*		
		$this->url =	$taskConfig->params->InletArmadaUrl;
		$this->login =	$taskConfig->params->InletArmadaLogin;
		$this->passw =	$taskConfig->params->InletArmadaPassword;
		if($taskConfig->params->InletArmadaPriority)
			$this->prio =	$taskConfig->params->InletArmadaPriority;
		else
			$this->prio = 5;
*/
	}

	/*************************************
	 * 
	 */
	private function addLastSlashInFolderPath($pathStr, $slashCh)
	{
		if($pathStr[strlen($pathStr)-1]!=$slashCh)
			return $pathStr.$slashCh;
		else
			return $pathStr;
	}
	
	/*************************************
	 * 
	 */
	private function lookForJobTemplateId($inlet, $name)
	{
	$rvObj=new XmlRpcData;
		$rv=$inlet->templateGroupList($rvObj);
		if(!$rv) {
			throw new VOperationEngineException("Inlet failure: templateGroupList, rv(".print_r($rvObj,1).")");
		}
		$templateDescObj=$this->templateGroupListToJobTemplate($rvObj->template_group_list, $name);
		return $templateDescObj->template_id;
	}
	
	/*************************************
	 * 
	 */
	private function templateGroupListToJobTemplate($groupList, $val, $fieldName="template_description")
	{
		foreach ($groupList as $grp) {
			foreach ($grp->templates as $tpl) {
				if($tpl->$fieldName==$val) {
					return $tpl;
				}
			}
		}
		return null;
	}
}
