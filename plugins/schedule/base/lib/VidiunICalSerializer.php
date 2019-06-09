<?php

class VidiunICalSerializer extends VidiunSerializer
{
	private $calendar;
	
	public function __construct()
	{
		$this->calendar = new vSchedulingICalCalendar();
	}
	/**
	 * {@inheritDoc}
	 * @see VidiunSerializer::setHttpHeaders()
	 */
	public function setHttpHeaders()
	{
		header("Content-Type: text/calendar; charset=UTF-8");		
	}

	/**
	 * {@inheritDoc}
	 * @see VidiunSerializer::getHeader()
	 */
	public function getHeader() 
	{
		return $this->calendar->begin();
	}


	/**
	 * {@inheritDoc}
	 * @see VidiunSerializer::serialize()
	 */
	public function serialize($object)
	{
		if($object instanceof VidiunScheduleEvent)
		{
			$event = vSchedulingICalEvent::fromObject($object);
			return $event->write();
		}
		elseif($object instanceof VidiunScheduleEventArray)
		{
			$ret = '';
			foreach($object as $item)
			{
				$ret .= $this->serialize($item);
			}
			return $ret;
		}
		elseif($object instanceof VidiunScheduleEventListResponse)
		{
			$ret = $this->serialize($object->objects);
			$ret .= $this->calendar->writeField('X-VIDIUN-TOTAL-COUNT', $object->totalCount);
			return $ret;
		}
		elseif($object instanceof VidiunAPIException)
		{
			$ret = $this->calendar->writeField('BEGIN', 'VERROR');
			$ret .= $this->calendar->writeField('X-VIDIUN-CODE', $object->getCode());
			$ret .= $this->calendar->writeField('X-VIDIUN-MESSAGE', $object->getMessage());
			$ret .= $this->calendar->writeField('X-VIDIUN-ARGUMENTS', implode(';', $object->getArgs()));
			$ret .= $this->calendar->writeField('END', 'VERROR');
			return $ret;
		}
		else
		{
			$ret = $this->calendar->writeField('BEGIN', get_class($object));
			$ret .= $this->calendar->writeField('END', get_class($object));
			
			return $ret;
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see VidiunSerializer::getFooter()
	 */
	public function getFooter($execTime = null)
	{
		if($execTime)
			$this->calendar->writeField('x-vidiun-execution-time', $execTime);
		
		return $this->calendar->end();
	}
}