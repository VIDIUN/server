<?php

class vSchedulingICalCalendar extends vSchedulingICalComponent
{
	/**
	 * @param string $data
	 * @param VidiunScheduleEventType $eventsType
	 */
	public function __construct($data = null, $eventsType = null)
	{
		$this->setVidiunType($eventsType);
		parent::__construct($data);
	}
	
	/**
	 * {@inheritDoc}
	 * @see vSchedulingICalComponent::getType()
	 */
	protected function getType()
	{
		return vSchedulingICal::TYPE_CALENDAR;
	}
}
