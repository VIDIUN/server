<?php
/**
 * @package plugins.systemPartner
 * @subpackage api.filters
 */
class VidiunSystemPartnerFilter extends VidiunPartnerFilter
{
	static private $map_between_objects = array
	(
		"partnerParentIdEqual" => "_eq_partner_parent_id",
		"partnerParentIdIn" => "_in_partner_parent_id",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/**
	 * @var int
	 * @requiresPermission write
	 */
	public $partnerParentIdEqual;

	/**
	 * @var string
	 * @requiresPermission all
	 */
	public $partnerParentIdIn;
}