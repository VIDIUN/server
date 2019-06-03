<?php
/**
 * @package api
 * @subpackage objects
 * @abstract
 */
abstract class VidiunRuleAction extends VidiunObject
{
	/**
	 * The type of the action
	 * 
	 * @readonly
	 * @var VidiunRuleActionType
	 */
	public $type;
}