<?php
/**
 * @package plugins.audit
 * @subpackage api.objects
 * @relatedService AuditTrailService
 */
class VidiunAuditTrail extends VidiunObject implements IRelatedFilterable
{
	/**
	 * @var int
	 * @filter eq
	 * @readonly
	 */
	public $id;
	
	/**
	 * @var time
	 * @filter gte,lte,order
	 * @readonly
	 */
	public $createdAt;

	/**
	 * Indicates when the data was parsed
	 * @var int
	 * @filter gte,lte,order
	 * @readonly
	 */
	public $parsedAt;

	/**
	 * @var VidiunAuditTrailStatus
	 * @filter eq,in
	 * @readonly
	 */
	public $status;

	/**
	 * @var VidiunAuditTrailObjectType
	 * @filter eq,in
	 */
	public $auditObjectType;

	/**
	 * @var string
	 * @filter eq,in
	 */
	public $objectId;

	/**
	 * @var string
	 * @filter eq,in
	 */
	public $relatedObjectId;

	/**
	 * @var VidiunAuditTrailObjectType
	 * @filter eq,in
	 */
	public $relatedObjectType;

	/**
	 * @var string
	 * @filter eq,in
	 */
	public $entryId;

	/**
	 * @var int
	 * @filter eq,in
	 * @readonly
	 */
	public $masterPartnerId;

	/**
	 * @var int
	 * @filter eq,in
	 * @readonly
	 */
	public $partnerId;

	/**
	 * @var string
	 * @filter eq,in
	 * @readonly
	 */
	public $requestId;

	/**
	 * @var string
	 * @filter eq,in
	 */
	public $userId;

	/**
	 * @var VidiunAuditTrailAction
	 * @filter eq,in
	 */
	public $action;

	/**
	 * @var VidiunAuditTrailInfo
	 */
	public $data;

	/**
	 * @var string
	 * @filter eq
	 * @readonly
	 */
	public $vs;

	/**
	 * @var VidiunAuditTrailContext
	 * @filter eq,in
	 * @readonly
	 */
	public $context;

	/**
	 * The API service and action that called and caused this audit
	 * @var string
	 * @filter eq,in
	 * @readonly
	 */
	public $entryPoint;

	/**
	 * @var string
	 * @filter eq,in
	 * @readonly
	 */
	public $serverName;

	/**
	 * @var string
	 * @filter eq,in
	 * @readonly
	 */
	public $ipAddress;

	/**
	 * @var string
	 * @readonly
	 */
	public $userAgent;

	/**
	 * @var string
	 * @filter eq
	 */
	public $clientTag;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var string
	 * @readonly
	 */
	public $errorDescription;
	
	private static $map_between_objects = array
	(
		"id",
		"createdAt",
		"parsedAt",
		"status",
		"auditObjectType" => "objectType",
		"objectId",
		"relatedObjectId",
		"relatedObjectType",
		"entryId",
		"masterPartnerId",
		"partnerId",
		"requestId",
		"userId" => "puserId",
		"action",
		"vs",
		"context",
		"entryPoint",
		"serverName",
		"ipAddress",
		"userAgent",
		"clientTag",
		"description",
		"errorDescription",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function getExtraFilters()
	{
		return array();
	}
	
	public function getFilterDocs()
	{
		return array();
	}
	
	/**
	 * @param AuditTrail $dbAuditTrail
	 * @param array $propsToSkip
	 * @return AuditTrail
	 */
	public function toObject($dbAuditTrail = null, $propsToSkip = array())
	{
		if(is_null($dbAuditTrail))
			$dbAuditTrail = new AuditTrail();
			
		$dbAuditTrail = parent::toObject($dbAuditTrail, $propsToSkip);
		
		if($this->data && $this->data instanceof VidiunAuditTrailInfo)
			$dbAuditTrail->setData($this->data->toObject());
			
		return $dbAuditTrail;
	}

	/**
	 * @param AuditTrail $dbAuditTrail
	 */
	public function doFromObject($dbAuditTrail, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($dbAuditTrail, $responseProfile);
		
		if($this->shouldGet('data', $responseProfile))
		{
			$dbData = $dbAuditTrail->getData();
			switch(get_class($dbData))
			{
				case 'vAuditTrailChangeInfo':
					$this->data = new VidiunAuditTrailChangeInfo();
					break;
					
				case 'vAuditTrailFileSyncCreateInfo':
					$this->data = new VidiunAuditTrailFileSyncCreateInfo();
					break;
					
				case 'vAuditTrailTextInfo':
					$this->data = new VidiunAuditTrailTextInfo();
					break;
					
				default:
	//				$this->data = new VidiunAuditTrailInfo();
					$this->data = null;
					break;
			}
			
			if($this->data && $dbData)
				$this->data->fromObject($dbData);
		}
	}
	
	/**
	 * @param AuditTrail $dbAuditTrail
	 * @param array $propsToSkip
	 * @return AuditTrail
	 */
	public function toInsertableObject($dbAuditTrail = null, $propsToSkip = array())
	{
		if(is_null($dbAuditTrail))
			$dbAuditTrail = new AuditTrail();
			
		return parent::toInsertableObject($dbAuditTrail, $propsToSkip);
	}
}
