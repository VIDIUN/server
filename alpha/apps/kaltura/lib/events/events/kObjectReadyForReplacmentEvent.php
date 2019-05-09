<?php
/**
 * Applicative event that raised explicitly by the developer
 * @package Core
 * @subpackage events
 */
class vObjectReadyForReplacmentEvent extends vApplicativeEvent
{
	const EVENT_CONSUMER = 'vObjectReadyForReplacmentEventConsumer';
	
	public function getConsumerInterface()
	{
		return self::EVENT_CONSUMER;
	}
	
	/**
	 * @param vObjectReadyForReplacmentEventConsumer $consumer
	 * @return bool true if should continue to the next consumer
	 */
	protected function doConsume(VidiunEventConsumer $consumer)
	{
		if(!$consumer->shouldConsumeReadyForReplacmentEvent($this->object))
			return true;
			
		$additionalLog = '';
		if(method_exists($this->object, 'getId'))
			$additionalLog .= 'id [' . $this->object->getId() . ']';
			
		VidiunLog::debug('consumer [' . get_class($consumer) . '] started handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		$result = $consumer->objectReadyForReplacment($this->object, $this->raisedJob);
		VidiunLog::debug('consumer [' . get_class($consumer) . '] finished handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		return $result;
	}

}