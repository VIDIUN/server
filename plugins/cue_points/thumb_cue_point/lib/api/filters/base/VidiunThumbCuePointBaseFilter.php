<?php
/**
 * @package plugins.thumbCuePoint
 * @relatedService CuePointService
 * @subpackage api.filters.base
 * @abstract
 */
abstract class VidiunThumbCuePointBaseFilter extends VidiunCuePointFilter
{
	static private $map_between_objects = array
	(
		"descriptionLike" => "_like_description",
		"descriptionMultiLikeOr" => "_mlikeor_description",
		"descriptionMultiLikeAnd" => "_mlikeand_description",
		"titleLike" => "_like_title",
		"titleMultiLikeOr" => "_mlikeor_title",
		"titleMultiLikeAnd" => "_mlikeand_title",
		"subTypeEqual" => "_eq_sub_type",
		"subTypeIn" => "_in_sub_type",
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
	 * @var string
	 */
	public $descriptionLike;

	/**
	 * @var string
	 */
	public $descriptionMultiLikeOr;

	/**
	 * @var string
	 */
	public $descriptionMultiLikeAnd;

	/**
	 * @var string
	 */
	public $titleLike;

	/**
	 * @var string
	 */
	public $titleMultiLikeOr;

	/**
	 * @var string
	 */
	public $titleMultiLikeAnd;

	/**
	 * @var VidiunThumbCuePointSubType
	 */
	public $subTypeEqual;

	/**
	 * @var string
	 */
	public $subTypeIn;
}
