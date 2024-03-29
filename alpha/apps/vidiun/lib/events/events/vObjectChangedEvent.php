<?php
/**
 * Auto-raised event that raised by the propel generated objects
 * @package Core
 * @subpackage events
 */
class vObjectChangedEvent extends VidiunEvent implements IVidiunDatabaseEvent, IVidiunObjectRelatedEvent
{
	const CUSTOM_DATA_OLD_VALUES = 'CUSTOM_DATA';
	const EVENT_CONSUMER = 'vObjectChangedEventConsumer';
	
	/**
	 * @var BaseObject
	 */
	private $object;
	
	/**
	 * @var array
	 */
	private $modifiedColumns;
	
	/**
	 * @param BaseObject $object
	 */
	public function __construct(BaseObject $object, array $modifiedColumns)
	{
		$this->object = $object;
		$this->modifiedColumns = $modifiedColumns;
		
		$additionalLog = '';
		if(method_exists($object, 'getId'))
			$additionalLog .= 'id [' . $object->getId() . ']';
			
		VidiunLog::debug("Event [" . get_class($this) . "] object type [" . get_class($object) . "] $additionalLog modified columns [" . print_r($modifiedColumns, true) . "]");
	}
	
	public function getConsumerInterface()
	{
		return self::EVENT_CONSUMER;
	}
	
	/**
	 * @param vObjectChangedEventConsumer $consumer
	 * @return bool true if should continue to the next consumer
	 */
	protected function doConsume(VidiunEventConsumer $consumer)
	{
		if(!$consumer->shouldConsumeChangedEvent($this->object, $this->modifiedColumns))
			return true;
			
		$additionalLog = '';
		if(method_exists($this->object, 'getId'))
			$additionalLog .= 'id [' . $this->object->getId() . ']';
			
		VidiunLog::debug('consumer [' . get_class($consumer) . '] started handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		$result = $consumer->objectChanged($this->object, $this->modifiedColumns);
		VidiunLog::debug('consumer [' . get_class($consumer) . '] finished handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		return $result;
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
	
	/**
	 * @return array $object
	 */
	public function getModifiedColumns() 
	{
		return $this->modifiedColumns;
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

	public function isCustomDataModified($name = null, $namespace = '')
	{
		if(isset($this->modifiedColumns[self::CUSTOM_DATA_OLD_VALUES][$namespace]) && (is_null($name) || array_key_exists($name, $this->modifiedColumns[self::CUSTOM_DATA_OLD_VALUES][$namespace])))
		{
			return true;
		}
		
		return false;
	}
	
	public function getCustomDataOldValue($name = null, $namespace = '')
	{
		if(isset($this->modifiedColumns[self::CUSTOM_DATA_OLD_VALUES][$namespace]) && (is_null($name) || array_key_exists($name, $this->modifiedColumns[self::CUSTOM_DATA_OLD_VALUES][$namespace])))
		{
			return $this->modifiedColumns[self::CUSTOM_DATA_OLD_VALUES][$namespace][$name];
		}
		
		return null;
	}
}