<?php
/**
 * @package api
 * @relatedService ConversionProfileAssetParamsService
 * @subpackage filters.base
 * @abstract
 */
abstract class VidiunConversionProfileAssetParamsBaseFilter extends VidiunRelatedFilter
{
	static private $map_between_objects = array
	(
		"conversionProfileIdEqual" => "_eq_conversion_profile_id",
		"conversionProfileIdIn" => "_in_conversion_profile_id",
		"assetParamsIdEqual" => "_eq_asset_params_id",
		"assetParamsIdIn" => "_in_asset_params_id",
		"readyBehaviorEqual" => "_eq_ready_behavior",
		"readyBehaviorIn" => "_in_ready_behavior",
		"originEqual" => "_eq_origin",
		"originIn" => "_in_origin",
		"systemNameEqual" => "_eq_system_name",
		"systemNameIn" => "_in_system_name",
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
	public $conversionProfileIdEqual;

	/**
	 * @var string
	 */
	public $conversionProfileIdIn;

	/**
	 * @var int
	 */
	public $assetParamsIdEqual;

	/**
	 * @var string
	 */
	public $assetParamsIdIn;

	/**
	 * @var VidiunFlavorReadyBehaviorType
	 */
	public $readyBehaviorEqual;

	/**
	 * @var string
	 */
	public $readyBehaviorIn;

	/**
	 * @var VidiunAssetParamsOrigin
	 */
	public $originEqual;

	/**
	 * @var string
	 */
	public $originIn;

	/**
	 * @var string
	 */
	public $systemNameEqual;

	/**
	 * @var string
	 */
	public $systemNameIn;
}
