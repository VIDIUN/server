<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.objects
 */
class VidiunGenericDistributionProviderAction extends VidiunObject implements IFilterable
{
	/**
	 * Auto generated
	 * 
	 * @readonly
	 * @var int
	 * @filter eq,in
	 */
	public $id;
	
	/**
	 * Generic distribution provider action creation date as Unix timestamp (In seconds)
	 * 
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $createdAt;

	/**
	 * Generic distribution provider action last update date as Unix timestamp (In seconds)
	 * 
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $updatedAt;

	/**
	 * @var int
	 * @insertonly
	 * @filter eq,in
	 */
	public $genericDistributionProviderId;

	/**
	 * @var VidiunDistributionAction
	 * @insertonly
	 * @filter eq,in
	 */
	public $action;

	/**
	 * @var VidiunGenericDistributionProviderStatus
	 * @readonly
	 */
	public $status;

	/**
	 * @var VidiunGenericDistributionProviderParser
	 */
	public $resultsParser;

	/**
	 * @var VidiunDistributionProtocol
	 */
	public $protocol;

	/**
	 * @var string
	 */
	public $serverAddress;

	/**
	 * @var string
	 */
	public $remotePath;

	/**
	 * @var string
	 */
	public $remoteUsername;

	/**
	 * @var string
	 */
	public $remotePassword;

	/**
	 * @var string
	 */
	public $editableFields;

	/**
	 * @var string
	 */
	public $mandatoryFields;

	/**
	 * @readonly
	 * @var string
	 */
	public $mrssTransformer;

	/**
	 * @readonly
	 * @var string
	 */
	public $mrssValidator;

	/**
	 * @readonly
	 * @var string
	 */
	public $resultsTransformer;

	/*
	 * mapping between the field on this object (on the left) and the setter/getter on the object (on the right)  
	 */
	private static $map_between_objects = array 
	(
		'id',
		'createdAt',
		'updatedAt',
		'genericDistributionProviderId',
		'action',
		'status',
		'resultsParser',
		'protocol',
		'serverAddress',
		'remotePath',
		'remoteUsername',
		'remotePassword',
		'editableFields',
		'mandatoryFields',
	);
		 
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function doFromObject($source_object, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($source_object, $responseProfile);

		if($this->shouldGet('mrssTransformer', $responseProfile))
		{
			$key = $source_object->getSyncKey(GenericDistributionProviderAction::FILE_SYNC_DISTRIBUTION_PROVIDER_ACTION_MRSS_TRANSFORMER);
			$this->mrssTransformer = vFileSyncUtils::file_get_contents($key, true, false);
		}
		
		if($this->shouldGet('mrssValidator', $responseProfile))
		{
			$key = $source_object->getSyncKey(GenericDistributionProviderAction::FILE_SYNC_DISTRIBUTION_PROVIDER_ACTION_MRSS_VALIDATOR);
			$this->mrssValidator = vFileSyncUtils::file_get_contents($key, true, false);
		}
			
		if($this->shouldGet('resultsTransformer', $responseProfile))
		{
			$key = $source_object->getSyncKey(GenericDistributionProviderAction::FILE_SYNC_DISTRIBUTION_PROVIDER_ACTION_RESULTS_TRANSFORMER);
			$this->resultsTransformer = vFileSyncUtils::file_get_contents($key, true, false);
		}
	}
	
	public function getExtraFilters()
	{
		return array(
		);
	}
	
	public function getFilterDocs()
	{
		return array(
		);
	}
}