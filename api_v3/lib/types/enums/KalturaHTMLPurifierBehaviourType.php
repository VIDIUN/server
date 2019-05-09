<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunHTMLPurifierBehaviourType extends VidiunDynamicEnum implements HTMLPurifierBehaviourType
{
	/**
	 * @return string
	 */
	public static function getEnumClass()
	{
		return 'HTMLPurifierBehaviourType';
	}
}
