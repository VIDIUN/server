<?php
/**
 * Server Node service
 *
 * @service serverNode
 * @package api
 * @subpackage services
 */
class ServerNodeService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		$partnerId = $this->getPartnerId();
		if(!$this->getPartner()->getEnabledService(PermissionName::FEATURE_SERVER_NODE) && $partnerId != PARTNER::BATCH_PARTNER_ID)
			throw new VidiunAPIException(VidiunErrors::SERVICE_FORBIDDEN, $this->serviceName.'->'.$this->actionName);
			
		$this->applyPartnerFilterForClass('serverNode');
	}
	
	/**
	 * Adds a server node to the Vidiun DB.
	 *
	 * @action add
	 * @param VidiunServerNode $serverNode
	 * @return VidiunServerNode
	 */
	function addAction(VidiunServerNode $serverNode)
	{	
		$dbServerNode = $this->addNewServerNode($serverNode);
		
		$serverNode = VidiunServerNode::getInstance($dbServerNode, $this->getResponseProfile());
		return $serverNode;
	}
	
	/**
	 * Get server node by id
	 * 
	 * @action get
	 * @param int $serverNodeId
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 * @return VidiunServerNode
	 */
	function getAction($serverNodeId)
	{
		$dbServerNode = ServerNodePeer::retrieveByPK($serverNodeId);
		if (!$dbServerNode)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $serverNodeId);
		
		$serverNode = VidiunServerNode::getInstance($dbServerNode, $this->getResponseProfile());
		return $serverNode;
	}
	
	/**
	 * Update server node by id 
	 * 
	 * @action update
	 * @param int $serverNodeId
	 * @param VidiunServerNode $serverNode
	 * @return VidiunServerNode
	 */
	function updateAction($serverNodeId, VidiunServerNode $serverNode)
	{
		$dbServerNode = ServerNodePeer::retrieveByPK($serverNodeId);
		if (!$dbServerNode)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $serverNodeId);
			
		$dbServerNode = $serverNode->toUpdatableObject($dbServerNode);
		$dbServerNode->save();
		
		$serverNode = VidiunServerNode::getInstance($dbServerNode, $this->getResponseProfile());
		return $serverNode;
	}
	
	/**
	 * delete server node by id
	 *
	 * @action delete
	 * @param string $serverNodeId
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */
	function deleteAction($serverNodeId)
	{
		$dbServerNode = ServerNodePeer::retrieveByPK($serverNodeId);
		if(!$dbServerNode)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $serverNodeId);
	
		$dbServerNode->setStatus(ServerNodeStatus::DELETED);
		$dbServerNode->save();
	}
	
	/**
	 * Disable server node by id
	 *
	 * @action disable
	 * @param string $serverNodeId
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 * @return VidiunServerNode
	 */
	function disableAction($serverNodeId)
	{
		$dbServerNode = ServerNodePeer::retrieveByPK($serverNodeId);
		if(!$dbServerNode)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $serverNodeId);
	
		$dbServerNode->setStatus(ServerNodeStatus::DISABLED);
		$dbServerNode->save();
		
		$serverNode = VidiunServerNode::getInstance($dbServerNode, $this->getResponseProfile());
		return $serverNode;
	}
	
	/**
	 * Enable server node by id
	 *
	 * @action enable
	 * @param string $serverNodeId
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 * @return VidiunServerNode
	 */
	function enableAction($serverNodeId)
	{
		$dbServerNode = ServerNodePeer::retrieveByPK($serverNodeId);
		if(!$dbServerNode)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $serverNodeId);
	
		$dbServerNode->setStatus(ServerNodeStatus::ACTIVE);
		$dbServerNode->save();
		
		$serverNode = VidiunServerNode::getInstance($dbServerNode, $this->getResponseProfile());
		return $serverNode;
	}
	
	/**	
	 * @action list
	 * @param VidiunServerNodeFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunServerNodeListResponse
	 */
	public function listAction(VidiunServerNodeFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if(!$filter)
			$filter = new VidiunServerNodeFilter();
			
		if(!$pager)
			$pager = new VidiunFilterPager();
		
		return $filter->getTypeListResponse($pager, $this->getResponseProfile(), null);
	}
	
	/**
	 * Update server node status
	 *
	 * @action reportStatus
	 * @param string $hostName
	 * @param VidiunServerNode $serverNode
	 * @param VidiunServerNodeStatus $serverNodeStatus
	 * @return VidiunServerNode
	 */
	function reportStatusAction($hostName, VidiunServerNode $serverNode = null, $serverNodeStatus = ServerNodeStatus::ACTIVE)
	{
		$dbType = null;
		if ($serverNode)
		{
			$dbServerNode1 = $serverNode->toObject();
			if ($dbServerNode1)
			{
				$dbType = $dbServerNode1->getType();
			}
		}
		$dbServerNode = ServerNodePeer::retrieveActiveServerNode($hostName, $this->getPartnerId(), $dbType);

		//Allow serverNodes auto registration without calling add
		if (!$dbServerNode)
		{
			if($serverNode)
			{
				$dbServerNode = $this->addNewServerNode($serverNode);
			}
			else 
				throw new VidiunAPIException(VidiunErrors::SERVER_NODE_NOT_FOUND, $hostName);
		}


		$dbServerNode->setHeartbeatTime(time());
		$dbServerNode->setStatus($serverNodeStatus);
		$dbServerNode->save();
	
		$serverNode = VidiunServerNode::getInstance($dbServerNode, $this->getResponseProfile());
		return $serverNode;
	}
	
	private function addNewServerNode(VidiunServerNode $serverNode)
	{
		$dbServerNode = $serverNode->toInsertableObject();
		/* @var $dbServerNode ServerNode */
		$dbServerNode->setPartnerId($this->getPartnerId());
		$dbServerNode->setStatus(ServerNodeStatus::DISABLED);
		$dbServerNode->save();
		
		return $dbServerNode;
	}

	/**
	 * Mark server node offline
	 *
	 * @action markOffline
	 * @param string $serverNodeId
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 * @return VidiunServerNode
	 * @throws VidiunAPIException
	 */
	function markOfflineAction($serverNodeId)
	{
		$criteria = new Criteria();
		$criteria->add(ServerNodePeer::ID, $serverNodeId);
		$criteria->add(ServerNodePeer::STATUS, ServerNodeStatus::ACTIVE);
		$dbServerNode = ServerNodePeer::doSelectOne($criteria);

		if(!$dbServerNode)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $serverNodeId);

		$dbServerNode->setStatus(ServerNodeStatus::NOT_REGISTERED);
		$dbServerNode->save();

		$serverNode = VidiunServerNode::getInstance($dbServerNode, $this->getResponseProfile());
		return $serverNode;
	}

	/**
	 * Get the edge server node full path
	 *
	 * @action getFullPath
	 * @param string $hostName
	 * @param string $protocol
	 * @param string $deliveryFormat
	 * @param string $deliveryType
	 * @return string
	 */
	function getFullPathAction($hostName, $protocol = 'http', $deliveryFormat = null, $deliveryType = null)
	{
		$dbServerNode = ServerNodePeer::retrieveActiveServerNode($hostName, $this->getPartnerId(), VidiunServerNodeType::EDGE);
		if (!$dbServerNode)
		{
			throw new VidiunAPIException(VidiunErrors::SERVER_NODE_NOT_FOUND, $hostName);
		}
		/** @var EdgeServerNode $dbServerNode */
		return $dbServerNode->buildEdgeFullPath($protocol, $deliveryFormat, $deliveryType);
	}


}
