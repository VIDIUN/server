<?php
/**
 * @package api
 * @relatedService PermissionItemService
 * @subpackage filters.base
 * @abstract
 */
abstract class VidiunPermissionItemBaseFilter extends VidiunRelatedFilter
{
	static private $map_between_objects = array
	(
		"idEqual" => "_eq_id",
		"idIn" => "_in_id",
		"typeEqual" => "_eq_type",
		"typeIn" => "_in_type",
		"partnerIdEqual" => "_eq_partner_id",
		"partnerIdIn" => "_in_partner_id",
		"tagsMultiLikeOr" => "_mlikeor_tags",
		"tagsMultiLikeAnd" => "_mlikeand_tags",
		"createdAtGreaterThanOrEqual" => "_gte_created_at",
		"createdAtLessThanOrEqual" => "_lte_created_at",
		"updatedAtGreaterThanOrEqual" => "_gte_updated_at",
		"updatedAtLessThanOrEqual" => "_lte_updated_at",
	);

	static private $order_by_map = array
	(
		"+id" => "+id",
		"-id" => "-id",
		"+createdAt" => "+created_at",
		"-createdAt" => "-created_at",
		"+updatedAt" => "+updated_at",
		"-updatedAt" => "-updated_at",
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
	 * @var VidiunPermissionItemType
	 */
	public $typeEqual;

	/**
	 * @var string
	 */
	public $typeIn;

	/**
	 * @var int
	 */
	public $partnerIdEqual;

	/**
	 * @var string
	 */
	public $partnerIdIn;

	/**
	 * @var string
	 */
	public $tagsMultiLikeOr;

	/**
	 * @var string
	 */
	public $tagsMultiLikeAnd;

	/**
	 * @var time
	 */
	public $createdAtGreaterThanOrEqual;

	/**
	 * @var time
	 */
	public $createdAtLessThanOrEqual;

	/**
	 * @var time
	 */
	public $updatedAtGreaterThanOrEqual;

	/**
	 * @var time
	 */
	public $updatedAtLessThanOrEqual;
}
