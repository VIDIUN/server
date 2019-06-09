<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunPartnerArray extends VidiunTypedArray
{
	public static function fromPartnerArray(array $arr)
	{
		$newArr = new VidiunPartnerArray();
		foreach($arr as $obj)
		{
			$nObj = new VidiunPartner();
			$nObj->fromPartner($obj);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunPartner" );
	}
}
?>