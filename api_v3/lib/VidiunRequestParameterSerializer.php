<?php
class VidiunRequestParameterSerializer
{
	/**
	 * Flattens VidiunObject into an array of parameters that can sent over a GET request
	 * @param VidiunObject $object
	 * @param string $prefix
	 * @return array
	 */
	public static function serialize (VidiunObject $object, $prefix)
	{
		$params = array();
		if (!($object instanceof VidiunTypedArray))
			$params[] = "$prefix:objectType=".get_class($object);
		
		foreach ($object as $prop => $val)
		{
			if (is_null($val))
				continue;
			
			if (is_numeric($prop))
			{
				$prop = "item$prop";	
			}
			
			if (is_scalar($val))
			{
				$params[] = "$prefix:$prop=$val";
			}
			elseif ($val instanceof VidiunTypedArray)
			{
				$params = array_merge($params, self::serialize($val->toArray(),"$prefix:$prop"));				
			}
			else 
			{
				$params = array_merge($params, self::serialize($val,"$prefix:$prop"));
			}
		}
		
		return $params;
	}
}