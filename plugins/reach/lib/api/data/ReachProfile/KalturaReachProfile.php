<?php

/**
 * @package plugins.reach
 * @subpackage api.objects
 * @relatedService ignore
 */
class VidiunReachProfile extends VidiunObject implements IRelatedFilterable
{
	const MAX_DICTIONARY_LENGTH = 1000;
	
	/**
	 * @var int
	 * @readonly
	 * @filter eq,in,order
	 */
	public $id;
	
	/**
	 * The name of the profile
	 * @var string
	 */
	public $name;
	
	/**
	 * @var int
	 * @readonly
	 */
	public $partnerId;
	
	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $createdAt;
	
	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $updatedAt;
	
	/**
	 * @var VidiunReachProfileStatus
	 * @readonly
	 * @filter eq,in
	 */
	public $status;
	
	/**
	 * @var VidiunReachProfileType
	 * @filter eq,in
	 */
	public $profileType;
	
	/**
	 * @var VidiunVendorCatalogItemOutputFormat
	 */
	public $defaultOutputFormat;
	
	/**
	 * @var VidiunNullableBoolean
	 */
	public $enableMachineModeration;
	
	/**
	 * @var VidiunNullableBoolean
	 */
	public $enableHumanModeration;
	
	/**
	 * @var VidiunNullableBoolean
	 */
	public $autoDisplayMachineCaptionsOnPlayer;
	
	/**
	 * @var VidiunNullableBoolean
	 */
	public $autoDisplayHumanCaptionsOnPlayer;
	
	/**
	 * @var VidiunNullableBoolean
	 */
	public $enableMetadataExtraction;
	
	/**
	 * @var VidiunNullableBoolean
	 */
	public $enableSpeakerChangeIndication;
	
	/**
	 * @var VidiunNullableBoolean
	 */
	public $enableAudioTags;
	
	/**
	 * @var VidiunNullableBoolean
	 */
	public $enableProfanityRemoval;
	
	/**
	 * @var int
	 */
	public $maxCharactersPerCaptionLine;
	
	/**
	 * @var VidiunReachProfileContentDeletionPolicy
	 */
	public $contentDeletionPolicy;
	
	/**
	 * @var VidiunRuleArray
	 */
	public $rules;
	
	/**
	 * @var VidiunBaseVendorCredit
	 * @requiresPermission update
	 */
	public $credit;
	
	/**
	 * @var float
	 * @readonly
	 */
	public $usedCredit;
	
	/**
	 * @var VidiunDictionaryArray
	 */
	public $dictionaries;
	
	/**
	 * Comma separated flavorParamsIds that the vendor should look for it matching asset when trying to download the asset
	 * @var string
	 */
	public $flavorParamsIds;
	
	/**
	 * Indicates in which region the task processing should task place
	 * @var VidiunVendorTaskProcessingRegion
	 */
	public $vendorTaskProcessingRegion;
	
	private static $map_between_objects = array
	(
		'id',
		'name',
		'partnerId',
		'createdAt',
		'updatedAt',
		'status',
		'profileType' => 'type',
		'defaultOutputFormat',
		'enableMachineModeration',
		'enableHumanModeration',
		'autoDisplayMachineCaptionsOnPlayer',
		'autoDisplayHumanCaptionsOnPlayer',
		'enableMetadataExtraction',
		'enableSpeakerChangeIndication',
		'enableAudioTags',
		'enableProfanityRemoval',
		'maxCharactersPerCaptionLine',
		'contentDeletionPolicy',
		'rules' => 'rulesArray',
		'credit',
		'usedCredit',
		'dictionaries' => 'dictionariesArray',
		'flavorParamsIds',
		'vendorTaskProcessingRegion'
	);
	
	/* (non-PHPdoc)
	 * @see VidiunCuePoint::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
 	 * @see VidiunObject::toInsertableObject()
 	 */
	public function toInsertableObject($object_to_fill = null, $props_to_skip = array())
	{
		if (is_null($object_to_fill))
			$object_to_fill = new ReachProfile();
		
		return parent::toInsertableObject($object_to_fill, $props_to_skip);
	}
	
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyNotNull("profileType");
		$this->validatePropertyNotNull("credit");
		$this->credit->validateForInsert();
		
		//validating dictionary duplications
		$this->validateDictionary();
		
		return parent::validateForInsert($propertiesToSkip);
	}
	
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		//validating dictionary duplications
		$this->validateDictionary();
		
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
	
	private function validateDictionaryLength($data)
	{
		return strlen($data) <= self::MAX_DICTIONARY_LENGTH ? true : false;
	}
	
	public function getExtraFilters()
	{
		return array();
	}
	
	public function getFilterDocs()
	{
		return array();
	}
	
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $dbObject ReachProfile */
		parent::doFromObject($dbObject, $responseProfile);
		
		if ($this->shouldGet('credit', $responseProfile) && !is_null($dbObject->getCredit()))
		{
			$this->credit = VidiunBaseVendorCredit::getInstance($dbObject->getCredit(), $responseProfile);
		}
	}
	
	private function validateDictionary()
	{
		if(!$this->dictionaries)
			return;
		
		$languages = array();
		foreach ($this->dictionaries as $dictionary)
		{
			/* @var VidiunDictionary $dictionary */
			if (in_array($dictionary->language, $languages))
				throw new VidiunAPIException(VidiunReachErrors::DICTIONARY_LANGUAGE_DUPLICATION, $dictionary->language);
			
			if (!$this->validateDictionaryLength($dictionary->data))
				throw new VidiunAPIException(VidiunReachErrors::MAX_DICTIONARY_LENGTH_EXCEEDED, $dictionary->language, self::MAX_DICTIONARY_LENGTH);
			
			$languages[] = $dictionary->language;
		}
	}
}
