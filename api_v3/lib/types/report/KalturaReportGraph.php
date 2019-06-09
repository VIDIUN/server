<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunReportGraph extends VidiunObject 
{
	/**
	 * @var string
	 */
	public $id;
	
	/**
	 * @var string
	 */
	public $data;
	
	
	public function fromReportData ( $id , array $dataArr , $delimiter )
	{
		$this->id = $id;
		$str = "";
		foreach ( $dataArr as $x => $y )
		{
			$str .= "$x$delimiter$y;";
		}
		
		$this->data = $str;
		
		return $this;
	}
}
