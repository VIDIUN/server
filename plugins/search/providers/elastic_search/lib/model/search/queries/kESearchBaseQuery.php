<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model.search
 */
abstract class vESearchBaseQuery
{

	abstract public function getFinalQuery();

	/**
	 * @return boolean
	 */
	public function getShouldMoveToFilterContext()
	{
		return false;
	}

}
