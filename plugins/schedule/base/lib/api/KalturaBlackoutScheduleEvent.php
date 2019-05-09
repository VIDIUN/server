<?php
/**
 * @package plugins.schedule
 * @subpackage api.objects
 */
class VidiunBlackoutScheduleEvent extends VidiunScheduleEvent
{
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($sourceObject = null, $propertiesToSkip = array())
	{
		if(is_null($sourceObject))
		{
			$sourceObject = new BlackoutScheduleEvent();
		}

		return parent::toObject($sourceObject, $propertiesToSkip);
	}

	/**
	 * {@inheritDoc}
	 * @see VidiunScheduleEvent::getScheduleEventType()
	 */
	public function getScheduleEventType()
	{
		return ScheduleEventType::BLACKOUT;
	}
}