<?php
/**
 * @package api
 * @subpackage objects
 */
abstract class VidiunTypedArray extends VidiunObject implements ArrayAccess, Iterator, Countable
{
	protected $array = array();
	private $class = "";
	
	/**
	 * @var int $count
	 */	
	public $count;
	
	public function __construct($class = 'VidiunObject')
	{
		$this->class = $class;
	}
	
	public function offsetExists($offset) 
	{
		return array_key_exists($offset, $this->array);
	}

	public function offsetGet($offset) 
	{
		return $this->array[$offset];
	}

	protected function validateType($value) 
	{
		if (!is_object($value) && is_subclass_of($value, $this->class))
			throw new Exception("'".get_class($value)."' is not an instance of '".$this->class."'");
	}

	public function offsetSet($offset, $value) 
	{
		$this->validateType($value);
		
		if ($offset === null)
		{
			$this->array[] = $value;
		}
			
		$this->count = count ( $this->array );
	}

	
	public function offsetUnset($offset) 
	{
		unset($this->array[$offset]);
	}
	
	public function current() 
	{
		return current($this->array);
	}

	public function next() 
	{
		return next($this->array);
	}

	public function key() 
	{
		return key($this->array);
	}

	public function valid() 
	{
		return ($this->current() !== false);
	}

	public function rewind() 
	{
		reset($this->array);
	}
	
	public function getType()
	{
		return $this->class;
	}
	
	public function count()
	{
		return count($this->array);
	}
	
	public function toArray()
	{
		return $this->array;
	}
	
	public function toObjectsArray()
	{
		$array = array();
		foreach($this->array as $key => $obj)
		{
			$coreObject = $obj->toObject();
			if($coreObject)
				$array[$key] = $coreObject;
		}
		return $array;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::loadRelatedObjects($responseProfile)
	 */
	public function loadRelatedObjects(VidiunDetachedResponseProfile $responseProfile)
	{
		foreach($this as &$item)
		{
			/* @var $item VidiunObject */
			$item->loadRelatedObjects($responseProfile);
		}
	}
}