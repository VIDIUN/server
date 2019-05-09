<?php
/**
 * @package plugins.externalMedia
 * @relatedService BaseEntryService
 * @subpackage api.filters.base
 * @abstract
 */
abstract class VidiunExternalMediaEntryBaseFilter extends VidiunMediaEntryFilter
{
	static private $map_between_objects = array
	(
		"externalSourceTypeEqual" => "_eq_external_source_type",
		"externalSourceTypeIn" => "_in_external_source_type",
		"assetParamsIdsMatchOr" => "_matchor_asset_params_ids",
		"assetParamsIdsMatchAnd" => "_matchand_asset_params_ids",
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
	 * @var VidiunExternalMediaSourceType
	 */
	public $externalSourceTypeEqual;

	/**
	 * @dynamicType VidiunExternalMediaSourceType
	 * @var string
	 */
	public $externalSourceTypeIn;

	/**
	 * @var string
	 */
	public $assetParamsIdsMatchOr;

	/**
	 * @var string
	 */
	public $assetParamsIdsMatchAnd;
}
