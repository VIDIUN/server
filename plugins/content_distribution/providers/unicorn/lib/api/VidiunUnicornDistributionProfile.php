<?php
/**
 * @package plugins.unicornDistribution
 * @subpackage api.objects
 */
class VidiunUnicornDistributionProfile extends VidiunConfigurableDistributionProfile
{
	/**
	 * The email address associated with the Upload User, used to authorize the incoming request.
	 * 
	 * @var string
	 */
	public $username;
	
	/**
	 * The password used in association with the email to determine if the Upload User is authorized the incoming request.
	 * 
	 * @var string
	 */
	public $password;
	
	/**
	 * The name of the Domain that the Upload User should have access to, Used for authentication.
	 * 
	 * @var string
	 */
	public $domainName;
	
	/**
	 * The Channel GUID assigned to this Publication Rule. Must be a valid Channel in the Domain that was used in authentication.
	 * 
	 * @var string
	 */
	public $channelGuid;
	
	/**
	 * The API host URL that the Upload User should have access to, Used for HTTP content submission.
	 * 
	 * @var string
	 */
	public $apiHostUrl;
	
	/**
	 * The GUID of the Customer Domain in the Unicorn system obtained by contacting your Unicorn representative.
	 * 
	 * @var string
	 */
	public $domainGuid;
	
	/**
	 * The GUID for the application in which to record metrics and enforce business rules obtained through your Unicorn representative.
	 * 
	 * @var string
	 */
	public $adFreeApplicationGuid;
	
	/**
	 * The flavor-params that will be used for the remote asset.
	 * 
	 * @var int
	 */
	public $remoteAssetParamsId;
	
	/**
	 * The remote storage that should be used for the remote asset.
	 * 
	 * @var string
	 */
	public $storageProfileId;
	
	/*
	 * mapping between the field on this object (on the left) and the setter/getter on the object (on the right)  
	 */
	private static $map_between_objects = array(
		'username', 
		'password', 
		'domainName', 
		'channelGuid', 
		'apiHostUrl',
		'domainGuid',
		'adFreeApplicationGuid',
		'remoteAssetParamsId',
		'storageProfileId',
	);
	
	/* (non-PHPdoc)
	 * @see VidiunDistributionProfile::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
}