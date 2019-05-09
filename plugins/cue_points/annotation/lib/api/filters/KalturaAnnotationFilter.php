<?php
/**
 * @package plugins.annotation
 * @subpackage api.filters
 */
class VidiunAnnotationFilter extends VidiunAnnotationBaseFilter
{
	const CHAPTERS_PUBLIC_TAG = 'chaptering';
	
	/* (non-PHPdoc)
 	 * @see VidiunFilter::getCoreFilter()
 	 */
	protected function getCoreFilter()
	{
		return new AnnotationFilter();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::validateForResponseProfile()
	 */
	public function validateForResponseProfile()
	{
		if(		!vCurrentContext::$is_admin_session
			&&	!$this->isPublicEqual)
		{
			parent::validateForResponseProfile();
		}
	}

	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		return parent::getTypeListResponse($pager, $responseProfile, AnnotationPlugin::getCuePointTypeCoreValue(AnnotationCuePointType::ANNOTATION));
	}
}
