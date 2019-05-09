<?php
/**
 * @package api
 * @subpackage objects
 */
abstract class VidiunAssociativeArray extends VidiunTypedArray
{
	/* (non-PHPdoc)
	 * @see VidiunTypedArray::offsetSet()
	 */
	public function offsetSet($offset, $value) 
	{
		$this->validateType($value);
		
		if ($offset === null)
		{
			$this->array[] = $value;
		}
		else
		{
			$this->array[$offset] = $value;
		}
			
		$this->count = count ( $this->array );
	}
}