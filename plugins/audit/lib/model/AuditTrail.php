<?php


/**
 * Skeleton subclass for representing a row from the 'audit_trail' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package plugins.audit
 * @subpackage model
 */
class AuditTrail extends BaseAuditTrail implements IBaseObject
{	
	protected $puserId = null;
	
	const AUDIT_TRAIL_CONTEXT_CLIENT = -1;
	const AUDIT_TRAIL_CONTEXT_SCRIPT = 0;
	const AUDIT_TRAIL_CONTEXT_PS2 = 1;
	const AUDIT_TRAIL_CONTEXT_API_V3 = 2;
	
	const AUDIT_TRAIL_STATUS_PENDING = 1;
	const AUDIT_TRAIL_STATUS_READY = 2;
	const AUDIT_TRAIL_STATUS_FAILED = 3;
	
	const AUDIT_TRAIL_ACTION_CREATED = 'CREATED';
	const AUDIT_TRAIL_ACTION_COPIED = 'COPIED';
	const AUDIT_TRAIL_ACTION_CHANGED = 'CHANGED';
	const AUDIT_TRAIL_ACTION_DELETED = 'DELETED';
	const AUDIT_TRAIL_ACTION_VIEWED = 'VIEWED';
	const AUDIT_TRAIL_ACTION_CONTENT_VIEWED = 'CONTENT_VIEWED';
	const AUDIT_TRAIL_ACTION_FILE_SYNC_CREATED = 'FILE_SYNC_CREATED';
	const AUDIT_TRAIL_ACTION_RELATION_ADDED = 'RELATION_ADDED';
	const AUDIT_TRAIL_ACTION_RELATION_REMOVED = 'RELATION_REMOVED';
	
	private static $allwodObjectTypes;
	
	public static function getAllwodObjectTypes()
	{
		if(!self::$allwodObjectTypes)
		{
			$reflect = new ReflectionClass('AuditTrailObjectType');
			self::$allwodObjectTypes = $reflect->getConstants();
		}
		
		return self::$allwodObjectTypes;
	}
	
	public function __construct()
	{	
		parent::__construct();
		
		$this->setContext($this->getDefaultContext());
	}

	/**
	 * @return int context
	 */
	public function getDefaultContext() 
	{
		switch (vCurrentContext::$ps_vesion) 
		{
			case 'ps2':
				return self::AUDIT_TRAIL_CONTEXT_PS2;
			
			case 'ps3':
				return self::AUDIT_TRAIL_CONTEXT_API_V3;
			
			default:
				return self::AUDIT_TRAIL_CONTEXT_SCRIPT;
		}
	}
	
	/* (non-PHPdoc)
	 * @see audit/lib/model/om/BaseAuditTrail#setObjectType()
	 */
	public function setObjectType($v)
	{
		if(!in_array($v, self::getAllwodObjectTypes()))
			throw new vAuditTrailException("Object type [$v] not allowed", vAuditTrailException::OBJECT_TYPE_NOT_ALLOWED);
		
		return parent::setObjectType($v);
	} // setObjectType()
	
	/* (non-PHPdoc)
	 * @see audit/lib/model/om/BaseAuditTrail#setRelatedObjectType()
	 */
	public function setRelatedObjectType($v)
	{
		if(!in_array($v, self::getAllwodObjectTypes()))
			throw new vAuditTrailException("Object type [$v] not allowed", vAuditTrailException::OBJECT_TYPE_NOT_ALLOWED);
		
		return parent::setRelatedObjectType($v);
	} // setRelatedObjectType()

	/**
	 * Serialize the object and set the value of [data] column.
	 * 
	 * @param      vAuditTrailInfo $v new value
	 * @return     AuditTrail The current object (for fluent API support)
	 */
	public function setData($v)
	{
		$data = null;
		if($v instanceof vAuditTrailInfo)
			$data = serialize($v);
		
		return parent::setData($data);
	} // setData()

	/**
	 * Get the [data] column value and unserialize to object.
	 * 
	 * @return     vAuditTrailInfo
	 */
	public function getData()
	{
		$data = parent::getData();
		if(is_null($data))
			return null;
			
		try{
			return unserialize($data);
		}
		catch(Exception $e){
			return null;
		}
	} 
	
	public function getPuserId()
	{
		if(!$this->puserId)
		{
			$vuser = vuserPeer::retrieveByPK($this->getVuserId());
			if($vuser)
				$this->puserId = $vuser->getPuserId(); 
		}
			
		return $this->puserId;
	}
	
	public function setPuserId($v)
	{
		$this->puserId = $v;
		vuserPeer::setUseCriteriaFilter(false);
		$vuser = vuserPeer::getVuserByPartnerAndUid($this->getPartnerId(), $this->puserId, true);
		vuserPeer::setUseCriteriaFilter(true);
		
		if ( !$vuser )
		{
			// Associate new vuser for the specified partner
			$vuser = vuserPeer::createVuserForPartner($this->getPartnerId(), $v);
		}
		
		if($vuser)
		{
			return $this->setVuserId($vuser->getId());
		}
	}
	
	/* (non-PHPdoc)
	 * @see audit/lib/model/om/BaseAuditTrail#save()
	 */
	public function save(PropelPDO $con = null)
	{
		if(!vAuditTrailManager::traceEnabled($this->getPartnerId(), $this))
		{
			return 0;
		}

		if(is_null($this->getVuserId()))
		{
			$this->setPuserId(vCurrentContext::$uid);
			
			if (vCurrentContext::$uid == '')
				$this->setPuserId(vCurrentContext::$vs_uid);
		}
	
		if(is_null($this->getClientTag()))
			$this->setClientTag(vCurrentContext::$client_lang);
		
		$this->setRequestId(new UniqueId());
		$this->setMasterPartnerId(vCurrentContext::$master_partner_id);
		$this->setVs(vCurrentContext::$vs);
		$this->setIpAddress(vCurrentContext::$user_ip);
		$this->setServerName(vCurrentContext::$host);
		$this->setEntryPoint(vCurrentContext::getEntryPoint());
		$this->setUserAgent(requestUtils::getRemoteUserAgent());
		
		return parent::save($con);
	} // save()
	
} // AuditTrail
