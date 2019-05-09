<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunConvartableJobData extends VidiunJobData
{
	/**
	 * @var string
	 * @deprecated
	 */
	public $srcFileSyncLocalPath;
	
	/**
	 * The translated path as used by the scheduler
	 * @var string
	 * @deprecated
	 */
	public $actualSrcFileSyncLocalPath;
	
	/**
	 * @var string
	 * @deprecated
	 */
	public $srcFileSyncRemoteUrl;

	/**
	 * 
	 * @var VidiunSourceFileSyncDescriptorArray
	 */
	public $srcFileSyncs;
	
	/**
	 * @var int
	 */
	public $engineVersion;
	
	/**
	 * @var int
	 */
	public $flavorParamsOutputId;
	
	/**
	 * @var VidiunFlavorParamsOutput
	 */
	public $flavorParamsOutput;
	
	/**
	 * @var int
	 */
	public $mediaInfoId;
	
	/**
	 * @var int
	 */
	public $currentOperationSet;
	
	/**
	 * @var int
	 */
	public $currentOperationIndex;
	
	/**
	 * @var VidiunKeyValueArray
	 */
	public $pluginData;
	
	private static $map_between_objects = array
	(
		"srcFileSyncs",
		"engineVersion" ,
		"mediaInfoId" ,
		"flavorParamsOutputId" ,
		"currentOperationSet" ,
		"currentOperationIndex" ,
		"pluginData",
	);


	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	    
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject(  $dbConvartableJobData = null, $props_to_skip = array()) 
	{
		if(is_null($dbConvartableJobData))
			$dbConvartableJobData = new vConvartableJobData();
			
		return parent::toObject($dbConvartableJobData, $props_to_skip);
	}
	    
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject($srcObj)
	 */
	public function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null) 
	{
		/* @var $srcObj vConvartableJobData */
		$srcObj->migrateOldSerializedData();
		parent::doFromObject($srcObj, $responseProfile);
	}
}
