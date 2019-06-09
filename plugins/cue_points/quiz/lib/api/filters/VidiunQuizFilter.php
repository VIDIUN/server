<?php
/**
 * @package plugins.quiz
 * @subpackage api.filters
 */

class VidiunQuizFilter extends VidiunRelatedFilter {

	static private $map_between_objects = array
	(
		"entryIdEqual" => "_eq_id",
		"entryIdIn" => "_in_id",
	);

	/**
	 * This filter should be in use for retrieving only a specific quiz entry (identified by its entryId).
	 *
	 * @var string
	 */
	public $entryIdEqual;

	/**
	 * This filter should be in use for retrieving few specific quiz entries (string should include comma separated list of entryId strings).
	 *
	 * @var string
	 */
	public $entryIdIn;

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null) {
		$entryFilter = new QuizEntryFilter();
		$entryFilter->setPartnerSearchScope(baseObjectFilter::MATCH_VIDIUN_NETWORK_AND_PRIVATE);
		$this->toObject($entryFilter);

		$c = VidiunCriteria::create(entryPeer::OM_CLASS);
		if($pager)
			$pager->attachToCriteria($c);

		$entryFilter->attachToCriteria($c);
		$list = entryPeer::doSelect($c);

		$response = new VidiunQuizListResponse();
		$response->objects = VidiunQuizArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $c->getRecordsCount();

		return $response;
	}

	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter() {
		return new QuizEntryFilter();
	}

}