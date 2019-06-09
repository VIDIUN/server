<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunReportResponse extends VidiunObject 
{
	/**
	 * @var string
	 */
	public $columns;
	
	/**
	 * @var VidiunStringArray
	 */
	public $results;
	
	public static function fromColumnsAndRows($columns, $rows)
	{
		$reportResponse = new VidiunReportResponse();
		$reportResponse->columns = implode(',', $columns);
		$reportResponse->results = new VidiunStringArray();
		foreach($rows as $row)
		{
			// we are using comma as a seperator, so don't allow it in results
			foreach($row as &$tempColumnData)
				$tempColumnData = str_replace(',', '', $tempColumnData);
				
			$string = new VidiunString();
			$string->value = implode(',', $row);
			$reportResponse->results[] = $string;
		}
		return $reportResponse;
	}
}