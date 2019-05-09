<?php

/*
 * @package server-infra
 * @subpackage renderers
 */
interface vRendererBase
{
	public function validate();
	
	public function output();
}
