<?php
/**
 * @package api
 * @subpackage objects
 * @relatedService ConversionProfileAssetParamsService
 */
class VidiunConversionProfileAssetParams extends VidiunObject implements IRelatedFilterable 
{
	/**
	 * The id of the conversion profile
	 * 
	 * @var int
	 * @readonly
	 * @filter eq,in
	 */
	public $conversionProfileId;
	
	/**
	 * The id of the asset params
	 * 
	 * @var int
	 * @readonly
	 * @filter eq,in
	 */
	public $assetParamsId;

	/**
	 * The ingestion origin of the asset params
	 *  
	 * @var VidiunFlavorReadyBehaviorType
	 * @filter eq,in
	 */
	public $readyBehavior;

	/**
	 * The ingestion origin of the asset params
	 *  
	 * @var VidiunAssetParamsOrigin
	 * @filter eq,in
	 */
	public $origin;

	/**
	 * Asset params system name
	 *  
	 * @var string
	 * @filter eq,in
	 */
	public $systemName;
	
	/**
	 * Starts conversion even if the decision layer reduced the configuration to comply with the source
	 * @var VidiunNullableBoolean
	 */
	public $forceNoneComplied;
	
	/**
	 * 
	 * Specifies how to treat the flavor after conversion is finished
	 * @var VidiunAssetParamsDeletePolicy
	 */
	public $deletePolicy;
	
	/**
	 * @var VidiunNullableBoolean
	 */
	public $isEncrypted;

	/**
	 * @var float
	 */
	public $contentAwareness;
	
	/**
	 * @var int
	 */
	public $chunkedEncodeMode;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $twoPass;

        /**
         * @var string
         */
        public $tags;

	/**
	 * JSON string containing an array of flavotParams field-value pairs.
	 * @var string
	 */
	public $overloadParams;
	
	private static $map_between_objects = array
	(
		'conversionProfileId',
		'assetParamsId' => 'flavorParamsId',
		'readyBehavior',
		'origin',
		'systemName',
		'forceNoneComplied',
		'deletePolicy',
		'isEncrypted',
		'contentAwareness',
		'chunkedEncodeMode',
		'twoPass',
		'tags',
		'overloadParams',
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getExtraFilters()
	 */
	public function getExtraFilters()
	{
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getFilterDocs()
	 */
	public function getFilterDocs()
	{
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUpdate($sourceObject, $propertiesToSkip)
	 */
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		/* @var $sourceObject flavorParamsConversionProfile */
		$assetParams = $sourceObject->getassetParams();
		if(!$assetParams)
			throw new VidiunAPIException(VidiunErrors::ASSET_ID_NOT_FOUND, $sourceObject->getFlavorParamsId());
			
		if($assetParams instanceof liveParams && $this->origin == VidiunAssetParamsOrigin::CONVERT_WHEN_MISSING)
			throw new VidiunAPIException(VidiunErrors::LIVE_PARAMS_ORIGIN_NOT_SUPPORTED, $sourceObject->getFlavorParamsId(), $assetParams->getType(), $this->origin);
	}
}
