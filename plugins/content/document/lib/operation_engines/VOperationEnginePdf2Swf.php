<?php
/**
 * @package plugins.document
 * @subpackage lib
 */
class VOperationEnginePdf2Swf extends VSingleOutputOperationEngine
{
	const PDF_FORMAT = 'PDF document';
	
	/* (non-PHPdoc)
	 * @see VOperationEngine::doOperation()
	 */
	protected function doOperation()
	{
		$this->validateFormat(self::PDF_FORMAT);
		return parent::doOperation();
	}
}