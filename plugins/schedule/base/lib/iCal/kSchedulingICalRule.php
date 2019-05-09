<?php

class vSchedulingICalRule extends vSchedulingICalComponent
{
	private static $stringFields = array(
		'name',
		'count',
		'interval',
		'bySecond',
		'byMinute',
		'byHour',
		'byDay',
		'byMonthDay',
		'byYearDay',
		'byWeekNumber' => 'byweekno',
		'byMonth',
		'byOffset' => 'bysetpos',
		'weekStartDay' => 'wkst',
	);
	
	/**
	 * {@inheritDoc}
	 * @see vSchedulingICalComponent::getLineDelimiter()
	 */
	protected function getLineDelimiter()
	{
		return ";";
	}
	
	/**
	 * {@inheritDoc}
	 * @see vSchedulingICalComponent::getFieldDelimiter()
	 */
	protected function getFieldDelimiter()
	{
		return '=';
	}
	
	/**
	 * {@inheritDoc}
	 * @see vSchedulingICalComponent::getType()
	 */
	protected function getType()
	{
		return 'RRULE';
	}

	/**
	 * {@inheritDoc}
	 * @see vSchedulingICalComponent::toObject()
	 */
	public function toObject()
	{
		$rule = new VidiunScheduleEventRecurrence();
		$rule->frequency = constant('VidiunScheduleEventRecurrenceFrequency::' . $this->getField('freq'));
		$rule->until = vSchedulingICal::parseDate($this->getField('until'));

		$strings = array(
			'name',
			'count',
			'interval',
			'bySecond',
			'byMinute',
			'byHour',
			'byDay',
			'byMonthDay',
			'byYearDay',
			'byWeekNumber' => 'byweekno',
			'byMonth',
			'byOffset' => 'bysetpos',
			'weekStartDay' => 'wkst',
		);
		foreach(self::$stringFields as $attribute => $field)
		{
			if(is_numeric($attribute))
				$attribute = $field;
			
			$rule->$attribute = $this->getField($field);
		}
		
		return $rule;
	}
	
	/**
	 * @param VidiunScheduleEventRecurrence $rule
	 * @return vSchedulingICalRule
	 */
	public static function fromObject(VidiunScheduleEventRecurrence $rule)
	{
		$object = new vSchedulingICalRule();

		$frequencyTypes = array(
			VidiunScheduleEventRecurrenceFrequency::SECONDLY => 'SECONDLY',
			VidiunScheduleEventRecurrenceFrequency::MINUTELY => 'MINUTELY',
			VidiunScheduleEventRecurrenceFrequency::HOURLY => 'HOURLY',
			VidiunScheduleEventRecurrenceFrequency::DAILY => 'DAILY',
			VidiunScheduleEventRecurrenceFrequency::WEEKLY => 'WEEKLY',
			VidiunScheduleEventRecurrenceFrequency::MONTHLY => 'MONTHLY',
			VidiunScheduleEventRecurrenceFrequency::YEARLY => 'YEARLY',
		);
		
		if($rule->frequency && isset($frequencyTypes[$rule->frequency]))
			$object->setField('freq', $frequencyTypes[$rule->frequency]);

		if($rule->until)
			$object->setField('until', vSchedulingICal::formatDate($rule->until));

		foreach(self::$stringFields as $attribute => $field)
		{
			if(is_numeric($attribute))
				$attribute = $field;
			
			if($rule->$attribute)
				$object->setField($field, $rule->$attribute);
		}
		
		return $object;
	}
	
	public function getBody()
	{
		$lines = array();
		foreach($this->fields as $field => $value)
			$lines[] = $field . $this->getFieldDelimiter() . $value;
		
		return implode($this->getLineDelimiter(), $lines);
	}
	
	/**
	 * {@inheritDoc}
	 * @see vSchedulingICalComponent::write()
	 */
	public function write()
	{
		return $this->writeBody();
	}
}
