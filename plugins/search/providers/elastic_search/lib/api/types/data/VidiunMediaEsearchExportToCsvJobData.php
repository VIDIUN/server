<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */

class VidiunMediaEsearchExportToCsvJobData extends VidiunExportCsvJobData
{
	/**
	 * Esearch parameters for the entry search
	 *
	 * @var VidiunESearchEntryParams
	 */
	public $searchParams;
	
	private static $map_between_objects = array
	(
		'searchParams',
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
	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (is_null($object_to_fill))
		{
			throw new VidiunAPIException(VidiunErrors::OBJECT_TYPE_ABSTRACT, "VidiunExportCsvJobData");
		}
		
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}