<?php
/**
 * @package plugins.tagSearch
 * @subpackage api.objects
 */
class VidiunTag extends VidiunObject
{
    /**
     * @var int
     * @readonly
     */
    public $id;
    
    /**
     * @var string
     * @readonly
     */
    public $tag;
    
    /**
     * @var VidiunTaggedObjectType
     * @readonly
     */
    public $taggedObjectType;
    
    /**
     * @var int
     * @readonly
     */
    public $partnerId;
    
    /**
     * @var int
     * @readonly
     */
    public $instanceCount;
    
    /**
     * @var time
     * @readonly
     */
    public $createdAt;
    
    /**
     * @var time
     * @readonly
     */
    public $updatedAt;
    
    private static $map_between_objects = array
	(
		"id",
	    "tag",
	    "taggedObjectType" => "objectType",
	    "partnerId",
	    "instanceCount",
	    "createdAt",
		"updatedAt",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
    
}