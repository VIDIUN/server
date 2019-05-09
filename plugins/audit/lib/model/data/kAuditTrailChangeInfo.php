<?php
class vAuditTrailChangeInfo extends vAuditTrailInfo
{
	/**
	 * @var array
	 */
	protected $changedItems;
	
	/**
	 * @return the $changedItems
	 */
	public function getChangedItems() {
		return $this->changedItems;
	}

	/**
	 * @param $changedItems the $changedItems to set
	 */
	public function setChangedItems(array $changedItems) {
		$this->changedItems = $changedItems;
	}
}
