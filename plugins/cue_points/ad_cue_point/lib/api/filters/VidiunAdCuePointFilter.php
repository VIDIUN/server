<?php
/**
 * @package plugins.adCuePoint
 * @subpackage api.filters
 */
class VidiunAdCuePointFilter extends VidiunAdCuePointBaseFilter
{
	static private $map_between_objects = array
	(
		"protocolTypeEqual" => "_eq_sub_type",
		"protocolTypeIn" => "_in_sub_type",
		"titleLike" => "_like_name",
		"titleMultiLikeOr" => "_mlikeor_name",
		"titleMultiLikeAnd" => "_mlikeand_name",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::validateForResponseProfile()
	 */
	public function validateForResponseProfile()
	{
		// override VidiunCuePointFilter::validateForResponseProfile because all ad cue-points are public
	}

	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		return parent::getTypeListResponse($pager, $responseProfile, AdCuePointPlugin::getCuePointTypeCoreValue(AdCuePointType::AD));
	}
}
