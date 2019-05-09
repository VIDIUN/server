<?php
/**
 * @package plugins.codeCuePoint
 * @subpackage api.filters
 */
class VidiunCodeCuePointFilter extends VidiunCodeCuePointBaseFilter
{
	static private $map_between_objects = array
	(
		"codeLike" => "_like_name",
		"codeMultiLikeOr" => "_mlikeor_name",
		"codeMultiLikeAnd" => "_mlikeand_name",
		"codeEqual" => "_eq_name",
		"codeIn" => "_in_name",
		"descriptionLike" => "_like_text",
		"descriptionMultiLikeOr" => "_mlikeor_text",
		"descriptionMultiLikeAnd" => "_mlikeand_text",
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
		// override VidiunCuePointFilter::validateForResponseProfile because all code cue-points are public
	}

	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		return parent::getTypeListResponse($pager, $responseProfile, CodeCuePointPlugin::getCuePointTypeCoreValue(CodeCuePointType::CODE));
	}
}
