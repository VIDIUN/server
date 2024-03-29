<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunReportInputFilter extends VidiunReportInputBaseFilter 
{
	/**
	 * Search keywords to filter objects
	 * 
	 * @var string
	 */
	public $keywords;
	
	/**
	 * Search keywords in objects tags
	 * 
	 * @var bool
	 */
	public $searchInTags;	

	/**
	 * Search keywords in objects admin tags
	 * 
	 * @var bool
	 * @deprecated
	 */
	public $searchInAdminTags;	
	
	/**
	 * Search objects in specified categories
	 * 
	 * @var string
	 */
	public $categories;

	/**
	 * Search objects in specified category ids
	 *
	 * @var string
	 */
	public $categoriesIdsIn;

	/**
	 * Filter by customVar1
	 *
	 * @var string
	 */
	public $customVar1In;

	/**
	 * Filter by customVar2
	 *
	 * @var string
	 */
	public $customVar2In;

	/**
	 * Filter by customVar3
	 *
	 * @var string
	 */
	public $customVar3In;

	/**
	 * Filter by device
	 *
	 * @var string
	 */
	public $deviceIn;

	/**
	 * Filter by country
	 *
	 * @var string
	 */
	public $countryIn;

	/**
	 * Filter by region
	 *
	 * @var string
	 */
	public $regionIn;

	/**
	 * Filter by city
	 *
	 * @var string
	 */
	public $citiesIn;

	/**
	 * Filter by operating system family
	 *
	 * @var string
	 */
	public $operatingSystemFamilyIn;

	/**
	 * Filter by browser family
	 *
	 * @var string
	 */
	public $browserFamilyIn;

	/**
	 * Time zone offset in minutes
	 * 
	 * @var int
	 */
	public $timeZoneOffset = 0;
	
	/**
	 * Aggregated results according to interval
	 * 
	 * @var VidiunReportInterval
	 */
	public $interval;

	/**
	 * Filter by media types
	 *
	 * @var string
	 */
	public $mediaTypeIn;

	/**
	 * Filter by source types
	 *
	 * @var string
	 */
	public $sourceTypeIn;

	/**
	 * Filter by entry owner
	 *
	 * @var string
	 */
	public $ownerIdsIn;

	/**
	 * @var VidiunESearchEntryOperator
	 */
	public $entryOperator;

	/**
	 * Entry created at greater than or equal as Unix timestamp
	 * @var time
	 */
	public $entryCreatedAtGreaterThanOrEqual;

	/**
	 * Entry created at less than or equal as Unix timestamp
	 * @var time
	 */
	public $entryCreatedAtLessThanOrEqual;

	/**
	 * @var string
	 */
	public $entryIdIn;

	private static $map_between_objects = array
	(
		'keywords',
		'searchInTags' => 'search_in_tags',
		'searchInAdminTags' => 'search_in_admin_tags',
		'categories',
		'categoriesIdsIn' => 'categoriesIds',
		'customVar1In' => 'custom_var1',
		'customVar2In' => 'custom_var2',
		'customVar3In' => 'custom_var3',
		'deviceIn' => 'devices',
		'countryIn' => 'countries',
		'regionIn' => 'regions',
		'citiesIn' => 'cities',
		'operatingSystemFamilyIn' => 'os_families',
		'browserFamilyIn' => 'browsers_families',
		'timeZoneOffset',
		'interval',
		'mediaTypeIn' => 'media_types',
		'sourceTypeIn' => 'source_types',
		'ownerIdsIn' => 'owners',
		'entryCreatedAtGreaterThanOrEqual' => 'gte_entry_created_at',
		'entryCreatedAtLessThanOrEqual' => 'lte_entry_created_at',
		'entryIdIn' => 'entries_ids',
	);

	protected function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	/* (non-PHPdoc)
	 * @see VidiunReportInputBaseFilter::toReportsInputFilter()
	 */
	public function toReportsInputFilter($reportInputFilter = null)
	{
		if (!$reportInputFilter)
		{
			$reportInputFilter = new reportsInputFilter();
		}

		if ($this->entryOperator)
		{
			$reportInputFilter->entry_operator = $this->entryOperator->toObject();
		}

		return parent::toReportsInputFilter($reportInputFilter);
	}
}
