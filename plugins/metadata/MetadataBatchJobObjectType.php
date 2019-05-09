<?php
/**
 * @package api
 * @subpackage enum
 */
class MetadataBatchJobObjectType implements IVidiunPluginEnum, BatchJobObjectType
{
	const METADATA				= "Metadata";
	const METADATA_PROFILE 		= "MetadataProfile";
	
	public static function getAdditionalValues()
	{
		return array(
			'METADATA' => self::METADATA,
			'METADATA_PROFILE' => self::METADATA_PROFILE,
		);
	}
	
	/**
	 * @return array
	 */
	public static function getAdditionalDescriptions()
	{
		return array();
	}
}
