<?php
/**
 * @package plugins.playReady
 * @subpackage api.objects
 */
class VidiunPlayReadyPolicy extends VidiunDrmPolicy
{
    /**
	 * @var int
	 */
	public $gracePeriod;	
	
	/**
	 * @var VidiunPlayReadyLicenseRemovalPolicy
	 */
	public $licenseRemovalPolicy;	
	
	/**
	 * @var int
	 */
	public $licenseRemovalDuration;	
	
	/**
	 * @var VidiunPlayReadyMinimumLicenseSecurityLevel
	 */
	public $minSecurityLevel;	
	
	/**
	 * @var VidiunPlayReadyRightArray
	 */
	public $rights;	
	
	
	private static $map_between_objects = array(
		'gracePeriod',
		'licenseRemovalPolicy',
		'licenseRemovalDuration',
		'minSecurityLevel',
		'rights',
	 );
		 
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new PlayReadyPolicy();
			
		parent::toObject($dbObject, $skip);
					
		return $dbObject;
	}
	
	public function validatePolicy()
	{
		if(count($this->rights))
		{
			foreach ($this->rights as $right) 
			{
				if($right instanceof VidiunPlayReadyPlayRight)
				{
					$this->validatePlayRight($right);
				}
				else if($right instanceof VidiunPlayReadyCopyRight)
				{
					$this->validateCopyRight($right);
				}
			}
		}
		
		parent::validatePolicy();
	}
	
	private function validatePlayRight(VidiunPlayReadyPlayRight $right)
	{
		if(	count($right->analogVideoOutputProtectionList) && 
			in_array(VidiunPlayReadyAnalogVideoOPId::EXPLICIT_ANALOG_TV, $right->analogVideoOutputProtectionList) && 
			in_array(VidiunPlayReadyAnalogVideoOPId::BEST_EFFORT_EXPLICIT_ANALOG_TV, $right->analogVideoOutputProtectionList))
		{
			throw new VidiunAPIException(VidiunPlayReadyErrors::ANALOG_OUTPUT_PROTECTION_ID_NOT_ALLOWED, VidiunPlayReadyAnalogVideoOPId::EXPLICIT_ANALOG_TV, VidiunPlayReadyAnalogVideoOPId::BEST_EFFORT_EXPLICIT_ANALOG_TV);
		}
	}
	
	private function validateCopyRight(VidiunPlayReadyCopyRight $right)
	{
		if($right->copyCount > 0 && !count($right->copyEnablers))
		{
			throw new VidiunAPIException(VidiunPlayReadyErrors::COPY_ENABLER_TYPE_MISSING);
		}
	}
	
}

