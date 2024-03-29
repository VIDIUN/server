<?php
/**
 * Applicative event that raised implicitly by the developer
 * @package Core
 * @subpackage events
 */
class vObjectDataChangedEvent extends vApplicativeEvent
{
	const EVENT_CONSUMER = 'vObjectDataChangedEventConsumer';
	
	/**
	 * @var string
	 */
	private $previousVersion;
		
	/**
	 * @param BaseObject $object
	 * @param string $previousVersion
	 */
	public function __construct(BaseObject $object, $previousVersion = null, BatchJob $raisedJob = null)
	{
		parent::__construct($object, $raisedJob);
		
		$this->previousVersion = $previousVersion;
	}
	
	public function getConsumerInterface()
	{
		return self::EVENT_CONSUMER;
	}
	
	/**
	 * @param vObjectDataChangedEventConsumer $consumer
	 * @return bool true if should continue to the next consumer
	 */
	protected function doConsume(VidiunEventConsumer $consumer)
	{
		if(!$consumer->shouldConsumeDataChangedEvent($this->object, $this->previousVersion))
			return true;
	
		$additionalLog = '';
		if(method_exists($this->object, 'getId'))
			$additionalLog .= 'id [' . $this->object->getId() . ']';
			
		VidiunLog::debug('consumer [' . get_class($consumer) . '] started handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		$result = $consumer->objectDataChanged($this->object, $this->previousVersion, $this->raisedJob);
		VidiunLog::debug('consumer [' . get_class($consumer) . '] finished handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		return $result;
	}
	
	/**
	 * @return string $previousVersion
	 */
	public function getPreviousVersion() 
	{
		return $this->previousVersion;
	}

}