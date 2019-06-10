<?php
/**
 * @package plugins.captionSearch
 * @subpackage api.filters
 */
class VidiunCaptionAssetItemFilter extends VidiunCaptionAssetFilter
{
	static private $map_between_objects = array
	(
		"idEqual" => "_eq_caption_asset_id",
		"idIn" => "_in_caption_asset_id",
		"startTimeGreaterThanOrEqual" => "_gte_start_time",
		"startTimeLessThanOrEqual" => "_lte_start_time",
		"endTimeGreaterThanOrEqual" => "_gte_end_time",
		"endTimeLessThanOrEqual" => "_lte_end_time",
		"contentLike" => "_like_content",
		"contentMultiLikeOr" => "_mlikeor_content",
		"contentMultiLikeAnd" => "_mlikeand_content",
		"partnerDescriptionLike" => "_like_partner_description",
		"partnerDescriptionMultiLikeOr" => "_mlikeor_partner_description",
		"partnerDescriptionMultiLikeAnd" => "_mlikeand_partner_description",
		"languageEqual" => "_eq_language",
		"languageIn" => "_in_language",
		"labelEqual" => "_eq_label",
		"labelIn" => "_in_label",
	);

	static private $order_by_map = array
	(
		"+startTime" => "+start_time",
		"-startTime" => "-start_time",
		"+endTime" => "+end_time",
		"-endTime" => "-end_time",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), self::$order_by_map);
	}
	
	protected function validateEntryIdFiltered()
	{
		// do nothing, just overwrite parent validations
	}

	/* (non-PHPdoc)
	 * @see VidiunAssetFilter::getTypeListResponse()
	 */
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, array $types = null)
	{
		$captionItemQueryToFilter = new ESearchCaptionQueryFromFilter();

		$captionAssetItemFilter = new CaptionAssetItemFilter();
		$this->toObject($captionAssetItemFilter);

		$captionAssetItemCorePager = new vFilterPager();
		$pager->toObject($captionAssetItemCorePager);

		try
		{
			list($captionAssetItems, $objectsCount) = $captionItemQueryToFilter->retrieveElasticQueryCaptions($captionAssetItemFilter, $captionAssetItemCorePager, false);
		}
		catch (vESearchException $e)
		{
			elasticSearchUtils::handleSearchException($e);
		}

		$list = VidiunCaptionAssetItemArray::fromDbArray($captionAssetItems, $responseProfile);
		$response = new VidiunCaptionAssetItemListResponse();
		$response->objects = $list;
		$response->totalCount = $objectsCount;
		return $response;
	}
	
	/**
	 * @var string
	 */
	public $contentLike;

	/**
	 * @var string
	 */
	public $contentMultiLikeOr;

	/**
	 * @var string
	 */
	public $contentMultiLikeAnd;

	/**
	 * @var string
	 */
	public $partnerDescriptionLike;

	/**
	 * @var string
	 */
	public $partnerDescriptionMultiLikeOr;

	/**
	 * @var string
	 */
	public $partnerDescriptionMultiLikeAnd;

	/**
	 * @var VidiunLanguage
	 */
	public $languageEqual;

	/**
	 * @var string
	 */
	public $languageIn;

	/**
	 * @var string
	 */
	public $labelEqual;

	/**
	 * @var string
	 */
	public $labelIn;
	
	/**
	 * @var int
	 */
	public $startTimeGreaterThanOrEqual;

	/**
	 * @var int
	 */
	public $startTimeLessThanOrEqual;

	/**
	 * @var int
	 */
	public $endTimeGreaterThanOrEqual;

	/**
	 * @var int
	 */
	public $endTimeLessThanOrEqual;
}
