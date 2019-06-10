<?php
/**
 * @package plugins.caption
 * @relatedService CaptionAssetService
 * @subpackage api.filters.base
 * @abstract
 */
abstract class VidiunCaptionAssetBaseFilter extends VidiunAssetFilter
{
	static private $map_between_objects = array
	(
		"captionParamsIdEqual" => "_eq_caption_params_id",
		"captionParamsIdIn" => "_in_caption_params_id",
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
	 * @var int
	 */
	public $captionParamsIdEqual;

	/**
	 * @var string
	 */
	public $captionParamsIdIn;

	/**
	 * @var VidiunCaptionType
	 */
	public $formatEqual;

	/**
	 * @dynamicType VidiunCaptionType
	 * @var string
	 */
	public $formatIn;

	/**
	 * @var VidiunCaptionAssetStatus
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
