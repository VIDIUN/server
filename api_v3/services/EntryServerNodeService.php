<?php
/**
 * Base class for entry server node
 *
 * @service entryServerNode
 * @package api
 * @subpackage services
 */
class EntryServerNodeService extends VidiunBaseService
{

	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass("entry");
		$this->applyPartnerFilterForClass("entryServerNode");
	}

	/**
	 * Adds a entry_user_node to the Vidiun DB.
	 *
	 * @action add
	 * @param VidiunEntryServerNode $entryServerNode
	 * @return VidiunEntryServerNode
	 */
	private function addAction(VidiunEntryServerNode $entryServerNode)
	{
		$dbEntryServerNode = $this->addNewEntryServerNode($entryServerNode);

		$te = new TrackEntry();
		$te->setEntryId($dbEntryServerNode->getEntryId());
		$te->setTrackEventTypeId(TrackEntry::TRACK_ENTRY_EVENT_TYPE_ADD_ENTRY);
		$te->setDescription(__METHOD__ . ":" . __LINE__ . "::" . $dbEntryServerNode->getServerType().":".$dbEntryServerNode->getServerNodeId());
		TrackEntry::addTrackEntry($te);

		$entryServerNode = VidiunEntryServerNode::getInstance($dbEntryServerNode, $this->getResponseProfile());
		return $entryServerNode;

	}

	private function addNewEntryServerNode(VidiunEntryServerNode $entryServerNode)
	{
		$dbEntryServerNode = $entryServerNode->toInsertableObject();
		/* @var $dbEntryServerNode EntryServerNode */
		$dbEntryServerNode->setPartnerId($this->getPartnerId());
		$dbEntryServerNode->setStatus(EntryServerNodeStatus::STOPPED);
		$dbEntryServerNode->save();

		return $dbEntryServerNode;
	}

	/**
	 *
	 * @action update
	 * @param int $id
	 * @param VidiunEntryServerNode $entryServerNode
	 * @return VidiunEntryServerNode|null|object
	 * @throws VidiunAPIException
	 */
	public function updateAction($id, VidiunEntryServerNode $entryServerNode)
	{
		$dbEntryServerNode = EntryServerNodePeer::retrieveByPK($id);
		if (!$dbEntryServerNode)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $id);

		$dbEntryServerNode = $entryServerNode->toUpdatableObject($dbEntryServerNode);
		$dbEntryServerNode->save();

		$entryServerNode = VidiunEntryServerNode::getInstance($dbEntryServerNode, $this->getResponseProfile());
		return $entryServerNode;
	}

	/**
	 * Deletes the row in the database
	 * @action delete
	 * @param int $id
	 * @throws VidiunAPIException
	 */
	private function deleteAction($id)
	{
		$dbEntryServerNode = EntryServerNodePeer::retrieveByPK($id);
		if (!$dbEntryServerNode)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $id);
		$dbEntryServerNode->deleteOrMarkForDeletion();

	}

	/**
	 * @action list
	 * @param VidiunEntryServerNodeFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunEntryServerNodeListResponse
	 */
	public function listAction(VidiunEntryServerNodeFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunEntryServerNodeFilter();
		if (!$pager)
			$pager = new VidiunFilterPager();

		return $filter->getListResponse($pager, $this->getResponseProfile());
	}

	/**
	 * @action get
	 * @param string $id
	 * @return VidiunEntryServerNode
	 * @throws VidiunAPIException
	 */
	public function getAction($id)
	{
		$dbEntryServerNode = EntryServerNodePeer::retrieveByPK( $id );
		if(!$dbEntryServerNode)
			throw new VidiunAPIException(VidiunErrors::ENTRY_SERVER_NODE_NOT_FOUND, $id);

		$entryServerNode = VidiunEntryServerNode::getInstance($dbEntryServerNode);
		if (!$entryServerNode)
			return null;
		$entryServerNode->fromObject($dbEntryServerNode);
		return $entryServerNode;
	}
	
	/**
	 * Validates server node still registered on entry
	 *
	 * @action validateRegisteredEntryServerNode
	 * @param int $id entry server node id
	 *
	 * @throws VidiunAPIException
	 */
	public function validateRegisteredEntryServerNodeAction($id)
	{
		VidiunResponseCacher::disableCache();
		
		$dbEntryServerNode = EntryServerNodePeer::retrieveByPK( $id );
		if(!$dbEntryServerNode)
			throw new VidiunAPIException(VidiunErrors::ENTRY_SERVER_NODE_NOT_FOUND, $id);
		
		/* @var EntryServerNode $dbEntryServerNode */
		$dbEntryServerNode->validateEntryServerNode();
	}

	/**
	 * @action updateStatus
	 * @param string $id
	 * @param VidiunEntryServerNodeStatus $status
	 * @return VidiunEntryServerNode
	 * @throws VidiunAPIException
	 */
	public function updateStatusAction($id, $status)
	{
		$dbEntryServerNode = EntryServerNodePeer::retrieveByPK($id);
		if(!$dbEntryServerNode)
			throw new VidiunAPIException(VidiunErrors::ENTRY_SERVER_NODE_NOT_FOUND, $id);

		$dbEntryServerNode->setStatus($status);
		$dbEntryServerNode->save();

		$entryServerNode = VidiunEntryServerNode::getInstance($dbEntryServerNode);
		return $entryServerNode;
	}
}