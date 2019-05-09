<?php
/**
 * @package plugins.tvinciDistribution
 * @subpackage api.objects
 */
class VidiunTvinciDistributionProfile extends VidiunConfigurableDistributionProfile
{
	/**
	 * @var string
	 */
	public $ingestUrl;
	
	/**
	 * @var string
	 */
	public $username;

	/**
	 * @var string
	 */
	public $password;

	/**
	 * Tags array for Tvinci distribution
	 * @var VidiunTvinciDistributionTagArray
	 */
	public $tags;

	/**
	 * @var string
	 */
	public $xsltFile;

	/**
	 * @var string
	 */
	public $innerType;

	/*
	 * mapping between the field on this object (on the left) and the setter/getter on the object (on the right)
	 */
	private static $map_between_objects = array 
	(
		'ingestUrl',
		'username',
		'password',
		'tags',
		'xsltFile',
		'innerType',
	 );
		 
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	/**
	 * @param TvinciDistributionProfile $srcObj
	 * @param VidiunDetachedResponseProfile $responseProfile
	 */
	protected function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($srcObj, $responseProfile);
		$this->tags = VidiunTvinciDistributionTagArray::fromDbArray($srcObj->getTags());
	}
}