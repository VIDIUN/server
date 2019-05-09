<?php

interface IResponseProfile
{
	/**
	 * @return string
	 */
	public function getName();
	
	/**
	 * @return ResponseProfileType
	 */
	public function getType();
	
	/**
	 * @return string
	 */
	public function getFields();
	
	/**
	 * @return array<string>
	 */
	public function getFieldsArray();
	
	/**
	 * @return array<IResponseProfile>
	 */
	public function getRelatedProfiles();
	
	/**
	 * @return baseObjectFilter
	 */
	public function getFilter();
	
	/**
	 * @return string
	 */
	public function getFilterApiClassName();

	/**
	 * @return vFilterPager
	 */
	public function getPager();
	
	/**
	 * @param string $v
	 */
	public function setName($v);
	
	/**
	 * @param ResponseProfileType $v
	 */
	public function setType($v);
	
	/**
	 * @param string $v
	 */
	public function setFields($v);
	
	/**
	 * @param array<IResponseProfile> $v
	 */
	public function setRelatedProfiles(array $v);

	/**
	 * @param baseObjectFilter $filter
	 */
	public function setFilter(baseObjectFilter $filter);

	/**
	 * @param string $filter
	 */
	public function setFilterApiClassName($filterApiClassName);

	/**
	 * @param vFilterPager $pager
	 */
	public function setPager(vFilterPager $pager);
	
	/**
	 * @return array
	 */
	public function getMappings();

	/**
	 * @param array<vResponseProfileMapping> $mappings
	 */
	public function setMappings(array $mappings);
}
