<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunReportExportItem extends VidiunObject
{

	/**
	 * @var string
	 */
	public $reportTitle;

	/**
	 * @var VidiunReportExportItemType
	 */
	public $action;

	/**
	 * @var VidiunReportType
	 */
	public $reportType;

	/**
	 * @var VidiunReportInputFilter
	 */
	public $filter;

	/**
	 * @var string
	 */
	public $order;

	/**
	 * @var string
	 */
	public $objectIds;

	/**
	 * @var VidiunReportResponseOptions
	 */
	public $responseOptions;

	private static $map_between_objects = array
	(
		"reportTitle",
		"action",
		"reportType",
		"order",
		"objectIds",
		"responseOptions",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (!$object_to_fill)
		{
			$object_to_fill = new vReportExportItem();
		}
		$object_to_fill->setFilter($this->filter);

		return parent::toObject($object_to_fill, array('filter'));
	}

	protected function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($srcObj, $responseProfile);
		$this->filter = $srcObj->getFilter();
	}

}
