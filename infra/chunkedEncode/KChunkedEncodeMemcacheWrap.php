<?php
 
/*****************************
 * Includes & Globals
 */
ini_set("memory_limit","512M");

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/****************************
	 * VChunkedEncodeMemcacheWrap
	 */
	class VChunkedEncodeMemcacheWrap implements VChunkedEncodeDistrExecInterface
	{
		const WRITE_IDX = "ChunkedEncodeWriteIdx";
		const READ_IDX  = "ChunkedEncodeReadIdx";
		
		const JOB_KEYNAME_PREFIX = "ChunkedEncodeJob";
		const SEMAPHORE_PREFIX = "ChunkedEncodeSemaphore";
		
		protected $cacheStore = null;
		protected $expiry = 86400;
		protected $memcacheConfig = null;
		
		protected $writeIndex = null;
		protected $readIndex = null;
				
		protected $storeToken = null;
		
		/* ---------------------------
		 * C'tor
		 */
		public function __construct($storeToken=null)
		{
			$this->storeToken = $storeToken;
		}

		/* ---------------------------
		 * Setup
		 */
		public function Setup($config)
		{
			$this->cacheStore = new vInfraMemcacheCacheWrapper();
			if($this->cacheStore->init($config)===false){
				VidiunLog::log("cacheStore failed to initialize with config:".serialize($config));
				false;
			}
			$this->memcacheConfig = $config;
			return $this->initialize();
		}
		
		/* ---------------------------
		 * Setup
		 */
		public function SetupWithCacheType($cacheType)
		{
			if(!isset($cacheType))
				$cacheType = vCacheManager::CACHE_TYPE_LIVE_MEDIA_SERVER . '_0';
				/*
				 * Create the memcacah store object
				 */
			$this->cacheStore = vCacheManager::getSingleLayerCache($cacheType);
			if(!$this->cacheStore) {
				VidiunLog::log("cacheStore is null. cacheType: $cacheType . returning false");
				return false;
			}
			
			return $this->initialize();
		}
		
		/* ---------------------------
		 * SaveJob
		 *	Store job to memcache storage
		 */
		public function SaveJob($job)
		{
			$key = $this->getJobKeyName($job->keyIdx);
			$str = serialize($job);
			if($this->set($key, $str, $this->expiry)===false){
				VidiunLog::log("Session($job->session) - Failed to set job $key($str)");
				return false;
			}
				// Just to remove non printables from the log msg
			$str = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $str);
			VidiunLog::log("Session($job->session) - Set job $key($str)");
			return true;
		}
		 
		/* ---------------------------
		 * AddJob
		 */
		public function AddJob($job)
		{
			$job->keyIdx = $this->allocateJobIndex();
			return $this->SaveJob($job);
		}
		
		/* ---------------------------
		 * FetchJob
		 *	Get job from memcache storage
		 */
		public function FetchJob($keyIdx)
		{
			$key = $this->getJobKeyName($keyIdx);
			/*
			 * 10 attempts to get the job object
			 */
			$maxTries = 10;
			for($try=0; $try<$maxTries; $try++) {
				if(($jobStr=$this->get($key))!==false){
					break;
				}
				sleep(0.3);
				VidiunLog::log("Attempt($try) to fetch job ($keyIdx)");
			}
			if($try==$maxTries){
				VidiunLog::log("Job($keyIdx) - Failed to get job ($keyIdx)");
				return false;
			}
			$job = unserialize($jobStr);
			return $job;
		}
		 
		/* ---------------------------
		 * DeleteJob
		 */
		public function DeleteJob($keyIdx)
		{
			$key = $this->getJobKeyName($keyIdx);
			$this->delete($key);
			VidiunLog::log("Job($keyIdx) - Deleted");
			return true;
		}
		
		/* ---------------------------
		 * GetActiveSessions
		 *	
		 */
		public function GetActiveSessions()
		{
			if($this->fetchReadWriteIndexes($writeIndex, $readIndex)===false){
				VidiunLog::log("ERROR: Missing write or read index ");
				return false;
			}
				/*
				 * Stop fetching attempts if there are no unread objects
				 */
			if($readIndex>=$writeIndex+1) 
				return array();;

			VidiunLog::log("RD:$readIndex, WR:$writeIndex");
			$sessions = array();
			for($idx=$readIndex; $idx<$writeIndex; $idx++){
				$job = $this->FetchJob($idx);
				if($job===false){
					continue;
				}
				if(array_key_exists($job->session, $sessions)==true)
					$sessions[$job->session] = $sessions[$job->session]+1;
				else
					$sessions[$job->session] = 1;
			}
			return $sessions;
		}
		
		/* ---------------------------
		 * initilize
		 */
		protected function initialize()
		{
				/*
				 * Get the global /read/write indexes,
				 * if don't exist - create them w/out expiry time
				 */
			$this->fetchReadWriteIndexes($writeIndex, $readIndex);
			$writeIndexKeyName = $this->getWriteIndexKeyName();
			if(!isset($writeIndex)){
				$writeIndex = 0;
				if($this->set($writeIndexKeyName,$writeIndex,0)===false) {
					VidiunLog::log("Failed to create WR index ($writeIndexKeyName)");
					return false;
				}
			}
			$this->writeIndex = $writeIndex;
			VidiunLog::log("Current WR index value ($writeIndexKeyName:$writeIndex)");
			
			$readIndexKeyName = $this->getReadIndexKeyName();
			if(!isset($readIndex)){
				$readIndex = 1;
				if($this->set($readIndexKeyName,$readIndex,0)===false) {
					VidiunLog::log("Failed to create RD index ($readIndexKeyName)");
					return false;
				}
			}
			$this->readIndex = $readIndex;
			VidiunLog::log("Current RD index value ($readIndexKeyName:$readIndex)");
			return true;
		}
		
		/* ---------------------------
		 *
		 
		public function incrementReadIndex()
		{
			return $this->increment($this->getReadIndexKeyName());
		}
*/
		/* ---------------------------
		 * fetchReadWriteIndexes
		 *	
		 */
		public function fetchReadWriteIndexes(&$writeIndex, &$readIndex)
		{
			$writeIndexKeyName = $this->getWriteIndexKeyName();
			if(($writeIndex=$this->get($writeIndexKeyName))===false){
				VidiunLog::log("Missing WR index ($writeIndexKeyName)");
				$writeIndex = null;
			}
			else
				VidiunLog::log("Current WR index value ($writeIndexKeyName:$writeIndex)");

			$readIndexKeyName = $this->getReadIndexKeyName();		
			if(($readIndex=$this->get($readIndexKeyName))===false){
				VidiunLog::log("Missing RD index ($readIndexKeyName)");
				$readIndex = null;
			}
			else
				VidiunLog::log("Current RD index value ($readIndexKeyName: $readIndex)");
			if(isset($readIndex) && isset($writeIndex))
				return true;
			else
				return false;
		}
		
		/* ---------------------------
		 * getReadIndexKeyName
		 *	Compose global read-index
		 */
		protected function getReadIndexKeyName()
		{
			return self::READ_IDX.$this->storeToken;
		}

		/* ---------------------------
		 * getWriteIndexKeyName
		 * 	Compose global write-index
		 */
		protected function getWriteIndexKeyName()
		{
			return self::WRITE_IDX.$this->storeToken;
		}

		/* ---------------------------
		 * getJobKeyName
		 *	Compose job key name, 
		 *	using manager::JOB_KEYNAME_PREFIX const and 'storeToken'
		 */
		protected function getJobKeyName($keyIdx)
		{
			return self::JOB_KEYNAME_PREFIX.$this->storeToken."_$keyIdx";
		}

		/* ---------------------------
		 * getSemaphoreKeyName
		 *	Compose job semaphore key name, 
		 * 	using manager::SEMAPHORE_PREFIX const and 'storeToken'
		 */
		protected function getSemaphoreKeyName($keyIdx)
		{
			return self::SEMAPHORE_PREFIX.$this->storeToken."_$keyIdx";
		}
		
		/* ---------------------------
		 * allocateJobIndex
		 *	Allocate an index for a new job
		 */
		protected function allocateJobIndex()
		{
			return $this->increment($this->getWriteIndexKeyName());
		}

		/* ---------------------------
		 * lock
		 *	based on memcache 'add'  functionality  
		 */
		protected function lock($key, $val, $attempDuration=60, $attemptSleep=0.005, $expiry=3600) // expiry = 1hr
		{
			$waited=0;
	VidiunLog::log("key($key), val($val)");
			do {
				$addLasted = microtime(true);
				$rv = $this->cacheStore->add($key, $val, $expiry);
				$addLasted = round((microtime(true)-$addLasted),3);
				if($rv===true){
					break;
				}
				$sleepLasted = microtime(true);
				usleep($attemptSleep*1000000);
				$sleepLasted = round((microtime(true)-$sleepLasted),5);
				$waited+=($addLasted+$sleepLasted);
				$attempDuration-= ($addLasted+$sleepLasted);
	VidiunLog::log("attempDuration($attempDuration)");
			}
			while($attempDuration>0);
			if($waited>0){
	VidiunLog::log("EXIT:key($key),val($val): rv($rv),waited($waited)");
			}
			else {
	VidiunLog::log("EXIT:key($key),val($val): rv($rv)");
			}
			return $rv;
		}

		/* ---------------------------
		 * delete
		 */
		protected function delete($key)
		{
			return $this->cacheStore->delete($key);
		}
		
		/* ---------------------------
		 * add
		 *	memcache low level 
		 */
		protected function add($key, $val, $expiry=86400)
		{
			return $this->cacheStore->add($key, $val, $expiry);
		}
		
		/* ---------------------------
		 * set
		 *	memcache low level 
		 */
		protected function set($key, $val, $expiry=86400)
		{
			return $this->cacheStore->set($key, $val, $expiry);
		}
		
		/* ---------------------------
		 * get
		 *	memcache low level 
		 */
		protected function get($key)
		{
			return $this->cacheStore->get($key);
		}
		
		/* ---------------------------
		 * increment
		 *	memcache low level 
		 */
		protected function increment($key, $value=1)
		{
			return $this->cacheStore->increment($key, $value);
		}
		
		/* ---------------------------
		 * decrement
		 *	memcache low level 
		 */
		protected function decrement($key, $value=1)
		{
			return $this->cacheStore->decrement($key, $value);
		}
		
		/* ---------------------------
		 * ExecuteSession
		 */
		public static function ExecuteSession($host, $port, $token, $concurrent, $sessionName, $cmdLine)
		{
			VidiunLog::log("host:$host, port:$port, token:$token, concurrent:$concurrent, sessionName:$sessionName, cmdLine:$cmdLine");
			$storeManager = new VChunkedEncodeMemcacheWrap($token);
				// 'flags=1' stands for 'compress stored data'
			$config = array('host'=>$host, 'port'=>$port, 'flags'=>1);
			$storeManager->Setup($config);
			
			$setup = new VChunkedEncodeSetup;
			$setup->concurrent = $concurrent;
			$setup->cleanUp = 0;
			$setup->cmd = $cmdLine;
			
			$session = new VChunkedEncodeSessionManager($setup, $storeManager, $sessionName);
			
			if(($rv=$session->Initialize())!=true) {
				$session->Report();
				return $rv;
			}
			$rv = $session->Generate();
			$session->Report();
			return $rv;
		}

	}
	/*****************************
	 * End of VChunkedEncodeMemcacheWrap
	 *****************************/
	
	/****************************
	 * VChunkedEncodeMemcacheScheduler
	 */
	class VChunkedEncodeMemcacheScheduler extends VChunkedEncodeMemcacheWrap implements VChunkedEncodeDistrSchedInterface
	{
		protected $tmpFolder = null;
		
		/* ---------------------------
		 * C'tor
		 */
		public function __construct($storeToken=null, $tmpFolder=null)
		{
			parent::__construct($storeToken);
			$this->tmpFolder = $tmpFolder;
		}
		
		/* ---------------------------
		 * FetchNextJob
		 *	Get next job from the mmecache storage
		 * 	if missing => return false 
		 */
		public function FetchNextJob()
		{
			$writeIndex = null;
			$readIndex = null;
			
				// semaphore token - process-id + hostname + rand
			$semaphoreToken = getmypid().".".gethostname().".".rand();
			while(true) {
				if($this->fetchReadWriteIndexes($writeIndex, $readIndex)===false){
					VidiunLog::log("ERROR: Missing write or read index ");
					return false;
				}
					/*
					 * Stop fetching if there are no unread objects
					 */
				if($readIndex>=$writeIndex+1) 
					break;

				VidiunLog::log("RD:$readIndex), WR:$writeIndex");

					/*
					 * Try to lock the next unread job object
					 * if failed - carry on to next
					 */
				$semaphoreKey = $this->getSemaphoreKeyName($readIndex);
				$rv = $this->lock($semaphoreKey, $semaphoreToken, 0);
				if($rv!==true){
					if($this->setReadIndex($readIndex+1)===false)
						return false;
					VidiunLog::log("Unable to lock readIndex($readIndex), skipping to the next ");
					continue;
				}
				
					/*
					 * Try to fetch the job object from the memcache storage
					 * If failed - delete the sempahore (unlock) and try the next one
					 */
				$job = $this->FetchJob($readIndex); 
				if($job!==false) {
					return $job;
				}
				else {
					$this->delete($semaphoreKey);
					VidiunLog::log("Unable to access job ($readIndex), skip to next");
					if($this->setReadIndex($readIndex+1)===false)
						return false;
				}
			}
			
			return null;
		}
			
		/* ---------------------------
		 * RefreshJobs
		 */
		public function RefreshJobs($maxSlots, &$jobs)
		{
				/*
				 * Get list of per scheduler running jobs
				 */
			$refreshed = $this->refetchJobs($jobs);
			$running = count($jobs);
			if($running>0 && $refreshed==0){
				return false;
			}
				/*
				 * If there are no free execution slots - wait and retry
				 */
			if($running>=$maxSlots) {
				VidiunLog::log("Running:$running - No free job slots, maxSlots:$maxSlots");
				return null;
			}

				/*
				 * If there are no pending jobs - wait and retry
				 */
			$job = $this->FetchNextJob();
			if($job===null){
				VidiunLog::log("Running:$running - No pending jobs");
				return null;
			}
			else if($job===false){
				VidiunLog::log("Failed to fetch next job");
				return false;
			}

			if($this->ExecuteJob($job)==true)
				$jobs[$job->keyIdx] = $job;
			
			return true;
		}

		/* ---------------------------
		 * refetchJobs
		 * 	Reload the givven jobs array from memcache storage
		 * 	Remove finished or retried jobs 
		 */
		protected function refetchJobs(&$jobs)
		{
			$cnt=0;
			foreach($jobs as $idx=>$job) {
				$job = $this->FetchJob($job->keyIdx);
				if($job===false) {
					VidiunLog::log("Missing $idx");
					continue;
				}
				if($job->isFinished() || $job->isRetry()) {
					unset($jobs[$idx]);
				}
				else {
					$jobs[$idx] = $job;
				}
				$cnt++;
			}
			return $cnt;
		}

		/* ---------------------------
		 *
		 */
		public function ExecuteJob($job)
		{
			$job->queueTime = time();
			$this->SaveJob($job);

			if(is_array($job->cmdLine) && count($job->cmdLine)>1) {
				$outFilename = $job->cmdLine[1];
				$pInfo = pathinfo($outFilename);
				$this->tmpFolder = realpath($pInfo['dirname']);
			}
			
			$logName = $this->tmpFolder;
			$logName.= "/$job->session"."_$job->id"."_$job->keyIdx".".log";
			{
				$cmdLine = 'php -r "';
				$cmdLine.= 'require_once \'/opt/vidiun/app/batch/bootstrap.php\';';
				/********************************************************
				 * The bellow includes to be removed for production
				 ********************************************************
				 
				{
					$cmdLine.= 'require_once \'/opt/vidiun/app/alpha/scripts/bootstrap.php\';';
					$cmdLine.= 'require_once \'/opt/vidiun/app/batch/client/VidiunTypes.php\';';
					$dirName = "/opt/vidiun/app/infra/chunkedEncode";
					$cmdLine.= 'require_once \''.$dirName.'/VChunkedEncodeUtils.php\';';
					$cmdLine.= 'require_once \''.$dirName.'/VChunkedEncode.php\';';
					$cmdLine.= 'require_once \''.$dirName.'/VBaseChunkedEncodeSessionManager.php\';';
					$cmdLine.= 'require_once \''.$dirName.'/VChunkedEncodeSessionManager.php\';';
					$cmdLine.= 'require_once \''.$dirName.'/VChunkedEncodeDistrExecInterface.php\';';
					$cmdLine.= 'require_once \''.$dirName.'/VChunkedEncodeMemcacheWrap.php\';';
				}
				*/
				$cmdLine.= '\$rv=VChunkedEncodeMemcacheScheduler::ExecuteJobCommand(';
				$cmdLine.= '\''.($this->memcacheConfig['host']).'\',';
				$cmdLine.= '\''.($this->memcacheConfig['port']).'\',';
				$cmdLine.= '\''.($this->storeToken).'\',';
				$cmdLine.= $job->keyIdx.');';
				$cmdLine.= 'if(\$rv==false) exit(1);';
				$cmdLine.= '"';
			}
			$tmp_ce_process_file = $this->tmpFolder."/tmp_ce_".$job->session."_".$job->keyIdx.".log";
			$cmdLine.= " > $logName 2>&1 & echo $! > $tmp_ce_process_file";

			VidiunLog::log($cmdLine);
			$output = system($cmdLine, $rv);
			if($rv!=0) {
				$job->state = $job::STATE_FAIL;
				$this->SaveJob($job);
			}
			else {
				$job->process = (int)file_get_contents($tmp_ce_process_file);
				unlink($tmp_ce_process_file);
			}
			VidiunLog::log("id:$job->id,keyIdx:$job->keyIdx,rv:$rv,process:$job->process,cmdLine:$cmdLine");
			return true;
		}
		
		/* ---------------------------
		 *
		 */
		public static function ExecuteJobCommand($host, $port, $token, $jobIndex)
		{
			VidiunLog::log("host:$host, port:$port, token:$token, jobIndex:$jobIndex");
			$storeManager = new VChunkedEncodeMemcacheWrap($token);
				// 'flags=1' stands for 'compress stored data'			
			$config = array('host'=>$host, 'port'=>$port, 'flags'=>1);
			$storeManager->Setup($config);
			
			if(!isset($jobIndex)) {
				return false;
			}

			$job = $storeManager->FetchJob($jobIndex);
			if($job===false)
				return false;
			
			$job->startTime = time();
			$job->process = getmypid();
			$job->hostname = gethostname();
			$job->state = $job::STATE_RUNNING;
			$storeManager->SaveJob($job);
			
			$outFilename = null;
			if(is_array($job->cmdLine)) {
				$cmdLine = $job->cmdLine[0];
				$outFilename = $job->cmdLine[1];
			}
			else
				$cmdLine = $job->cmdLine;
			exec($cmdLine,$op,$rv);
			$job->finishTime = time();
			if($rv!=0) {
				$job->state = $job::STATE_FAIL;
				$storeManager->SaveJob($job);
					$storeManager->SaveJob($job);
					$rvStr = "FAILED - rv($rv),";
			}
			else {
				if(isset($outFilename)) {
					$stat = new VChunkFramesStat($outFilename/*,ffmpegBin,ffprobeBin*/);
					$job->stat = $stat;
				}

				$job->state = $job::STATE_SUCCESS;
				$storeManager->SaveJob($job);
				$rvStr = "SUCCESS -";
			}
			VidiunLog::log("$rvStr elap(".($job->finishTime-$job->startTime)."),process($job->process),".print_r($job,1));
			return ($rv==0? true: false);
		}

		/* ---------------------------
		 *
		 */
		protected function setReadIndex($readIndex)
		{
			$maxTry=10;
			for($try=0; $try<$maxTry; $try++) {
				$rv = $this->set($this->getReadIndexKeyName(),$readIndex,0);
				if($rv!==false){
					return $rv;
				}
				VidiunLog::log("Attempt($try) to set RD($readIndex)");
				sleep(0.3);
			}
			return false;
		}

	}
	/*****************************
	 * End of VChunkedEncodeMemcacheScheduler
	 *****************************/

