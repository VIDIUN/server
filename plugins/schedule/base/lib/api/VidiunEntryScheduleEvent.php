<?php
/**
 * @package plugins.schedule
 * @subpackage api.objects
 * @abstract
 */
abstract class VidiunEntryScheduleEvent extends VidiunScheduleEvent
{
	/**
	 * Entry to be used as template during content ingestion
	 * @var string
	 * @filter eq
	 */
	public $templateEntryId;

	/**
	 * Entries that associated with this event
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $entryIds;
	
	/**
	 * Categories that associated with this event
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $categoryIds;

	/**
	 * Blackout schedule events the conflict with this event
	 * @readonly
	 * @var VidiunScheduleEventArray
	 */
	public $blackoutConflicts;

	/*
	 * Mapping between the field on this object (on the left) and the setter/getter on the entry object (on the right)  
	 */
	private static $map_between_objects = array 
	 (	
		'templateEntryId',
		'entryIds',
		'categoryIds',
		'blackoutConflicts',
	 );
		 
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
}