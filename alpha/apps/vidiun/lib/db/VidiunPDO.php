<?php

/**
 *  @package server-infra
 *  @subpackage DB
 */
class VidiunPDO extends PropelPDO
{
	/**
	 * Use to set logged info for Vidiun logger
	 */
	const VIDIUN_ATTR_NAME = -1001;
		
	/**
	 * Use to disable transaction
	 */
	const VIDIUN_ATTR_NO_TRANSACTION = 'noTransaction';
	
	/**
	 * Sets the number of retries of doSave()
	 */
	const SAVE_MAX_RETRIES = 4; 
	
	protected static $comment = null;
	protected $vidiunOptions = array();
	protected $connectionName = null;
	protected $hostName = null;
	protected $enableComments = true;
	protected $configKey = null;

	/* (non-PHPdoc)
	 * @see PDO::__construct()
	 */
	public function __construct($dsn, $username = null, $password = null, $driver_options = array(), $config_key = null)
	{
		if(isset($driver_options[VidiunPDO::VIDIUN_ATTR_NAME]))
		{
			$this->connectionName = $driver_options[VidiunPDO::VIDIUN_ATTR_NAME];
			$this->vidiunOptions = DbManager::getVidiunConfig($this->connectionName);
		}
		
		list($mysql, $connection) = explode(':', $dsn);
		$arguments = explode(';', $connection);
		foreach($arguments as $argument)
		{
			list($argumentName, $argumentValue) = explode('=', $argument);
			if(strtolower($argumentName) == 'host')
			{
				$this->hostName = $argumentValue;
				break;
			}
		}
		$this->configKey = $config_key;
					
		$connStart = microtime(true);

		parent::__construct($dsn, $username, $password, $driver_options);

		$connTook = microtime(true) - $connStart;
		
		VidiunLog::debug("conn took - $connTook seconds to $dsn");
		
		VidiunMonitorClient::monitorConnTook($dsn, $connTook);		

		$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('VidiunStatement'));
	}

	public function setCommentsEnabled($enabled) 
	{
		$this->enableComments = $enabled;
	}

	public function getConnectionName() 
	{
		return $this->connectionName;
	}

	public function getHostName() 
	{
		return $this->hostName;
	}

	public function getComment() 
	{
		if(!self::$comment)
		{
			$uniqueId = new UniqueId();
			self::$comment = (isset($_SERVER["HOSTNAME"]) ? $_SERVER["HOSTNAME"] : gethostname());
			self::$comment .= "[$uniqueId]";
		}
		
		return self::$comment . "[$this->connectionName]";
	}
	
	protected function getCommentWrapped() 
	{
		if(!$this->enableComments)
			return '';
			
		$commentBody = $this->getComment();
		return "/* $commentBody */ ";
	}
	
	/* (non-PHPdoc)
	 * @see PropelPDO::prepare()
	 */
	public function prepare($sql, $driver_options = array())
	{
		$comment = $this->getCommentWrapped();
		$sql = $comment . $sql;
		
		return parent::prepare($sql, $driver_options);
	}
	
	/* (non-PHPdoc)
	 * @see PDO::exec()
	 */
	public function exec($sql)
	{
		$comment = $this->getCommentWrapped();
		$sql = $comment . $sql;
		
		VidiunLog::debug($sql);
		
		$sqlStart = microtime(true);
		try
		{
			$result = parent::exec($sql);
		}
		catch(PropelException $pex)
		{
			VidiunLog::alert($pex->getMessage());
			throw new PropelException("Database error");
		}
		$sqlTook = microtime(true) - $sqlStart;
		VidiunLog::debug("Sql took - " . $sqlTook . " seconds");
		VidiunMonitorClient::monitorDatabaseAccess($sql, $sqlTook, $this->hostName);
		
		return $result;
	}

	public function queryAndFetchAll($sql, $fetchStyle, &$sqlConditions, $columnIndex = 0, $filter = null)
	{
		$finalSql = str_replace(vApiCache::VIDIUN_COMMENT_MARKER, $this->getComment(), $sql);
		
		VidiunLog::debug($finalSql);
		
		$sqlStart = microtime(true);
		$stmt = parent::query($finalSql);
		
		$sqlTook = microtime(true) - $sqlStart;
		VidiunLog::debug("Sql took - " . $sqlTook . " seconds");
		VidiunMonitorClient::monitorDatabaseAccess($sql, $sqlTook, $this->hostName);
		
		if (!$stmt)
			return false;
		
		if ($fetchStyle == PDO::FETCH_COLUMN)
			$result = $stmt->fetchAll($fetchStyle, $columnIndex);
		else
			$result = $stmt->fetchAll($fetchStyle);
		
		if(is_null($result))
			return false;
			
		if(!$result)
			$result = array();
			
		$filteredResult = vApiCache::filterQueryResult($result, $filter);
	
		$sqlConditions[] = array($this->configKey, $sql, $fetchStyle, $columnIndex, $filter, $filteredResult);		
		
		return $filteredResult;
	}
	
	/* (non-PHPdoc)
	 * @see PDO::query()
	 */
	public function query()
	{
		vApiCache::disableConditionalCache();
	
		$args = func_get_args();
		
		$sql = $args[0];
		VidiunLog::debug($sql);
		
		$comment = $this->getCommentWrapped();
		$sql = $comment . $sql;
		
		$sqlStart = microtime(true);
		try
		{
			if (version_compare(PHP_VERSION, '5.3', '<'))
				$result = call_user_func_array(array($this, 'parent::query'), $args);
			else
				$result = call_user_func_array('parent::query', $args);
		}
		catch(PropelException $pex)
		{
			VidiunLog::alert($pex->getMessage());
			throw new PropelException("Database error");
		}
		$sqlTook = microtime(true) - $sqlStart;
		VidiunLog::debug("Sql took - " . $sqlTook . " seconds");
		VidiunMonitorClient::monitorDatabaseAccess($sql, $sqlTook, $this->hostName);
		
		return $result;
	}
	
	public function getVidiunOption($option)
	{
		if(isset($this->vidiunOptions[$option]))
			return $this->vidiunOptions[$option];
			
		return null;
	}
	
	public function beginTransaction()
	{
		if($this->getVidiunOption(VidiunPDO::VIDIUN_ATTR_NO_TRANSACTION))
			return true;
		
		return parent::beginTransaction();
	}
}
