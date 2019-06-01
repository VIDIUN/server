<?php
/**
 * The VidiunFilterPager object enables paging management to be applied upon service list actions. 
 * 
 * @package api
 * @subpackage filters
 */
class VidiunFilterPager extends VidiunPager
{
	public function toObject($object = null, $skipProperties = array())
	{
		if(!$object)
		{
			$object = new vFilterPager();
		}
		
		return parent::toObject($object, $skipProperties);
	}

	public function calcPageSize()
	{
		return max(min($this->pageSize, baseObjectFilter::getMaxInValues()), 0);
	}

	public function attachToCriteria ( Criteria $c )
	{
		$this->pageIndex = $this->calcPageIndex();
		$this->pageSize = $this->calcPageSize();
		$c->setLimit( $this->pageSize );
		$c->setOffset( $this->calcOffset() );
	}
	
	public static function detachFromCriteria(Criteria $c)
	{
		$c->setOffset(0);
		$c->setLimit(-1);
	}
}
