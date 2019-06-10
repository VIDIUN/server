<?php
/**
 * @package api
 * @subpackage objects
 * @abstract
 * @deprecated use VidiunRuleAction
 */
abstract class VidiunAccessControlAction extends VidiunObject
{
	/**
	 * The type of the access control action
	 * 
	 * @readonly
	 * @var VidiunAccessControlActionType
	 */
	public $type;
}