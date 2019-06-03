<?php
/**
 * @package api
 * @relatedService ConversionProfileService
 * @subpackage filters.base
 * @abstract
 */
abstract class VidiunConversionProfileBaseFilter extends VidiunRelatedFilter
{
	static private $map_between_objects = array
	(
		"idEqual" => "_eq_id",
		"idIn" => "_in_id",
		"statusEqual" => "_eq_status",
		"statusIn" => "_in_status",
		"typeEqual" => "_eq_type",
		"typeIn" => "_in_type",
		"nameEqual" => "_eq_name",
		"systemNameEqual" => "_eq_system_name",
		"systemNameIn" => "_in_system_name",
		"tagsMultiLikeOr" => "_mlikeor_tags",
		"tagsMultiLikeAnd" => "_mlikeand_tags",
		"defaultEntryIdEqual" => "_eq_default_entry_id",
		"defaultEntryIdIn" => "_in_default_entry_id",
	);

	static private $order_by_map = array
	(
		"+createdAt" => "+created_at",
		"-createdAt" => "-created_at",
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
	public $idEqual;

	/**
	 * @var string
	 */
	public $idIn;

	/**
	 * @var VidiunConversionProfileStatus
	 */
	public $statusEqual;

	/**
	 * @dynamicType VidiunConversionProfileStatus
	 * @var string
	 */
	public $statusIn;

	/**
	 * @var VidiunConversionProfileType
	 */
	public $typeEqual;

	/**
	 * @dynamicType VidiunConversionProfileType
	 * @var string
	 */
	public $typeIn;

	/**
	 * @var string
	 */
	public $nameEqual;

	/**
	 * @var string
	 */
	public $systemNameEqual;

	/**
	 * @var string
	 */
	public $systemNameIn;

	/**
	 * @var string
	 */
	public $tagsMultiLikeOr;

	/**
	 * @var string
	 */
	public $tagsMultiLikeAnd;

	/**
	 * @var string
	 */
	public $defaultEntryIdEqual;

	/**
	 * @var string
	 */
	public $defaultEntryIdIn;
}
