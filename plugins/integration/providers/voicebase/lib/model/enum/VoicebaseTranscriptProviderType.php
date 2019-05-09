<?php
/**
 * @package plugins.voicebase
 * @subpackage lib.enum
 */
class VoicebaseTranscriptProviderType implements IVidiunPluginEnum, TranscriptProviderType
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
