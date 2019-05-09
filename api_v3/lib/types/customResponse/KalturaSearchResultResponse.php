<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated
 */
class VidiunSearchResultResponse extends VidiunObject
{
	/**
	 * @var VidiunSearchResultArray
	 * @readonly
	 */
	public $objects;

	/**
	 * @var bool
	 * @readonly
	 */
	public $needMediaInfo;
}