<?php
/**
 * @package plugins.elasticSearch
 * @subpackage lib.events
 */
class vESearchSearchHistoryInfoEvent extends vApplicativeEvent
{

	const EVENT_CONSUMER = 'vESearchSearchHistoryInfoEventConsumer';

	public function __construct($object)
	{
		$this->object = $object;

		$additionalLog = '';
		if (method_exists($object, 'getId'))
			$additionalLog .= ' id [' . $object->getId() . ']';

		VidiunLog::debug("Event [" . get_class($this) . "] object type [" . get_class($object) . "]" . $additionalLog);
	}

	/**
	 * @return string - name of consumer interface
	 */
	public function getConsumerInterface()
	{
		return self::EVENT_CONSUMER;
	}

	/**
	 * Executes the consumer
	 * @param VidiunEventConsumer $consumer
	 * @return bool true if should continue to the next consumer
	 */
	protected function doConsume(VidiunEventConsumer $consumer)
	{
		if (!$consumer->shouldConsumeESearchSearchHistoryInfoEvent($this->object))
			return true;

		$additionalLog = '';
		if (method_exists($this->object, 'getId'))
			$additionalLog .= 'id [' . $this->object->getId() . ']';

		VidiunLog::debug('consumer [' . get_class($consumer) . '] started handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		$result = $consumer->consumeESearchSearchHistoryInfoEvent($this->object);
		VidiunLog::debug('consumer [' . get_class($consumer) . '] finished handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		return $result;
	}

}
