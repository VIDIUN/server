<?php
/**
 * @package Core
 * @subpackage model.data
 */
abstract class vRegexCondition extends vMatchCondition
{
	/* (non-PHPdoc)
	 * @see vMatchCondition::matches()
	 */
	protected function matches($field, $value)
	{
		return ($field === $value) || preg_match("/$value/i", $field);
	}
}
