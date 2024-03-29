<?php

require_once(dirname(__file__) . '/../request/infraRequestUtils.class.php');
require_once(dirname(__file__) . '/vRendererBase.php');

/*
 * @package server-infra
 * @subpackage renderers
 */
class vRendererString implements vRendererBase
{
	protected $content;
	protected $contentType;
	protected $maxAge;
	
	public function __construct($content, $contentType, $maxAge = 8640000)
	{
		$this->content = $content;
		$this->contentType = $contentType;
		$this->maxAge = $maxAge;
	}
	
	public function validate()
	{
		return true;
	}
	
	public function output()
	{
		header('Content-Length: '.strlen($this->content));
		if ($this->contentType)
			header('Content-Type: '.$this->contentType);
		header("Access-Control-Allow-Origin:*");
		
		infraRequestUtils::sendCachingHeaders($this->maxAge);

		echo $this->content;
	}
}
