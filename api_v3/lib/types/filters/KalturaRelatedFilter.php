<?php
/**
 * @package api
 * @subpackage filters
 */
abstract class VidiunRelatedFilter extends VidiunFilter
{
	/**
	 * @param VidiunFilterPager $pager
	 * @param VidiunDetachedResponseProfile $responseProfile
	 * @return VidiunListResponse
	 */
	abstract public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null);
	
	public function validateForResponseProfile()
	{
		
	}

	/**
	 * @param VidiunFilterPager $pager
	 * @param VidiunDetachedResponseProfile|null $responseProfile
	 * @return VidiunListResponse
	 * @throws Exception
	 */
	public function validateAndGetListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{

		if (ValidateAccessResponseProfile::validateAccess($this))
			return $this->getListResponse($pager,$responseProfile);
		return new VidiunListResponse();

	}

}
