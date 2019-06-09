<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunReportGraphArray extends VidiunTypedArray
{
	public static function fromReportDataArray ( $arr, $delimiter = ',' )
	{
		$newArr = new VidiunReportGraphArray();
		foreach ( $arr as $id => $data )
		{
			$nObj = new VidiunReportGraph();
			$nObj->fromReportData ( $id, $data, $delimiter );
			$newArr[] = $nObj;
		}
			
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunReportGraph" );
	}
}
?>