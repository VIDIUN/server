<?php
/**
 * @package plugins.quiz
 * @subpackage api.filters
 */
class VidiunQuizUserEntryFilter extends VidiunQuizUserEntryBaseFilter
{

	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$this->typeEqual = QuizPlugin::getApiValue(QuizUserEntryType::QUIZ);
		UserEntryPeer::setDefaultCriteriaOrderBy(UserEntryPeer::ID);
		$response = parent::getListResponse($pager, $responseProfile);
		return $response;
	}
}
