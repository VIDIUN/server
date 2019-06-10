<?php
/**
 * @package plugins.attachment
 * @relatedService AttachmentAssetService
 * @subpackage api.filters.base
 * @abstract
 */
abstract class VidiunAttachmentAssetBaseFilter extends VidiunAssetFilter
{
	static private $map_between_objects = array
	(
		"formatEqual" => "_eq_format",
		"formatIn" => "_in_format",
		"statusEqual" => "_eq_status",
		"statusIn" => "_in_status",
		"statusNotIn" => "_notin_status",
	);

	static private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), self::$order_by_map);
	}

	/**
	 * @var VidiunAttachmentType
	 */
	public $formatEqual;

	/**
	 * @dynamicType VidiunAttachmentType
	 * @var string
	 */
	public $formatIn;

	/**
	 * @var VidiunAttachmentAssetStatus
	 */
	public $statusEqual;

	/**
	 * @var string
	 */
	public $statusIn;

	/**
	 * @var string
	 */
	public $statusNotIn;
}
