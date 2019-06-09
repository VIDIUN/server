<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated
 */
class VidiunSearchResultArray extends VidiunTypedArray
{
	public static function fromSearchResultArray ( $arr , VidiunSearch $search )
	{
		$newArr = new VidiunSearchResultArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunSearchResult();
			$nObj->fromSearchResult( $obj , $search );
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunSearchResult" );
	}
}
?>