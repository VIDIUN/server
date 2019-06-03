<?php
/**
 * @package api
 * @subpackage objects
 */
abstract class VidiunServerNode extends VidiunObject implements IFilterable, IApiObjectFactory
{
	/**
	 * @var int
	 * @readonly
	 * @filter eq,in
	 */
	public $id;
	
	/**
	 * @var int
	 * @readonly
	 */
	public $partnerId;
	
	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $createdAt;
	
	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $updatedAt;
	
	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $heartbeatTime;
	
	/**
	 * serverNode name
	 * 
	 * @var string
	 * @filter eq,in
	 */
	public $name;
	
	/**
	 * serverNode uniqe system name
	 * 
	 * @var string
	 * @filter eq,in
	 */
	public $systemName;
	
	/**
	 * 
	 * @var string
	 */
	public $description;
	
	/**
	 * serverNode hostName
	 *
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $hostName;
	
	/**
	 * @var VidiunServerNodeStatus
	 * @readonly
	 * @filter eq,in
	 */
	public $status;
	
	/**
	 * @var VidiunServerNodeType
	 * @readonly
	 * @filter eq,in
	 */
	public $type;
	
	/**
	 * serverNode tags
	 *
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $tags;
	
	/**
	 * DC where the serverNode is located
	 *
	 * @var int
	 * @readonly
	 * @filter eq,in
	 */
	public $dc;
	
	/**
	 * Id of the parent serverNode
	 *
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $parentId;

	/**
	 * Environment
	 *
	 * @var string
	 * @filter eq,in
	 */
	public $environment;



	private static $map_between_objects = array
	(
		"id",
		"partnerId",
		"createdAt",
		"updatedAt",
		"heartbeatTime",
		"name",
		"systemName",
		"description",
		"hostName",
		"status",
		"type",
		"tags",
		"dc",
		"parentId",
		"environment",
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}	
	
	public function validateForInsertByType($propertiesToSkip, $type)
	{
		$this->validateMandatoryAttributes(true);
		$this->validateDuplications(null, $type);
		$this->validateParentId();
	
		return parent::validateForInsert($propertiesToSkip);
	}
	
	public function validateForUpdateByType($sourceObject, $propertiesToSkip = array(), $type)
	{
		$this->validateMandatoryAttributes();
		$this->validateDuplications($sourceObject->getId(), $type);
		$this->validateParentId();
		$this->validateParentLoop($sourceObject->getId(), explode(",", $this->parentId));
				
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
	
	public function validateMandatoryAttributes($isInsert = false)
	{
		$this->validatePropertyMinLength("hostName", 1, !$isInsert);
		
		$this->validatePropertyMinLength("name", 1, !$isInsert);
	}
	
	public function validateDuplications($serverNodeId = null, $type)
	{
		if($this->hostName)		
			$this->validateHostNameDuplication($serverNodeId, $type);
		
		if($this->systemName)
			$this->validateSystemNameDuplication($serverNodeId, $type);
	}
	
	public function validateHostNameDuplication($serverNodeId = null, $type)
	{
		$c = VidiunCriteria::create(ServerNodePeer::OM_CLASS);
		
		if($serverNodeId)
			$c->add(ServerNodePeer::ID, $serverNodeId, Criteria::NOT_EQUAL);
		
		$c->add(ServerNodePeer::HOST_NAME, $this->hostName);
		$c->add(ServerNodePeer::TYPE, $type);
		
		if(ServerNodePeer::doCount($c))
			throw new VidiunAPIException(VidiunErrors::HOST_NAME_ALREADY_EXISTS, $this->hostName);
	}
	
	public function validateSystemNameDuplication($serverNodeId = null, $type)
	{
		$c = VidiunCriteria::create(ServerNodePeer::OM_CLASS);
	
		if($serverNodeId)
			$c->add(ServerNodePeer::ID, $serverNodeId, Criteria::NOT_EQUAL);
	
		$c->add(ServerNodePeer::SYSTEM_NAME, $this->systemName);
		$c->add(ServerNodePeer::TYPE, $type);
	
		if(ServerNodePeer::doCount($c))
			throw new VidiunAPIException(VidiunErrors::SYSTEM_NAME_ALREADY_EXISTS, $this->systemName);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($source_object, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($source_object, $responseProfile);
		
		if($this->shouldGet('status', $responseProfile) && !is_null($source_object->getHeartbeatTime())
				&& $source_object->getHeartbeatTime(null) < (time() - ServerNode::SERVER_NODE_TTL_TIME) && $this->status !== ServerNodeStatus::DISABLED)
			$this->status = ServerNodeStatus::NOT_REGISTERED;
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getExtraFilters()
	 */
	public function getExtraFilters()
	{
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getFilterDocs()
	 */
	public function getFilterDocs()
	{
		return array();
	}
	
	public static function getInstance($sourceObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$type = $sourceObject->getType();
		
		switch ($type)
		{
			case VidiunServerNodeType::EDGE:
				$object = new VidiunEdgeServerNode();
				break;
		
			default:
				$object = VidiunPluginManager::loadObject('VidiunServerNode', $type);
				if(!$object)
					$object = new VidiunServerNode();
				break;
		}
		
		if (!$object)
			return null;
		 
		$object->fromObject($sourceObject, $responseProfile);
		return $object;
	}
	
	public function validateParentId()
	{
		if(!isset($this->parentId) || $this->parentId == '')
			return;
		
		$inputParentIds = explode(",", $this->parentId);
		$dbParents = ServerNodePeer::retrieveByPKs($inputParentIds);
		$dbParentIds = array_map(function($serverNode) { return $serverNode->getId(); }, $dbParents);
		
		if(count($inputParentIds) !== count($dbParentIds))
		{
			$parentIdsDiff = array_diff($inputParentIds, $dbParentIds);
			throw new VidiunAPIException(VidiunErrors::SERVER_NODE_PROVIDED_AS_PARENT_NOT_FOUND, implode(",", $parentIdsDiff));
		}
	}
	
	public function validateParentLoop($currentServerNodeId, $directParentIds = array(), $parentIdsTree = array())
	{
		if(in_array($currentServerNodeId, $directParentIds) || in_array($currentServerNodeId, $parentIdsTree))
			throw new VidiunAPIException(VidiunErrors::SERVER_NODE_PARENT_LOOP_DETECTED, $currentServerNodeId);
		
		if(!count($directParentIds))
			return;
		
		$parentIdsTree[] = $currentServerNodeId;
		foreach ($directParentIds as $key => $parentId)
		{
			if(!$parentId)
				continue;
			
			$parentObj = ServerNodePeer::retrieveByPK($parentId);
			$this->validateParentLoop($parentId, $parentObj->getParentIdsArray(), $parentIdsTree);
		}
	}
}
