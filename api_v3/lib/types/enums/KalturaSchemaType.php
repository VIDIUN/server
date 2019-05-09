<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunSchemaType extends VidiunDynamicEnum implements SchemaType
{
	public static function getDescriptions()
	{
		$descriptions = array(
			self::SYNDICATION => 'Syndication feed'
		);
		
		return self::mergeDescriptions(self::getEnumClass(), $descriptions);
	}

	public static function getDescription($type)
	{
		$descriptions = self::getDescriptions();
		if(isset($descriptions[$type]))
			return $descriptions[$type];
			
		return null;
	}

	public static function getEnumClass()
	{
		return 'SchemaType';
	}
}
