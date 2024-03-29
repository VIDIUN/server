<?php
/**
 * @package plugins.annotation
 * @subpackage lib.enum
 */
class AnnotationMetadataObjectType implements IVidiunPluginEnum, MetadataObjectType
{
	const ANNOTATION = 'Annotation';
	
	public static function getAdditionalValues()
	{
		return array(
			'ANNOTATION' => self::ANNOTATION,
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
