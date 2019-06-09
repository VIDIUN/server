<?php

/**
 * @package Core
 * @subpackage model.data
 */
class vFilterPager extends vPager
{
	public function calcPageSize()
	{
		return max(min($this->pageSize, baseObjectFilter::getMaxInValues()), 0);
	}

}
