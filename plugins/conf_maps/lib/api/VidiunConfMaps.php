<?php
/**
 * @package plugins.confMaps
 * @subpackage api.objects
 * @relatedService ConfMapsService
 */

class VidiunConfMaps extends VidiunObject implements IRelatedFilterable
{
	/**
	 * Name of the map
	 *
	 * @var string
	 * @insertonly
	 * @filter eq
	 */
	public $name;

	/**
	 * Ini file content
	 *
	 * @var string
	 */
	public $content;

	/**
	 * IsEditable - true / false
	 *
	 * @var bool
	 * @readonly
	 */
	public $isEditable;

	/**
	 * Time of the last update
	 *
	 * @var time
	 * @readonly
	 */
	public $lastUpdate;

	/**
	 * Regex that represent the host/s that this map affect
	 *
	 * @var string
	 * @filter eq
	 */
	public $relatedHost;

	/**
	 * @var int
	 * @readonly
	 * @filter eq
	 */
	public $version;

	/**
	 * @var VidiunConfMapsSourceLocation
	 * @insertonly
	 */
	public $sourceLocation;

	/**
	 * @var string
	 * @insertonly
	 */
	public $remarks;

	/**
	 * map status
	 *
	 * @var VidiunConfMapsStatus
	 * @filter eq
	 */
	public $status;



	private static $map_between_objects = array
	(
		"name" => "mapName",
		"relatedHost" => "hostName",
		"status",
		"version",
		"lastUpdate" => "createdAt",
		"remarks",
		"content"
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function validateForInsert($propertiesToSkip = array())
	{
		if(strstr($this->relatedHost,'*'))
		{
			throw new VidiunAPIException(VidiunErrors::HOST_NAME_CONTAINS_ASTRIX ,$this->relatedHost );
		}
		if($this->sourceLocation == VidiunConfMapsSourceLocation::FS)
		{
			throw new VidiunAPIException(VidiunErrors::MAP_CANNOT_BE_CREATED_ON_FILE_SYSTEM);
		}
		parent::validateForInsert($propertiesToSkip);
	}

	public function validateContent()
	{
		$contentArray = json_decode($this->content, true);
		if(!$contentArray)
		{
			throw new VidiunAPIException(VidiunErrors::CANNOT_PARSE_CONTENT , "Cannot JSON decode content"  ,$this->content );
		}
		$initStr = iniUtils::arrayToIniString($contentArray);
		if(!parse_ini_string($initStr,true))
		{
			throw new VidiunAPIException(VidiunErrors::CANNOT_PARSE_CONTENT, "Cannot parse INI", $initStr);
		}
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

}