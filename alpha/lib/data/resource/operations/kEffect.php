<?php
/**
 * Effects Attribute
 *
 * @package Core
 * @subpackage model.data
 */
class vEffect
{

	/**
	 * audio fade in MilSec
	 * @var vEffectType
	 */
	private $effectType;


	/**
	 * audio fade in MilSec
	 * @var string value
	 */
	private $value;

	/**
	 * @return vEffectType
	 */
	public function getEffectType()
	{
		return $this->effectType;
	}

	/**
	 * @param vEffectType $effectType
	 */
	public function setEffectType($effectType)
	{
		$this->effectType = $effectType;
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param string $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

}