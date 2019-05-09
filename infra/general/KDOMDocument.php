<?php

/**
 *  @package infra
 *  @subpackage general
 */
class VDOMDocument extends DOMDocument
{
	public function __construct ($version = null, $encoding = null )
	{
		parent::__construct($version, $encoding);
	}

	public function load ( $filename , $options = 0 ,$key = null, $iv = null)
	{
		if(!file_exists($filename) || !filesize($filename))
			throw new Exception('Empty file supplied as input');
			
		return parent::loadXML(vEncryptFileUtils::getEncryptedFileContent($filename, $key, $iv), $options);
	}

	public function schemaValidate ( $filename , $key = null, $iv = null)
	{
		if(!file_exists($filename) || !filesize($filename))
			throw new Exception('Empty file supplied as input');
		
		return parent::schemaValidateSource(vEncryptFileUtils::getEncryptedFileContent($filename, $key, $iv));
	}

}

