<?php
/**
 * @package plugins.transcript
 * @subpackage api.enum
 */
class VidiunTranscriptProviderType extends VidiunDynamicEnum implements TranscriptProviderType
{
	public static function getEnumClass()
	{
		return 'TranscriptProviderType';
	}
}