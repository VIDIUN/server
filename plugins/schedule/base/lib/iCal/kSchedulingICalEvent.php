<?php

class vSchedulingICalEvent extends vSchedulingICalComponent
{
	/**
	 * @var vSchedulingICalRule
	 */
	private $rule = null;

	private static $stringFields = array(
		'summary',
		'description',
		'status',
		'geoLatitude',
		'geoLongitude',
		'location',
		'priority',
		'sequence',
		'duration',
		'contact',
		'comment',
		'organizer',
	);

	private static $dateFields = array(
		'startDate' => 'dtstart',
		'endDate' => 'dtend',
	);

	protected static function formatDurationString($durationStringInSeconds)
	{
		$duration = 'PT';
		$seconds = (int)$durationStringInSeconds;
		$hours = (int)($seconds / 3600);
		$minutes = (int)(($seconds - $hours * 3600) / 60);
		$secondsInt = (int)($seconds - $hours * 3600 - $minutes * 60);

		$duration = $duration . $hours . 'H';
		$duration = $duration . $minutes . 'M';
		$duration = $duration . $secondsInt . 'S';

		return $duration;
	}

	/**
	 * {@inheritDoc}
	 * @see vSchedulingICalComponent::getType()
	 */
	protected function getType()
	{
		return vSchedulingICal::TYPE_EVENT;
	}

	public function getUid()
	{
		return $this->getField('uid');
	}

	public function getMethod()
	{
		return $this->getField('method');
	}

	public function setRRule($rrule)
	{
		$this->rule = new vSchedulingICalRule($rrule);
	}

	/**
	 * @return vSchedulingICalRule
	 */
	public function getRule()
	{
		return $this->rule;
	}

	public function setRule(vSchedulingICalRule $rule)
	{
		$this->rule = $rule;
	}

	/**
	 * {@inheritDoc}
	 * @see vSchedulingICalComponent::writeBody()
	 */
	protected function writeBody()
	{
		$ret = parent::writeBody();

		if ($this->rule)
			$ret .= $this->writeField('RRULE', $this->rule->getBody());

		return $ret;
	}

	/**
	 * {@inheritDoc}
	 * @see vSchedulingICalComponent::toObject()
	 */
	public function toObject()
	{
		$type = $this->getVidiunType();
		$event = null;
		switch ($type)
		{
			case VidiunScheduleEventType::RECORD:
				$event = new VidiunRecordScheduleEvent();
				break;

			case VidiunScheduleEventType::LIVE_STREAM:
				$event = new VidiunLiveStreamScheduleEvent();
				break;

			case VidiunScheduleEventType::BLACKOUT:
				$event = new VidiunBlackoutScheduleEvent();
				break;

			default:
				throw new Exception("Event type [$type] not supported");
		}

		$event->referenceId = $this->getUid();

		foreach (self::$stringFields as $string)
		{
			$event->$string = $this->getField($string);
			if ( $string == 'duration')
			  $event->$string = $this->formatDuration($event->$string);
		}

		foreach (self::$dateFields as $date => $field)
		{
			$configurationField = $this->getConfigurationField($field);
			$timezoneFormat = null;
			if ($configurationField != null)
			{
				if (preg_match('/"([^"]+)"/', $configurationField, $matches))
				{
					if (isset($matches[1]))
						$timezoneFormat = $matches[1];

				} elseif (preg_match('/=([^"]+)/', $configurationField, $matches))
				{
					if (isset($matches[1]))
						$timezoneFormat = $matches[1];
				}
			}
			$val = vSchedulingICal::parseDate($this->getField($field), $timezoneFormat);
			$event->$date = $val;
		}

		$classificationTypes = array(
			'PUBLIC' => VidiunScheduleEventClassificationType::PUBLIC_EVENT,
			'PRIVATE' => VidiunScheduleEventClassificationType::PRIVATE_EVENT,
			'CONFIDENTIAL' => VidiunScheduleEventClassificationType::CONFIDENTIAL_EVENT
		);

		$classificationType = $this->getField('class');
		if (isset($classificationTypes[$classificationType]))
			$event->classificationType = $classificationTypes[$classificationType];

		$rule = $this->getRule();
		if ($rule)
		{
			$event->recurrenceType = VidiunScheduleEventRecurrenceType::RECURRING;
			$event->recurrence = $rule->toObject();
		} else
		{
			$event->recurrenceType = VidiunScheduleEventRecurrenceType::NONE;
		}

		$event->parentId = $this->getField('x-vidiun-parent-id');
		$event->tags = $this->getField('x-vidiun-tags');
		$event->ownerId = $this->getField('x-vidiun-owner-id');

		if ($event instanceof VidiunEntryScheduleEvent)
		{
			$event->templateEntryId = $this->getField('x-vidiun-template-entry-id');
			$event->entryIds = $this->getField('x-vidiun-entry-ids');
			$event->categoryIds = $this->getField('x-vidiun-category-ids');
		}

		return $event;
	}

	/**
	 * @param VidiunScheduleEvent $event
	 * @return vSchedulingICalEvent
	 */
	public static function fromObject(VidiunScheduleEvent $event)
	{
		$object = new vSchedulingICalEvent();
		$resourceIds = array();

		if ($event->referenceId)
			$object->setField('uid', $event->referenceId);

		foreach (self::$stringFields as $string)
		{
			if ($event->$string)
			{
				if ($string == 'duration')
				{
					$duration = self::formatDurationString($event->$string);
					$object->setField($string, $duration);
				} else
					$object->setField($string, $event->$string);
			}
		}

		foreach (self::$dateFields as $date => $field)
		{
			if ($event->$date)
				$object->setField($field, vSchedulingICal::formatDate($event->$date));
		}

		$classificationTypes = array(
			VidiunScheduleEventClassificationType::PUBLIC_EVENT => 'PUBLIC',
			VidiunScheduleEventClassificationType::PRIVATE_EVENT => 'PRIVATE',
			VidiunScheduleEventClassificationType::CONFIDENTIAL_EVENT => 'CONFIDENTIAL'
		);

		if ($event->classificationType && isset($classificationTypes[$event->classificationType]))
			$classificationType = $object->setField('class', $classificationTypes[$event->classificationType]);

		if ($event->recurrence)
		{
			$rule = vSchedulingICalRule::fromObject($event->recurrence);
			$object->setRule($rule);
		}

		$object->setField('dtstamp', vSchedulingICal::formatDate($event->updatedAt));
		$object->setField('x-vidiun-id', $event->id);
		$object->setField('x-vidiun-type', $event->getScheduleEventType());
		$object->setField('x-vidiun-partner-id', $event->partnerId);
		$object->setField('x-vidiun-status', $event->status);
		$object->setField('x-vidiun-owner-id', $event->ownerId);


		$resources = ScheduleEventResourcePeer::retrieveByEventId($event->id);
		foreach ($resources as $resource)
		{
			/* @var $resource ScheduleEventResource */
			$resourceIds[] = $resource->getResourceId();
		}

		if ($event->parentId)
		{
			$parent = ScheduleEventPeer::retrieveByPK($event->parentId);
			if ($parent)
			{
				$object->setField('x-vidiun-parent-id', $event->parentId);
				if ($parent->getReferenceId())
					$object->setField('x-vidiun-parent-uid', $parent->getReferenceId());

				if (!count($resourceIds))
				{
					$resources = ScheduleEventResourcePeer::retrieveByEventId($event->parentId);
					foreach ($resources as $resource)
					{
						/* @var $resource ScheduleEventResource */
						$resourceIds[] = $resource->getResourceId();
					}
				}
			}
		}

		$resourceIds = array_diff($resourceIds, array(0)); //resource 0 should not be exported outside of vidiun BE.
		if (count($resourceIds))
			$object->setField('x-vidiun-resource-ids', implode(',', $resourceIds));

		if ($event->tags)
			$object->setField('x-vidiun-tags', $event->tags);

		if ($event instanceof VidiunEntryScheduleEvent)
		{
			if ($event->templateEntryId)
				$object->setField('x-vidiun-template-entry-id', $event->templateEntryId);

			if ($event->entryIds)
				$object->setField('x-vidiun-entry-ids', $event->entryIds);

			if ($event->categoryIds)
			{
				$object->setField('x-vidiun-category-ids', $event->categoryIds);

				// hack, to be removed after x-vidiun-category-ids will be fully supported by other partners
				$pks = explode(',', $event->categoryIds);
				$categories = categoryPeer::retrieveByPKs($pks);
				$fullIds = array();
				foreach ($categories as $category)
				{
					/* @var $category category */
					$fullIds[] = $category->getFullIds();
				}
				if (count($fullIds))
					$object->setField('related-to', implode(';', $fullIds));
			}
		}

		if ($event->getScheduleEventType() == ScheduleEventType::LIVE_STREAM)
		{
			$entry = entryPeer::retrieveByPK($event->templateEntryId);
			if ( $entry && $entry->getType() == entryType::LIVE_STREAM)
			{
				/* @var $event LiveStreamEntry */
				$object->setField('x-vidiun-primary-rtmp-endpoint', $entry->getPrimaryBroadcastingUrl());
				$object->setField('x-vidiun-secondary-rtmp-endpoint', $entry->getSecondaryBroadcastingUrl());
				$object->setField('x-vidiun-primary-rtsp-endpoint', $entry->getPrimaryRtspBroadcastingUrl());
				$object->setField('x-vidiun-secondary-rtsp-endpoint', $entry->getSecondaryRtspBroadcastingUrl());
				$object->setField('x-vidiun-live-stream-name', $entry->getStreamName());
				$object->setField('x-vidiun-live-stream-username', $entry->getStreamUsername());
				$object->setField('x-vidiun-live-stream-password', $entry->getStreamPassword());
			}

		}

		return $object;
	}

	/**
	 * @param $duration
	 */
	private function formatDuration($duration)
	{
		if ($duration && ( $duration != '0' && intval($duration) == 0))
		{
			$datetime = new DateTime('@0');
			$datetime->add(new DateInterval($duration));
			$duration = $datetime->format('U');
		}
		return $duration;
	}
}
