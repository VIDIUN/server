<?php
/**
 * @package plugins.quiz
 * @subpackage api.filters
 */
class VidiunAnswerCuePointFilter extends VidiunAnswerCuePointBaseFilter
{
    /* (non-PHPdoc)
     * @see VidiunCuePointFilter::getCriteria()
     */
    protected function getCriteria()
    {
        return VidiunCriteria::create('AnswerCuePoint');
    }
    
	/* (non-PHPdoc)
	 * @see VidiunCuePointFilter::getTypeListResponse()
	 */
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		if ($this->quizUserEntryIdIn || $this->quizUserEntryIdEqual)
		{
			VidiunCriterion::disableTag(VidiunCriterion::TAG_WIDGET_SESSION);
		}
		return parent::getTypeListResponse($pager, $responseProfile, QuizPlugin::getCoreValue('CuePointType',QuizCuePointType::QUIZ_ANSWER));
	}
	
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		vApiCache::disableCache();
		return new AnswerCuePointFilter();
	}	
}
