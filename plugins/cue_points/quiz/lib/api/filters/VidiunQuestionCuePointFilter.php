<?php
/**
 * @package plugins.quiz
 * @subpackage api.filters
 */
class VidiunQuestionCuePointFilter extends VidiunQuestionCuePointBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::validateForResponseProfile()
	 */
	public function validateForResponseProfile()
	{
		// override VidiunCuePointFilter::validateForResponseProfile because all question cue-points are public
	}

	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		return parent::getTypeListResponse($pager, $responseProfile, QuizPlugin::getCoreValue('CuePointType',QuizCuePointType::QUIZ_QUESTION));
	}
}
