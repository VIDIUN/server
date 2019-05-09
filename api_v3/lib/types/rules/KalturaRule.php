<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunRule extends VidiunObject
{
	/**
	 * Short Rule Description
	 *
	 * @var string
	 */
	public $description;
	
	/**
	 * Rule Custom Data to allow saving rule specific information 
	 *
	 * @var string
	 */
	public $ruleData;
	
	/**
	 * Message to be thrown to the player in case the rule is fulfilled
	 * 
	 * @var string
	 */
	public $message;

	/**
	 * Code to be thrown to the player in case the rule is fulfilled
	 *
	 * @var string
	 */
	public $code;
	
	/**
	 * Actions to be performed by the player in case the rule is fulfilled
	 * 
	 * @var VidiunRuleActionArray
	 */
	public $actions;
	
	/**
	 * Conditions to validate the rule
	 * 
	 * @var VidiunConditionArray
	 */
	public $conditions;
	
	/**
	 * Indicates what contexts should be tested by this rule 
	 * 
	 * @var VidiunContextTypeHolderArray
	 */
	public $contexts;
	
	/**
	 * Indicates that this rule is enough and no need to continue checking the rest of the rules 
	 * 
	 * @var bool
	 */
	public $stopProcessing;
	
	/**
	 * Indicates if we should force vs validation for admin vs users as well
	 *
	 * @var VidiunNullableBoolean
	 */
	public $forceAdminValidation;

	private static $mapBetweenObjects = array
	(
		'description',
		'ruleData',
		'message',
		'code',
		'actions',
		'conditions',
		'contexts',
		'stopProcessing',
		'forceAdminValidation',
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vRule();
			
		return parent::toObject($dbObject, $skip);
	}
}
