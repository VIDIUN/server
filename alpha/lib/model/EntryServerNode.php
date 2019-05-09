<?php


/**
 * Skeleton subclass for representing a row from the 'entry_server_node' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package Core
 * @subpackage model
 */
abstract class EntryServerNode extends BaseEntryServerNode {

	public function validateEntryServerNode()
	{
		if ((time() - $this->getUpdatedAt(null)) > vConf::get('marked_for_deletion_entry_server_node_timeout'))
			$this->deleteOrMarkForDeletion();
	}

	public function getCacheInvalidationKeys()
	{
		return array("entryServerNode:id=".strtolower($this->getId()), "entryServerNode:entryId".strtolower($this->getEntryId()));
	}
	
	protected function addTrackEntryInfo($trackEventType, $description, $entryId = null)
	{
		$te = new TrackEntry();
		$entryId = $entryId ? $entryId : $this->getEntryId();
		$te->setEntryId($entryId);
		$te->setTrackEventTypeId($trackEventType);
		$te->setDescription($description);
	
		TrackEntry::addTrackEntry($te);
	}

	public function getDCId()
	{
		$serverNode = ServerNodePeer::retrieveRegisteredServerNodeByPk($this->getServerNodeId());
		if ($serverNode)
			return $serverNode->getDc();
		return null;
	}

	public function deleteOrMarkForDeletion()
	{
		return $this->delete();
	}
} // EntryServerNode
