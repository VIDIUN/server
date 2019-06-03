<?php

 /*****************************
 * Includes & Globals
 */
//ini_set("memory_limit","2048M");

	/********************
	 * Base Chunked Encoding Session Manager module
	 */
	abstract class VBaseChunkedEncodeSessionManager
	{
		protected $name = null;	
		protected $chunker = null;

		protected $maxFailures = 5;		// Max allowed job failures (if more, get out w/out retry)
		protected $maxRetries = 10;		// Max retries per failed job
		protected $maxExecutionTime = 3600;	// In seconds 
		
		protected $videoCmdLines = array();
		protected $audioCmdLines = array();
		
		protected $createTime = null;	// Secs
		protected $finishTime = null;

		protected $chunkExecutionDataArr = array();

		protected $returnStatus = null;	// VChunkedEncodeReturnStatus
		protected $returnMessages = array();

		protected $concurrencyHistogram = array();
		protected $concurrencyAccum = 0;

		/********************
		 *
		 */
		public function __construct(VChunkedEncodeSetup $setup, $name=null)
		{
			$this->chunker = new VChunkedEncode($setup);
			VidiunLog::log(date("Y-m-d H:i:s"));
			VidiunLog::log("sessionData:".print_r($this,1));
			
			if(strlen($name)==0)
				$this->name = null;
			else
				$this->name = $name;
			
			$this->createTime = time();
		}

		/********************
		 *
		 */
		public function Generate()
		{
			if($this->GenerateContent()!=true){
				return false;
			}
			
			if($this->Analyze()>0 && $this->FixChunks()!==true){
				return false;
			}

			if($this->Merge()!=true){
				return false;
			}
			
			if(isset($this->chunker->setup->cleanUp) && $this->chunker->setup->cleanUp){
				$this->CleanUp();
			}
			
			$this->returnStatus = VChunkedEncodeReturnStatus::OK;
			if(file_exists($this->chunker->getSessionName())) {
				copy($this->chunker->getSessionName(), $this->chunker->params->output);
			}			
			return true;
		}
		
		/* ---------------------------
		 * Initialize
		 */
		public function Initialize()
		{
			$chunker = $this->chunker;
			$rv = $chunker->Initialize($msgStr);
			if($rv!==true){
				$this->returnStatus = VChunkedEncodeReturnStatus::InitializeError;
				$this->returnMessages[] = $msgStr;
				return $rv;
			}
			if(!isset($this->name))
				$this->name = basename($chunker->params->output);

			$videoCmdLines = array();
			for($chunkIdx=0;$chunkIdx<$chunker->GetMaxChunks();$chunkIdx++) {
				$chunkData = $chunker->GetChunk($chunkIdx);
				$start = $chunkData->start;
				$cmdLine = $chunker->BuildVideoCommandLine($start, $chunkIdx);
				$logFilename = $chunker->getChunkName($chunkIdx,".log");
				$cmdLine = "time $cmdLine > $logFilename 2>&1";
				$outFilename = $chunker->getChunkName($chunkIdx);
				$videoCmdLines[$chunkIdx] = array($cmdLine, $outFilename);
				VidiunLog::log($cmdLine);
			}
			$this->videoCmdLines = $videoCmdLines;
			
			$cmdLine = $chunker->BuildAudioCommandLine();
			if(isset($cmdLine)){
				$logFilename = $chunker->getSessionName("audio").".log";
				$cmdLine = "time $cmdLine > $logFilename 2>&1";
				$this->audioCmdLines = array($cmdLine);
			}
			$this->SerializeSession();
			return true;
		}

		/********************
		 * Analyze 
		 */
		public function Analyze()
		{
			return $this->chunker->CheckChunksContinuity();
		}
		
		/* ---------------------------
		 * 
		 */
		public function FixChunks()
		{
			$chunker = $this->chunker;
			$processArr = array();
			$maxChunks = $chunker->GetMaxChunks();
			for($idx=0; $idx<$maxChunks; $idx++) {
				$chunkData = $chunker->GetChunk($idx);
				if(!isset($chunkData->toFix) || $chunkData->toFix==0)
					continue;
				/*
				 * Check for too short generated chunks. If found - leave with error,
				 * 10 frame threshold allowed.
				 */
				if($idx<$maxChunks-1
				&& $chunkData->stat->start+$chunkData->gap > $chunkData->stat->finish+10*$chunker->params->frameDuration){
					$msgStr="Chunk id ($chunkData->index): chunk duration too short - ".($chunkData->stat->finish-$chunkData->stat->start.", should be $chunkData->gap");
					VidiunLog::log($msgStr);
					$this->returnMessages[] = $msgStr;
					$this->returnStatus = VChunkedEncodeReturnStatus::AnalyzeError;
					return false;
				}

				$toFixChunkIdx = $chunkData->index;
				
				$chunkFixName = $chunker->getChunkName($toFixChunkIdx, "fix");
				$cmdLine = $chunker->BuildFixVideoCommandLine($toFixChunkIdx)." > $chunkFixName.log 2>&1";
				$process = $this->executeCmdline($cmdLine, "$chunkFixName.log");
				if($process==false){
					VidiunLog::log($msgStr="Chunk ($chunkFixName) fix FAILED !");
					$this->returnMessages[] = $msgStr;
					$this->returnStatus = VChunkedEncodeReturnStatus::AnalyzeError;
					return false;
				}
				$processArr[$toFixChunkIdx] = $process;
			}
			VidiunLog::log("waiting ...");
			foreach($processArr as $idx=>$process) {
				VProcessExecutionData::waitForCompletion($process);
				$chunkFixName = $chunker->getChunkName($idx, "fix");
				$execData = new VProcessExecutionData($process, $chunkFixName.".log");
				if($execData->exitCode!=0) {
					VidiunLog::log($msgStr="Chunk ($idx) fix FAILED, exitCode($execData->exitCode)!");
					$this->returnMessages[] = $msgStr;
					$this->returnStatus = VChunkedEncodeReturnStatus::AnalyzeError;
					return false;
				}
			}
			
			return true;
		}
		
		/********************
		 *
		 */
		public function Merge()
		{
			$concatFilenameLog = $this->chunker->getSessionName("concat");

			$mergeCmd = $this->chunker->BuildMergeCommandLine();
			VidiunLog::log("mergeCmd:$mergeCmd");
			$maxAttempts = 3;
			for($attempt=0; $attempt<$maxAttempts; $attempt++) {

				$process = $this->executeCmdline($mergeCmd, $concatFilenameLog);
				if($process==false) {
					VidiunLog::log("FAILED to merge (attempt:$attempt)!");
					$logTail = self::getLogTail($concatFilenameLog);
					if(isset($logTail))
						VidiunLog::log("Log dump:\n".$logTail);
					sleep(5);
					continue;
				}
				VidiunLog::log("waiting ...");
				VProcessExecutionData::waitForCompletion($process);
				$execData = new VProcessExecutionData($process, $concatFilenameLog);
				if($execData->exitCode!=0) {
					VidiunLog::log("FAILED to merge (attempt:$attempt, exitCode:$execData->exitCode)!");
					$logTail = self::getLogTail($concatFilenameLog);
					if(isset($logTail))
						VidiunLog::log("Log dump:\n".$logTail);
					sleep(5);
					continue;
				}
				break;
			}
			if($attempt==$maxAttempts){
				VidiunLog::log($msgStr="FAILED to merge, leaving!");
				$this->returnMessages[] = $msgStr;
				$this->returnStatus = VChunkedEncodeReturnStatus::MergeAttemptsError;
				return false;
			}

			if($this->chunker->ValidateMergedFile($msgStr)!=true){
				VidiunLog::log($msgStr);
				$this->returnMessages[] = $msgStr;
				$this->returnStatus = VChunkedEncodeReturnStatus::MergeThreshError;
				return false;
			}
			return true;
		}
		
		/********************
		 *
		 */
		public function Report()
		{
			$this->finishTime = time();
			$sessionData = $this;
			$chunker = $this->chunker;
			VidiunLog::log("sessionData:".print_r($sessionData,1));

			$msgStr = "Merged:";
			$durStr = null;
			$fileDtMrg = $chunker->mergedFileDt;
			if(isset($fileDtMrg)){
				VidiunLog::log("merged:".print_r($fileDtMrg,1));
				$msgStr.= "file dur(v:".round($fileDtMrg->videoDuration/1000,4).",a:".round($fileDtMrg->audioDuration/1000,4).")";
			}
			if(isset($sessionData->refFileDt)) {
				$fileDtRef = $sessionData->refFileDt;
				VidiunLog::log("reference:".print_r($fileDtRef,1));
			}
			$fileDtSrc = $chunker->sourceFileDt;
			if(isset($fileDtSrc)){
				VidiunLog::log("source:".print_r($fileDtSrc,1));
			}
			
			{
				VidiunLog::log("CSV,idx,startedAt,user,system,elapsed,cpu");
				$userAcc = $systemAcc = $elapsedAcc = $cpuAcc = 0;
				foreach($this->chunkExecutionDataArr as $idx=>$execData){
					$userAcc+= $execData->user;
					$systemAcc+= $execData->system;
					$elapsedAcc+= $execData->elapsed;
					$cpuAcc+= $execData->cpu;
					
					VidiunLog::log("CSV,$idx,$execData->startedAt,$execData->user,$execData->system,$execData->elapsed,$execData->cpu");
				}
				$cnt = $chunker->GetMaxChunks();
				if($cnt>0) {
					$userAvg 	= round($userAcc/$cnt,3);
					$systemAvg 	= round($systemAcc/$cnt,3);
					$elapsedAvg = round($elapsedAcc/$cnt,3);
					$cpuAvg 	= round($cpuAcc/$cnt,3);
				}
				else
					$userAvg = $systemAvg = $elapsedAvg = $cpuAvg = 0;

			}
			
//			VidiunLog::log("LogFile: ".$chunker->getSessionName("log"));

			if(isset($this->concurrencyHistogram) && count($this->concurrencyHistogram)>0){
				ksort($this->concurrencyHistogram);
				$ttlStr = "Concurrency";
				$tmStr = "Concurrency";
				$concurSum = 0;
				$tmSum = 0;
				foreach($this->concurrencyHistogram as $concur=>$tm){
					$ttlStr.=",$concur";
					$tmStr.= ",$tm";
					$concurSum+= ($concur*$tm);
					$tmSum+= $tm;
				}
				VidiunLog::log($ttlStr);
				VidiunLog::log($tmStr);
				$concurrencyLevel = (round(($concurSum/$tmSum),2));
			}

			VidiunLog::log("***********************************************************");
			VidiunLog::log("* Session Summary (".date("Y-m-d H:i:s").")");
			VidiunLog::log("* ");
			VidiunLog::log("ExecutionStats:chunks($cnt),accum(elapsed:$elapsedAcc,user:$userAcc,system:$systemAcc),average(elapsed:$elapsedAvg,user:$userAvg,system:$systemAvg,cpu:$cpuAvg)");
			if($sessionData->returnStatus==VChunkedEncodeReturnStatus::AnalyzeError){
				$msgStr.= ",analyze:BAD";
			}
			if($sessionData->returnStatus==VChunkedEncodeReturnStatus::OK){
				$msgStr.= ",analyze:OK";
				if(isset($fileDtMrg)) {
					$frameRateMode = stristr($fileDtMrg->rawData,"Frame rate mode                          : ");
					$frameRateMode = strtolower(substr($frameRateMode, strlen("Frame rate mode                          : ")));
					$frameRateMode = strncmp($frameRateMode,"constant",8);
					if($frameRateMode==0) {
						$msgStr.= ",frameRateMode(constant)";
					}
					else
						$msgStr.= ",frameRateMode(variable)";
				}
			}
			if(isset($chunker->sourceFileDt)
			&& (!isset($chunker->setup->duration) || $chunker->setup->duration<=0 || abs($chunker->setup->duration-round($chunker->sourceFileDt->containerDuration/1000,4))<0.1)) {
				if(isset($fileDtMrg)){
					$deltaStr = null;
					if(isset($fileDtRef)){
						$vidDelta = round(($fileDtMrg->videoDuration - $fileDtRef->videoDuration)/1000,4);
						$audDelta = round(($fileDtMrg->audioDuration - $fileDtRef->audioDuration)/1000,4);
						$deltaStr = "MergedToRef:(v:$vidDelta,a:$audDelta)";
						$videoOk = (abs($vidDelta)<$chunker->maxInaccuracyValue);
						$deltaStr.=$videoOk?",video:OK":",video:BAD";
						$audioOk = (abs($audDelta)<$chunker->maxInaccuracyValue);
						$deltaStr.=$audioOk?",audio:OK":",audio:BAD";
						$deltaStr.=($audioOk && $videoOk)?",delta:OK":",delta:BAD";
						$deltaStr.= ",dur(v:".round($fileDtRef->videoDuration/1000,4).",a:".round($fileDtRef->audioDuration/1000,4).")";
						VidiunLog::log("$deltaStr");
					}

					$deltaStr = null;
					if(isset($fileDtSrc)){
						$dur=$fileDtSrc->videoDuration = ($fileDtSrc->videoDuration>0)? $fileDtSrc->videoDuration: $dur=$fileDtSrc->containerDuration;
						$vidDelta = ($fileDtMrg->videoDuration - $dur)/1000;//round(($fileDtMrg->videoDuration - $dur)/1000,6);
						$dur=$fileDtSrc->audioDuration = ($fileDtSrc->audioDuration>0)? $fileDtSrc->audioDuration: $dur=$fileDtSrc->containerDuration;
						$audDelta = ($fileDtMrg->audioDuration - $dur)/1000;//round(($fileDtMrg->audioDuration - $dur)/1000,6);
						$deltaStr = "MergedToSrc:(v:$vidDelta,a:$audDelta)";
						$videoOk = (abs($vidDelta)<$chunker->maxInaccuracyValue);
						$deltaStr.=$videoOk?",video:OK":",video:BAD";
						$audioOk = (abs($audDelta)<$chunker->maxInaccuracyValue);
						$deltaStr.=$audioOk?",audio:OK":",audio:BAD";
						$deltaStr.=($audioOk && $videoOk)?",delta:OK":",delta:BAD";
						$deltaStr.= ",dur(v:".round($fileDtSrc->videoDuration/1000,6).",a:".round($fileDtSrc->audioDuration/1000,6).")";
						VidiunLog::log("$deltaStr");
					}
				}
			}
			
			VidiunLog::log("$msgStr");
			VidiunLog::log("OutputFile: ".realpath($chunker->getSessionName()));
			
			$errStr = null;
			$lasted = $this->finishTime - $this->createTime;
				
			if($sessionData->returnStatus==VChunkedEncodeReturnStatus::OK) {
				$msgStr = "RESULT:Success"."  Lasted:".gmdate('H:i:s',$lasted)."/".($lasted)."s";
				if(isset($concurrencyLevel)) {
					$val = end($this->concurrencyHistogram);
					$idle = round($this->concurrencyHistogram[0]/1000,2);
					$msgStr.= ", concurrency:$concurrencyLevel(max:".key($this->concurrencyHistogram).",".round($val/1000,2)."s,idle:$idle"."s)";
				}
			}
			else {
				$msgStr = $sessionData->getErrorMessage();
				$msgStr = "RESULT:$msgStr";
			}
			VidiunLog::log($msgStr);
			VidiunLog::log("***********************************************************");

			$this->SerializeSession();
		}
		
		/********************
		 *
		 */
		public function CleanUp()
		{
			$setup = $this->chunker->setup;
			for($idx=0;$idx<$this->chunker->GetMaxChunks();$idx++){
				$chunkName_wc = $this->chunker->getChunkName($idx,"*");
				$cmd = "rm -f $chunkName_wc";
				VidiunLog::log("cleanup cmd:$cmd");
				$rv = null; $op = null;
				$output = exec($cmd, $op, $rv);
			}
			$mergedFilenameAudio = $this->chunker->getSessionName("audio");
			$cmd = "rm -f $mergedFilenameAudio* ".$concatFilenameLog = $this->chunker->getSessionName("concat");
			VidiunLog::log("cleanup cmd:$cmd");
			$rv = null; $op = null;
			$output = exec($cmd, $op, $rv);
			return;
			$cmd = "rm -f $setup->output*.$this->videoChunkPostfix*";
			$cmd.= " ".$this->chunker->getSessionName("audio")."*";
			$cmd.= " ".$this->chunker->getSessionName("session");
			VidiunLog::log("cleanup cmd:$cmd");
			$rv = null; $op = null;
			$output = exec($cmd, $op, $rv);
		}
		
		/********************
		 *
		 */
		protected function executeCmdline($cmdLine, $logFile=null)
		{
			return VProcessExecutionData::executeCmdline($cmdLine);
		}

		/********************
		 * 
		 */
		public function getErrorMessage()
		{
			switch($this->returnStatus){
				case VChunkedEncodeReturnStatus::InitializeError: 	 $errStr = "InitializeError"; break;
				case VChunkedEncodeReturnStatus::GenerateVideoError: $errStr = "GenerateVideoError"; break;
				case VChunkedEncodeReturnStatus::GenerateAudioError: $errStr = "GenerateAudioError"; break;
				case VChunkedEncodeReturnStatus::FixDriftError: 	 $errStr = "FixDriftError"; break;
				case VChunkedEncodeReturnStatus::AnalyzeError: 		 $errStr = "AnalyzeError"; break;
				case VChunkedEncodeReturnStatus::MergeError: 		 $errStr = "MergeError"; break;
				case VChunkedEncodeReturnStatus::MergeAttemptsError: $errStr = "MergeAttemptsError"; break;
				case VChunkedEncodeReturnStatus::MergeThreshError:   $errStr = "MergeThreshError"; break;
			}
			$msgStr = "Failure - error($errStr/$this->returnStatus),message(".implode(',',$this->returnMessages).")";
			return $msgStr;
		}

		/********************
		 * Save the sessionData to .ses file
		 */
		public function SerializeSession()
		{
			file_put_contents($this->chunker->getSessionName("session"), serialize($this));
		}
		
		/********************
		 * 
		 */
		protected static function getLogTail($logFilename, $size=5000)
		{
			$logTail = null;
			if(file_exists($logFilename)) {
				$fHd = fopen($logFilename,"r");
				$fileSz = filesize($logFilename);
				if($fileSz>$size)
					fseek($fHd,$fileSz-$size);
				$logTail = fread($fHd, $size);
				fclose($fHd);
			}
			return $logTail;
		}
	
		/********************
		 *
		 */
		abstract public function GenerateContent();

	}
	
