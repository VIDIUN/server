<?php
/**
 * @package plugins.drm
 * @subpackage api.objects
 */
class VidiunDrmProfile extends VidiunObject implements IFilterable
{	
	/**
	 * @var int
	 * @readonly
	 * @filter eq,in,order
	 */
	public $id;
	
	/**
	 * @var int
	 * @insertonly
	 * @filter eq,in
	 */
	public $partnerId;
	
	/**
	 * @var string
	 * @filter like,order
	 */
	public $name;
			
	/**
	 * @var string
	 */
	public $description;
	
	/**
	 * @var VidiunDrmProviderType
	 * @filter eq,in
	 */
	public $provider;
	
	/**
	 * @var VidiunDrmProfileStatus
	 * @filter eq,in
	 */
	public $status;
	
	/**
	 * @var string
	 */
	public $licenseServerUrl;
	
	/**
	 * @var string
	 */
	public $defaultPolicy;
	
	/**
	 * @var int
	 * @readonly
	 */
	public $createdAt;

	/**
	 * @var int
	 * @readonly
	 */
	public $updatedAt;

    /**
     * @var string
     */
    public $signingKey;

	/*
	 * mapping between the field on this object (on the left) and the setter/getter on the entry object (on the right)  
	 */
	private static $map_between_objects = array(
		'id',
		'partnerId',
		'name',
		'description',
		'provider',
		'status',
		'licenseServerUrl',
		'defaultPolicy',
		'createdAt',
		'updatedAt',
		'signingKey',
	 );
		 
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new DrmProfile();
		parent::toObject($dbObject, $skip);		
		return $dbObject;
	}
		
	/**
	 * @param int $type
	 * @return VidiunDrmProfile
	 */
	static function getInstanceByType ($provider)
	{
		if ($provider == VidiunDrmProviderType::CENC)
        {
            return new VidiunDrmProfile();
        }
        $obj = VidiunPluginManager::loadObject('VidiunDrmProfile', $provider);
		return $obj;
	}

	public function getExtraFilters()
	{
		return array();
	} 
	
	public function getFilterDocs()
	{
		return null;
	}
	
}