<?php
/**
 * @package Core
 * @subpackage events
 */
class vObjectInvalidateCacheEvent extends vApplicativeEvent
{
	const EVENT_CONSUMER = 'vObjectInvalidateCacheEventConsumer';

	public function getConsumerInterface()
	{
		return self::EVENT_CONSUMER;
	}

	/**
	 * @param VidiunEventConsumer $consumer
	 * @return bool true if should continue to the next consumer
	 */
	protected function doConsume(VidiunEventConsumer $consumer)
	{
		if(!$consumer->shouldConsumeInvalidateCache($this->object,$this->raisedJob))
		{
			return true;
		}

		$additionalLog = '';
		if(method_exists($this->object, 'getId'))
			$additionalLog .= 'id [' . $this->object->getId() . ']';

		VidiunLog::debug('consumer [' . get_class($consumer) . '] started handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		$result = $consumer->invalidateCache($this->object, $this->raisedJob);
		VidiunLog::debug('consumer [' . get_class($consumer) . '] finished handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		return $result;
	}
}
