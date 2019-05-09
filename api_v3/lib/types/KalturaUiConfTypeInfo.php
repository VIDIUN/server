<?php
/**
 * Info about uiconf type
 * 
 * @see VidiunStringArray
 * @package api
 * @subpackage objects
 */
class VidiunUiConfTypeInfo extends VidiunObject
{
	/**
	 * UiConf Type
	 * 
	 * @var VidiunUiConfObjType
	 */
    public $type;
    
    /**
     * Available versions
     *  
     * @var VidiunStringArray
     */
    public $versions;
    
    /**
     * The direcotry this type is saved at
     * 
     * @var string
     */
    public $directory;
    
    /**
     * Filename for this UiConf type
     * 
     * @var string
     */
    public $filename;
    
	private static $mapBetweenObjects = array
	(
		"type",
		"versions",
		"directory",
		"filename",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
}