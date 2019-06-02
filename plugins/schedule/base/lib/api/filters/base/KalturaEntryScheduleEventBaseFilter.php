<?php
/**
 * @package plugins.schedule
 * @relatedService ScheduleEventService
 * @subpackage api.filters.base
 * @abstract
 */
abstract class VidiunEntryScheduleEventBaseFilter extends VidiunScheduleEventFilter
{
	static private $map_between_objects = array
	(
		"templateEntryIdEqual" => "_eq_template_entry_id",
		"entryIdsLike" => "_like_entry_ids",
		"entryIdsMultiLikeOr" => "_mlikeor_entry_ids",
		"entryIdsMultiLikeAnd" => "_mlikeand_entry_ids",
		"categoryIdsLike" => "_like_category_ids",
		"categoryIdsMultiLikeOr" => "_mlikeor_category_ids",
		"categoryIdsMultiLikeAnd" => "_mlikeand_category_ids",
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
	public $templateEntryIdEqual;

	/**
	 * @var string
	 */
	public $entryIdsLike;

	/**
	 * @var string
	 */
	public $entryIdsMultiLikeOr;

	/**
	 * @var string
	 */
	public $entryIdsMultiLikeAnd;

	/**
	 * @var string
	 */
	public $categoryIdsLike;

	/**
	 * @var string
	 */
	public $categoryIdsMultiLikeOr;

	/**
	 * @var string
	 */
	public $categoryIdsMultiLikeAnd;
}
