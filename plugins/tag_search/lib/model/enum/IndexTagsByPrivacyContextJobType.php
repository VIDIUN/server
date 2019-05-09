<?php
/**
 * @package plugins.tag_search
 *  @subpackage model.enum
 */
class IndexTagsByPrivacyContextJobType implements IVidiunPluginEnum, BatchJobType
{
	const INDEX_TAGS = 'IndexTagsByPrivacyContext';
	
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues() {
		return array(
			'INDEX_TAGS' => self::INDEX_TAGS,
		);
		
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() {
		return array();
		
	}


}