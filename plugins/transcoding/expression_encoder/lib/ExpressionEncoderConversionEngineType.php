<?php
/**
 * @package plugins.expressionEncoder
 * @subpackage lib
 */
class ExpressionEncoderConversionEngineType implements IVidiunPluginEnum, conversionEngineType
{
	const EXPRESSION_ENCODER = 'ExpressionEncoder';
	
	public static function getAdditionalValues()
	{
		return array(
			'EXPRESSION_ENCODER' => self::EXPRESSION_ENCODER
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
