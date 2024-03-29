<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunDetachedResponseProfile extends VidiunBaseResponseProfile
{
	/**
	 * Friendly name
	 * 
	 * @var string
	 */
	public $name;
	
	/**
	 * @var VidiunResponseProfileType
	 */
	public $type;
	
	/**
	 * Comma separated fields list to be included or excluded
	 * 
	 * @var string
	 */
	public $fields;
	
	/**
	 * @var VidiunRelatedFilter
	 */
	public $filter;
	
	/**
	 * @var VidiunFilterPager
	 */
	public $pager;
	
	/**
	 * @var VidiunDetachedResponseProfileArray
	 */
	public $relatedProfiles;
	
	/**
	 * @var VidiunResponseProfileMappingArray
	 */
	public $mappings;
	
	private static $map_between_objects = array(
		'name', 
		'type',
		'fields',
		'pager',
		'relatedProfiles',
		'mappings',
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUsage($sourceObject, $propertiesToSkip)
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		$this->validatePropertyMinLength('name', 2, !is_null($sourceObject));
		
		// Allow null in case of update
		if(is_null($sourceObject))
		{
			$this->validatePropertyNotNull('type');
		}
		
		$this->validateNestedObjects();
	
		parent::validateForUsage($sourceObject, $propertiesToSkip);
	}
	
	public function validateNestedObjects($maxPageSize = null, $maxNestingLevel = null)
	{	
		if($this->filter)
		{
			$this->filter->validateForResponseProfile();
		}
		
		$relatedProfiles = $this->relatedProfiles;
		if(!$relatedProfiles)
		{
			return;
		}
	
		if(is_null($maxPageSize))
		{
			$maxPageSize = vConf::get('response_profile_max_page_size', 'local', 100);
		}
		
		if(is_null($maxNestingLevel))
		{
			$maxNestingLevel = vConf::get('response_profile_max_nesting_level', 'local', 2);
		}
		
		if($maxNestingLevel > 0)
		{
			foreach($relatedProfiles as $relatedProfile)
			{
				/* @var $relatedProfile VidiunDetachedResponseProfile */
				$relatedProfile->validateNestedObjects($maxPageSize, $maxNestingLevel - 1);
				
				$pager = $relatedProfile->pager;
				if($pager)
				{
					$pager->validatePropertyMaxValue('pageSize', $maxPageSize, true);
				}
			}
		}
		elseif(count($relatedProfiles))
		{
			throw new VidiunAPIException(VidiunErrors::RESPONSE_PROFILE_MAX_NESTING_LEVEL);
		}
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject($srcObj, $responseProfile)
	 */
	public function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $srcObj vResponseProfile */
		parent::doFromObject($srcObj, $responseProfile);
		
		if($srcObj->getFilter() && $this->shouldGet('filter', $responseProfile))
		{
			$filterApiClassName = $srcObj->getFilterApiClassName();
			$this->filter = new $filterApiClassName();
			$this->filter->fromObject($srcObj->getFilter());
		}
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($object = null, $propertiesToSkip = array())
	{
		if(!$object)
		{
			$object = new vResponseProfile();
		}
		
		if($this->filter)
		{
			$object->setFilterApiClassName(get_class($this->filter));
			$object->setFilter($this->filter->toObject());
		}
		
		return parent::toObject($object, $propertiesToSkip);
	}
	
	/**
	 * Return unique identifier to be used in cache
	 * @return string
	 */
	public function getKey()
	{
		return md5(serialize($this));
	}
}