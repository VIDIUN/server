<?php
/**
 * Base class for all the batch classes.
 *
 * @package Scheduler
 */
abstract class VBatchBase implements IVidiunLogger
{
	const PRIVILEGE_BATCH_JOB_TYPE = "jobtype";
	const DEFAULT_SLEEP_INTERVAL = 5;
	const DEFUALT_API_RETRIES_ATTEMPS = 3;
	
	/**
	 * @var VSchedularTaskConfig
	 */
	public static $taskConfig;

	/**
	 * @var string
	 */
	protected $sessionKey;

	/**
	 * @var int timestamp
	 */
	private $start;

	/**
	 * @var VidiunClient
	 */
	public static $vClient = null;

	/**
	 * @var VidiunConfiguration
	 */
	public static $vClientConfig = null;
	
	/**
	 * @var string
	 */
	public static $clientTag = null;

	/**
	 * @var boolean
	 */
	protected $isUnitTest = false;

	/**
	 * @var resource
	 */
	protected $monitorHandle = null;

	/**
	 * @param array $jobs
	 * @return array $jobs
	 */
	abstract public function run($jobs = null);

	protected function init()
	{
		set_error_handler(array(&$this, "errorHandler"));
	}

	public function errorHandler($errNo, $errStr, $errFile, $errLine)
	{

		$errorFormat = "%s line %d - %s";
		switch ($errNo)
		{
			case E_NOTICE:
			case E_STRICT:
			case E_USER_NOTICE:
				VidiunLog::log(sprintf($errorFormat, $errFile, $errLine, $errStr), VidiunLog::NOTICE);
				break;
			case E_USER_WARNING:
			case E_WARNING:
				VidiunLog::log(sprintf($errorFormat, $errFile, $errLine, $errStr), VidiunLog::WARN);
				break;
		}
	}

	public function done()
	{
		$done = "Done after [" . (microtime ( true ) - $this->start ) . "] seconds";
		VidiunLog::info($done);
		VidiunLog::stderr($done, VidiunLog::INFO);
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	public static function getType()
	{
		throw new Exception("Method getType must be overridden");
	}

	/**
	 * @param boolean $unitTest
	 */
	public function setUnitTest($unitTest)
	{
		$this->isUnitTest = $unitTest;
	}

	/**
	 * @return VidiunClient
	 */
	protected function getClient()
	{
		return self::$vClient;
	}


	static public function impersonate($partnerId)
	{
		self::$vClient->setPartnerId($partnerId);
	}

	static public function unimpersonate()
	{
		self::$vClient->setPartnerId(self::$taskConfig->getPartnerId());
	}

	protected function getSchedulerId()
	{
		return self::$taskConfig->getSchedulerId();
	}

	protected function getSchedulerName()
	{
		return self::$taskConfig->getSchedulerName();
	}

	protected function getId()
	{
		return self::$taskConfig->id;
	}

	protected function getIndex()
	{
		return self::$taskConfig->getTaskIndex();
	}

	protected function getName()
	{
		return self::$taskConfig->name;
	}

	protected function getConfigHostName()
	{
		return self::$taskConfig->getHostName();
	}

	/**
	 *
	 */
	protected function onBatchUp()
	{
		$this->onEvent(VBatchEvent::EVENT_BATCH_UP);
	}

	/**
	 *
	 */
	protected function onBatchDown()
	{
		$this->onEvent(VBatchEvent::EVENT_BATCH_DOWN);
	}

	/**
	 * @param string $file
	 * @param int $size
	 * @param int $event_id
	 */
	protected function onFileEvent($file, $size, $event_id)
	{
		$event = new VBatchEvent();
		$event->value_1 = $size;
		$event->value_2 = $file;

		$this->onEvent($event_id, $event);
	}

	/**
	 * @param int $event_id
	 * @param VBatchEvent $event
	 */
	protected function onEvent($event_id, VBatchEvent $event = null)
	{
		if(is_null($event))
			$event = new VBatchEvent();

		$event->batch_client_version = "1.0";
		$event->batch_event_time = time();
		$event->batch_event_type_id = $event_id;

		$event->batch_session_id = $this->sessionKey;
		$event->batch_id = $this->getIndex();
		$event->batch_name = $this->getName();
		$event->section_id = $this->getId();
		$event->batch_type = $this->getType();
		$event->location_id = $this->getSchedulerId();
		$event->host_name = $this->getSchedulerName();

		VDwhClient::send($event);
	}

	/**
	 * @param VSchedularTaskConfig $taskConfig
	 */
	public function __construct($taskConfig = null)
	{
		/*
		 *  argv[0] - the script name
		 *  argv[1] - serialized VSchedulerConfig config
		 */
		global $argv, $g_context;

		$this->sessionKey = uniqid('sess');
		$this->start = microtime(true);

		if(is_null($taskConfig))
		{
			$data = gzuncompress(base64_decode($argv[1]));
			self::$taskConfig = unserialize($data);
		}
		else
		{
			self::$taskConfig = $taskConfig;
		}

		if(!self::$taskConfig)
			die("Task config not supplied");

		date_default_timezone_set(self::$taskConfig->getTimezone());

		// clear seperator between executions
		VidiunLog::debug('___________________________________________________________________________________');
		VidiunLog::stderr('___________________________________________________________________________________', VidiunLog::DEBUG);
		VidiunLog::info(file_get_contents(dirname( __FILE__ ) . "/../VERSION.txt"));

		if(! (self::$taskConfig instanceof VSchedularTaskConfig))
		{
			VidiunLog::err('config is not a VSchedularTaskConfig');
			die;
		}

		VidiunLog::debug("set_time_limit({".self::$taskConfig->maximumExecutionTime."})");
		set_time_limit(self::$taskConfig->maximumExecutionTime);


		VidiunLog::info('Batch index [' . $this->getIndex() . '] session key [' . $this->sessionKey . ']');

		self::$vClientConfig = new VidiunConfiguration();
		self::$vClientConfig->setLogger($this);
		self::$vClientConfig->serviceUrl = self::$taskConfig->getServiceUrl();
		self::$vClientConfig->curlTimeout = self::$taskConfig->getCurlTimeout();

		if(isset(self::$taskConfig->clientConfig))
		{
			foreach(self::$taskConfig->clientConfig as $attr => $value)
				self::$vClientConfig->$attr = $value;
		}

		self::$vClient = new VidiunClient(self::$vClientConfig);
		self::$vClient->setPartnerId(self::$taskConfig->getPartnerId());

		self::$clientTag = 'batch: ' . self::$taskConfig->getSchedulerName() . ' ' . get_class($this) . " index: {$this->getIndex()} sessionId: " . UniqueId::get();
		self::$vClient->setClientTag(self::$clientTag);
		
		//$vs = self::$vClient->session->start($secret, "user-2", VidiunSessionType::ADMIN);
		$vs = $this->createVS();
		self::$vClient->setVs($vs);

		VDwhClient::setEnabled(self::$taskConfig->getDwhEnabled());
		VDwhClient::setFileName(self::$taskConfig->getDwhPath());
		$this->onBatchUp();

		VScheduleHelperManager::saveRunningBatch($this->getName(), $this->getIndex());
	}

	protected function getParams($name)
	{
		return  self::$taskConfig->$name;
	}

	protected function getAdditionalParams($name)
	{
		if(isset(self::$taskConfig->params) && isset(self::$taskConfig->params->$name))
			return self::$taskConfig->params->$name;

		return null;
	}

	/**
	 * @return array
	 */
	protected function getPrivileges()
	{
		return array('disableentitlement');
	}

	/**
	 * @param string $extraPrivileges
	 * @return string
	 */
	protected function createVS($extraPrivileges = null)
	{
		$partnerId = self::$taskConfig->getPartnerId();
		$sessionType = VidiunSessionType::ADMIN;
		$puserId = 'batchUser';
		$privileges = implode(',', $this->getPrivileges());
		if($extraPrivileges)
		{
			$privileges = $privileges.','.$extraPrivileges;
		}

		$adminSecret = self::$taskConfig->getSecret();
		$expiry = 60 * 60 * 24 * 30; // 30 days

		$rand = microtime(true);
		$expiry = time() + $expiry;
		$masterPartnerId = self::$taskConfig->getPartnerId();
		$additionalData = null;

		$fields = array($partnerId, '', $expiry, $sessionType, $rand, $puserId, $privileges, $masterPartnerId, $additionalData);
		$str = implode(";", $fields);

		$salt = $adminSecret;
		$hashed_str = $this->hash($salt, $str) . "|" . $str;
		$decoded_str = base64_encode($hashed_str);

		return $decoded_str;
	}

	/**
	 * Replace the current client vs with a new vs that also have $privileges append to it
	 * @param string $privileges
	 */
	protected function appendPrivilegesToVs($privileges)
	{
		if(!empty($privileges))
		{
			$newVS = $this->createVS($privileges);
			self::$vClient->setVs($newVS);
		}
	}

	/**
	 * @param string $salt
	 * @param string $str
	 * @return string
	 */
	private function hash($salt, $str)
	{
		return sha1($salt . $str);
	}

	/**
	 * @param string $localPath
	 * @return string
	 */
	protected function translateLocalPath2Shared($localPath)
	{
		$search = array();
		$replace = array();

		if(!is_null(self::$taskConfig->baseLocalPath) || !is_null(self::$taskConfig->baseSharedPath))
		{
			$search[] = self::$taskConfig->baseLocalPath;
			$replace[] = self::$taskConfig->baseSharedPath;
		}
		if(!is_null(self::$taskConfig->baseTempLocalPath) || !is_null(self::$taskConfig->baseTempSharedPath))
		{
			$search[] = self::$taskConfig->baseTempLocalPath;
			$replace[] = self::$taskConfig->baseTempSharedPath;
		}

		$search[] = '\\';
		$replace[] = '/';

		return str_replace($search, $replace, $localPath);
	}

	/**
	 * @param string $sharedPath
	 * @return string
	 */
	protected function translateSharedPath2Local($sharedPath)
	{
		$search = array();
		$replace = array();

		if(!is_null($search) || !is_null($replace))
		{
			$search = self::$taskConfig->baseSharedPath;
			$replace = self::$taskConfig->baseLocalPath;
		}

		return str_replace($search, $replace, $sharedPath);
	}

	/**
	 * @param array $files array(0 => array('name' => [name], 'path' => [path], 'size' => [size]), 1 => array('name' => [name], 'path' => [path], 'size' => [size]))
	 * @return string
	 */
	protected function checkFilesArrayExist(array $files)
	{
		foreach($files as $file)
			if(!$this->checkFileExists($file['path'], $file['size']))
				return false;

		return true;
	}

	protected static function foldersize($path)
	{
	  if(!file_exists($path)) return 0;
	  if(is_file($path)) return vFile::fileSize($path);
	  $ret = 0;
	  foreach(glob($path."/*") as $fn)
	    $ret += VBatchBase::foldersize($fn);
	  return $ret;
	}

	protected function setFilePermissions($filePath)
	{
		if(is_dir($filePath))
		{
			$chmod = 0750;
			if(self::$taskConfig->getDirectoryChmod())
				$chmod = octdec(self::$taskConfig->getDirectoryChmod());
				
			VidiunLog::debug("chmod($filePath, $chmod)");
			@chmod($filePath, $chmod);
			$dir = dir($filePath);
			while (false !== ($file = $dir->read()))
			{
				if($file[0] != '.')
					$this->setFilePermissions($filePath . DIRECTORY_SEPARATOR . $file);
			}
			$dir->close();
		}
		else
		{
			$chmod = 0640;
			if(self::$taskConfig->getChmod())
				$chmod = octdec(self::$taskConfig->getChmod());
		
			VidiunLog::debug("chmod($filePath, $chmod)");
			@chmod($filePath, $chmod);
		}
	}
	
	/**
	 * @param string $file
	 * @param int $size
	 * @return bool
	 */
	protected function checkFileExists($file, $size = null, $directorySync = null)
	{
		$this->setFilePermissions($file);
		
		if($this->isUnitTest)
		{
			VidiunLog::debug("Is in unit test");
			return true;
		}

			// If this is not a file but a directory, certain operations should be done diffrently:
			// - size calcultions
			// - the response from the client (to check the client size beaviour)
		if(is_null($directorySync))
			$directorySync = is_dir($file);
		VidiunLog::info("Check File Exists[$file] size[$size] isDir[$directorySync]");
		if(is_null($size))
		{
			clearstatcache();
			if($directorySync)
				$size=VBatchBase::foldersize($file);
			else
				$size = vFile::fileSize($file);
			if($size === false)
			{
				VidiunLog::debug("Size not found on file [$file]");
				return false;
			}
		}

		$retries = (self::$taskConfig->fileExistReties ? self::$taskConfig->fileExistReties : 1);
		$interval = (self::$taskConfig->fileExistInterval ? self::$taskConfig->fileExistInterval : 5);

		while($retries > 0)
		{
			$check = self::$vClient->batch->checkFileExists($file, $size);
				// In case of directorySync - do not check client sizeOk - to be revised
			if($check->exists && ($check->sizeOk || $directorySync))
			{
				$this->onFileEvent($file, $size, VBatchEvent::EVENT_FILE_EXISTS);
				return true;
			}
			$this->onFileEvent($file, $size, VBatchEvent::EVENT_FILE_DOESNT_EXIST);

			sleep($interval);
			$retries --;
		}

		VidiunLog::log("Passed max retries");
		return false;
	}

	public function __destruct()
	{
		$this->onBatchDown();
		VScheduleHelperManager::unlinkRunningBatch($this->getName(), $this->getIndex());
	}

	/**
	 * @param array $commands
	 */
	public function saveSchedulerCommands(array $commands)
	{
		$type = self::$taskConfig->type;
		$file = "$type.cmd";
		VScheduleHelperManager::saveCommand($file, $commands);
	}

	/**
	 * @param string $path
	 * @param int $rights
	 * @return NULL|string
	 */
	public static function createDir($path, $rights = 0777)
	{
		if(! is_dir($path))
		{
			if(! file_exists($path))
			{
				VidiunLog::info("Creating temp directory [$path]");
				mkdir($path, $rights, true);
			}
			else
			{
				// already exists but not a directory
				VidiunLog::err("Cannot create temp directory [$path] due to an error. Please fix and restart");
				return null;
			}
		}

		return $path;
	}

	protected function getMonitorPath()
	{
		return 'killer/VBatchKillerExe.php';
	}

	protected function startMonitor(array $files)
	{
		if($this->monitorHandle && is_resource($this->monitorHandle))
			return;

		$killConfig = new VBatchKillerConfig();

		$killConfig->pid = getmypid();
		$killConfig->maxIdleTime = self::$taskConfig->getMaxIdleTime();
		$killConfig->sleepTime = self::$taskConfig->getMaxIdleTime() / 2;
			/*
			Do not run killer process w/out set config->maxIdle
			*/
		if($killConfig->maxIdleTime<=0 || is_null($killConfig->maxIdleTime) ) {
			VidiunLog::info(__METHOD__.': The MaxIdleTime is not set properly. The Killer job will not run');
			return;
		}
		$killConfig->files = $files;
//$killConfig->files = array("/root/anatol/0_phxt8hsa.api.log");
		$killConfig->sessionKey = $this->sessionKey;
		$killConfig->batchIndex = $this->getIndex();
		$killConfig->batchName = $this->getName();
		$killConfig->workerId = $this->getId();
		$killConfig->workerType = $this->getType();
		$killConfig->schedulerId = $this->getSchedulerId();
		$killConfig->schedulerName = $this->getSchedulerName();
		$killConfig->dwhPath = self::$taskConfig->getDwhPath();
		$killConfig->dwhEnabled = self::$taskConfig->getDwhEnabled();

		$phpPath = 'php'; // TODO - get it from somewhere
		$killerPath = $this->getMonitorPath();
		$killerPathStr = base64_encode(serialize($killConfig));

		$cmdLine = "$phpPath $killerPath $killerPathStr";

		$descriptorspec = array(); // stdin is a pipe that the child will read from
		$other_options = array('suppress_errors' => FALSE, 'bypass_shell' => FALSE);

		VidiunLog::log("Now executing [$cmdLine]");
		VidiunLog::debug('Starting monitor');
		$this->monitorHandle = proc_open($cmdLine, $descriptorspec, $pipes, null, null, $other_options);
	}

	protected function stopMonitor()
	{
		if(!$this->monitorHandle || !is_resource($this->monitorHandle))
			return;

		VidiunLog::debug('Stoping monitor');

		$status = proc_get_status($this->monitorHandle);
		if($status['running'] == true)
		{
			proc_terminate($this->monitorHandle, 9); //9 is the SIGKILL signal
			proc_close($this->monitorHandle);

			$pid = $status['pid'];
			if(!is_numeric($pid))
				throw new Exception("Non numeric PID was supplied. " . $pid);

			if(function_exists('posix_kill'))
			{
				posix_kill($pid, 9);
			}
			else
			{
				exec("kill -9 $pid", $output); // for linux
				//exec("taskkill -F -PID $pid", $output); // for windows
			}
		}

		$this->monitorHandle = null;
	}

	/**
	 * @param string $fileName
	 * @return boolean
	 */
	public static function pollingFileExists($fileName)
	{
		$retries = (self::$taskConfig->inputFileExistRetries ? self::$taskConfig->inputFileExistRetries : 10);
		$interval = (self::$taskConfig->inputFileExistInterval ? self::$taskConfig->inputFileExistInterval : self::DEFAULT_SLEEP_INTERVAL);

		for ($retry = 0; $retry < $retries; $retry++)
		{
			clearstatcache();
			if (file_exists($fileName))
				return true;

			VidiunLog::log("File $fileName does not exist, try $retry, waiting $interval seconds");
			sleep($interval);
		}
		return false;
	}

	function log($message)
	{
		VidiunLog::log($message);
	}

	/**
	 * @param string $path path to encrypted file 
	 * @param string $key
	 * @return string the new temp clear file path
	 */
	public static function createTempClearFile($path, $key)
	{
		$iv = self::getIV();
		$tempPath = vEncryptFileUtils::getClearTempPath($path);
		VidiunLog::info("Creating tempFile with Key is: [$key] iv: [$iv] for path [$path] at [$tempPath]");
		if (vEncryptFileUtils::decryptFile($path, $key, $iv, $tempPath))
			return $tempPath;
		return null;
	}
	
	public static function getIV()
	{
		return vConf::get("encryption_iv");
	}

	public static function tryExecuteApiCall($callback, $params, $numOfRetries = self::DEFUALT_API_RETRIES_ATTEMPS, $apiIntervalInSec = self::DEFAULT_SLEEP_INTERVAL)
	{
		while ($numOfRetries-- > 0)
		{
			try 
			{
				$res = call_user_func_array($callback, $params);
				if (VBatchBase::$vClient->isError($res))
					throw new APIException($res);
				return $res;
			}
			catch  (Exception $ex) {
				VidiunLog::warning("API Call for " . print_r($callback, true) . " failed number of retires $numOfRetries");
				VidiunLog::err($ex->getMessage());
				sleep($apiIntervalInSec);
			}
		}
		return false;
	}
}
