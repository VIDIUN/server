<?php

/**
 * @package plugins.scheduledTask
 * @subpackage lib.objectFilterEngine
 */
abstract class VObjectFilterEngineBase
{
	/**
	 * @var VidiunClient
	 */
	protected $_client;

	/**
	 * @var int
	 */
	private $_pageSize;

	/**
	 * @var int
	 */
	private $_pageIndex;

	public function __construct(VidiunClient $client)
	{
		$this->_client = $client;
	}

	/**
	 * @param VidiunFilter $filter
	 * @return VidiunObjectListResponse
	 */
	abstract function query(VidiunFilter $filter);

	/**
	 * @param int $pageIndex
	 */
	public function setPageIndex($pageIndex)
	{
		$this->_pageIndex = $pageIndex;
	}

	/**
	 * @return int
	 */
	public function getPageIndex()
	{
		return $this->_pageIndex;
	}

	/**
	 * @param int $pageSize
	 */
	public function setPageSize($pageSize)
	{
		$this->_pageSize = $pageSize;
	}

	/**
	 * @return int
	 */
	public function getPageSize()
	{
		return $this->_pageSize;
	}

	/**
	 * @return VidiunFilterPager
	 */
	public function getPager()
	{
		$pager = new VidiunFilterPager();
		$pager->pageIndex = $this->_pageIndex;
		$pager->pageSize = $this->_pageSize;
		return $pager;
	}
}