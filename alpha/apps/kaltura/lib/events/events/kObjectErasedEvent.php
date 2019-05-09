<?php
/**
 * @package Core
 * @subpackage events
 */
class vObjectErasedEvent extends VidiunEvent implements IVidiunDatabaseEvent, IVidiunObjectRelatedEvent
{
    const EVENT_CONSUMER = 'vObjectErasedEventConsumer';
	
	/**
	 * @var BaseObject
	 */
	private $object;
    
	/* (non-PHPdoc)
     * @see VidiunEvent::doConsume()
     */
    protected function doConsume (VidiunEventConsumer $consumer)
    {
        if(!$consumer->shouldConsumeErasedEvent($this->object))
			return true;
			
		$additionalLog = '';
		if(method_exists($this->object, 'getId'))
			$additionalLog .= 'id [' . $this->object->getId() . ']';
			
		VidiunLog::debug('consumer [' . get_class($consumer) . '] started handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		$result = $consumer->objectErased($this->object);
		VidiunLog::debug('consumer [' . get_class($consumer) . '] finished handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		return $result;
    }

	/* (non-PHPdoc)
     * @see VidiunEvent::getConsumerInterface()
     */
    public function getConsumerInterface ()
    {
        return self::EVENT_CONSUMER;
        
    }
    
    public function __construct(BaseObject $object)
	{
		$this->object = $object;
		
		$additionalLog = '';
		if(method_exists($object, 'getId'))
			$additionalLog .= ' id [' . $object->getId() . ']';
			
		VidiunLog::debug("Event [" . get_class($this) . "] object type [" . get_class($object) . "]" . $additionalLog);
	}
	
	public function getKey()
	{
		if(method_exists($this->object, 'getId'))
			return get_class($this->object).$this->object->getId();
		
		return null;
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