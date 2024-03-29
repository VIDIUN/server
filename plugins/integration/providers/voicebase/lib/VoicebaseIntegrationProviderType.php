<?php
/**
 * @package plugins.voicebase
 * @subpackage lib.enum
 */
class VoicebaseIntegrationProviderType implements IVidiunPluginEnum, IntegrationProviderType
{
	const VOICEBASE = 'Voicebase';
	
	public static function getAdditionalValues()
	{
		return array(
			'VOICEBASE' => self::VOICEBASE,
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
