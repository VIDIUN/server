<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunReportTotal extends VidiunObject 
{
	/**
	 * @var string
	 */
	public $header;
	
	/**
	 * @var string
	 */
	public $data;
	
	
	public function fromReportTotal ( array $header , array $data , $delimiter )
	{
		$this->header = implode ( $delimiter , $header );
		$this->data = implode ( $delimiter , $data );
	}
	
}
