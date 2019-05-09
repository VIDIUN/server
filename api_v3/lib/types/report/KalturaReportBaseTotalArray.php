<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunReportBaseTotalArray extends VidiunTypedArray
{
	public static function fromReportDataArray ( $arr )
	{
		$newArr = new VidiunReportBaseTotalArray();
		foreach ( $arr as $id => $data )
		{
			$nObj = new VidiunReportBaseTotal();
			$nObj->fromReportData ( $id, $data );
			$newArr[] = $nObj;
		}
			
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunReportBaseTotal" );
	}
}
?>