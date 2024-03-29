<?php
/**
 * Auto-raised event that raised by the propel generated objects
 * This event is fired regardless of whether changes were made to the object being saved - it is fired when the object's postSave function is called.
 * @package Core
 * @subpackage events
 */
class vObjectSavedEvent extends VidiunEvent implements IVidiunDatabaseEvent, IVidiunObjectRelatedEvent
{
	const EVENT_CONSUMER = 'vObjectSavedEventConsumer';
	
	/**
	 * @var BaseObject
	 */
	private $object;
	
	/**
	 * @param BaseObject $object
	 */
	public function __construct(BaseObject $object)
	{
		$this->object = $object;
		
		$additionalLog = '';
		if(method_exists($object, 'getId'))
			$additionalLog .= ' id [' . $object->getId() . ']';
			
		VidiunLog::debug("Event [" . get_class($this) . "] object type [" . get_class($object) . "]" . $additionalLog);
	}
	
	public function getConsumerInterface()
	{
		return self::EVENT_CONSUMER;
	}
	
	/**
	 * @param vObjectSavedEventConsumer $consumer
	 * @return bool true if should continue to the next consumer
	 */
	protected function doConsume(VidiunEventConsumer $consumer)
	{
		if(!$consumer->shouldConsumeSavedEvent($this->object))
			return true;
	
		$additionalLog = '';
		if(method_exists($this->object, 'getId'))
			$additionalLog .= 'id [' . $this->object->getId() . ']';
			
		VidiunLog::debug('consumer [' . get_class($consumer) . '] started handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		$result = $consumer->objectSaved($this->object);
		VidiunLog::debug('consumer [' . get_class($consumer) . '] finished handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		return $result;
	}
	
	/**
	 * @return BaseObject $object
	 */
	public function getObject() 
	{
		return $this->object;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunEvent::getScope()
	 */
	public function getScope()
	{
		$scope = parent::getScope();
		if(method_exists($this->object, 'getPartnerId'))
			$scope->setPartnerId($this->object->getPartnerId());
			
		return $scope;
	}
}