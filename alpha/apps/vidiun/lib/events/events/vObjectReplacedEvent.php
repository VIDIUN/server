<?php
/**
 * Applicative event that raised explicitly by the developer
 * @package Core
 * @subpackage events
 */
class vObjectReplacedEvent extends vApplicativeEvent
{
	/**
	 * @var BaseObject
	 */
	protected $replacingObject;

	const EVENT_CONSUMER = 'vObjectReplacedEventConsumer';
	
	public function __construct(BaseObject $object, BaseObject $replacingObject, BatchJob $raisedJob = null)
	{
		$this->replacingObject = $replacingObject;
		parent::__construct($object, $raisedJob);
	}
	
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
		if(!$consumer->shouldConsumeReplacedEvent($this->object))
			return true;
			
		$additionalLog = '';
		if(method_exists($this->object, 'getId'))
			$additionalLog .= 'id [' . $this->object->getId() . ']';
			
		VidiunLog::debug('consumer [' . get_class($consumer) . '] started handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		$result = $consumer->objectReplaced($this->object, $this->replacingObject, $this->raisedJob);
		VidiunLog::debug('consumer [' . get_class($consumer) . '] finished handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		return $result;
	}

}