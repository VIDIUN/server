<?php
/**
 * @package plugins.reach
 * @subpackage model.data
 */
class vAddEntryVendorTaskAction extends vRuleAction
{
	/**
	 * @var string
	 */
	protected $catalogItemIds;
	
	public function __construct() 
	{
		parent::__construct(ReachPlugin::getRuleActionTypeCoreValue(ReachRuleActionType::ADD_ENTRY_VENDOR_TASK));
	}
	
	/**
	 * @return the $catalogItemIds
	 */
	public function getCatalogItemIds()
	{
		return $this->catalogItemIds;
	}

	/**
	 * @param string $catalogItemIds
	 */
	public function setCatalogItemIds($catalogItemIds)
	{
		$this->catalogItemIds = $catalogItemIds;
	}
}
