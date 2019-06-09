<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchEntryParams extends VidiunESearchParams
{
	/**
	 * @var VidiunESearchEntryOperator
	 */
	public $searchOperator;

	private static $mapBetweenObjects = array
	(
		"searchOperator",
	);

	protected function initStatuses()
	{
		$statuses = explode(',', $this->objectStatuses);
		$enumType = VidiunEntryStatus::getEnumClass();

		$finalStatuses = array();
		foreach($statuses as $status)
		{
			$finalStatuses[] = vPluginableEnumsManager::apiToCore($enumType, $status);
		}
		return implode(',', $finalStatuses);
	}

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}

	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (!$object_to_fill)
		{
			$object_to_fill = new ESearchParams();
		}

		self::validateSearchOperator($this->searchOperator);

		if (!empty($this->objectStatuses))
		{
			$this->objectStatuses = $this->initStatuses();
		}

		return parent::toObject($object_to_fill, $props_to_skip);
	}

}
