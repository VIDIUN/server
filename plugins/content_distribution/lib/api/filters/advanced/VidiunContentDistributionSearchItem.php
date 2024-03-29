<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.objects
 */
class VidiunContentDistributionSearchItem extends VidiunSearchItem
{
	/**
	 * @var bool
	 */
	public $noDistributionProfiles;
	
	/**
	 * @var int
	 */
	public $distributionProfileId;
	
	/**
	 * @var VidiunEntryDistributionSunStatus
	 */
	public $distributionSunStatus;
	
	/**
	 * @var VidiunEntryDistributionFlag
	 */
	public $entryDistributionFlag;
	
	/**
	 * @var VidiunEntryDistributionStatus
	 */
	public $entryDistributionStatus;
	
	/**
	 * @var bool
	 */
	public $hasEntryDistributionValidationErrors;
	
	/**
	 * Comma seperated validation error types
	 * @dynamicType VidiunDistributionErrorType
	 * @var string
	 */
	public $entryDistributionValidationErrors;

	private static $map_between_objects = array
	(
		'noDistributionProfiles',
		'distributionProfileId',
		'distributionSunStatus',
		'entryDistributionFlag',
		'entryDistributionStatus',
		'hasEntryDistributionValidationErrors',
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function toObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if(!$object_to_fill)
			$object_to_fill = new ContentDistributionSearchFilter();
			
		$object = parent::toObject($object_to_fill, $props_to_skip);
		if($this->entryDistributionValidationErrors)
			$object->setEntryDistributionValidationErrors(explode(',', $this->entryDistributionValidationErrors));
			
		return $object;
	}

	public function doFromObject($source_object, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($source_object, $responseProfile);
		
		if($this->shouldGet('entryDistributionValidationErrors', $responseProfile))
		{
			$entryDistributionValidationErrors = $source_object->getEntryDistributionValidationErrors();
			if(count($entryDistributionValidationErrors))
				$this->entryDistributionValidationErrors = implode(',', $entryDistributionValidationErrors);
		}
	}
}