<?php
/**
 * @package plugins.tag_search
 *  @subpackage model.enum
 */
class TagResolveBatchJobType implements IVidiunPluginEnum, BatchJobType
{
	const TAG_RESOLVE = 'TagResolve';
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues() {
		return array(
			'TAG_RESOLVE' => self::TAG_RESOLVE,
		);
		
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() {
		return array();
		
	}


}