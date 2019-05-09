<?php
/**
 * @package plugins.inletArmada
 * @subpackage lib
 */
class InletArmadaConversionEngineType implements IVidiunPluginEnum, conversionEngineType
{
	const INLET_ARMADA = 'InletArmada';
	
	public static function getAdditionalValues()
	{
		return array(
			'INLET_ARMADA' => self::INLET_ARMADA
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
