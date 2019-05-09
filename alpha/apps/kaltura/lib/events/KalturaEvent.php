<?php
/**
 * @package Core
 * @subpackage events
 */
abstract class VidiunEvent
{
	/**
	 * @return string - name of consumer interface
	 */
	public abstract function getConsumerInterface();
	
	/**
	 * Executes the consumer
	 * @param VidiunEventConsumer $consumer
	 * @return bool true if should continue to the next consumer
	 */
	protected abstract function doConsume(VidiunEventConsumer $consumer);

	/**
	 * @param vGenericEventConsumer $consumer
	 * @return bool true if should continue to the next consumer
	 */
	protected function consumeGeneric(vGenericEventConsumer $consumer)
	{
		if(!$consumer->shouldConsumeEvent($this))
			return true;

		VidiunLog::debug('consumer [' . get_class($consumer) . '] started handling [' . get_class($this) . ']');
		$result = $consumer->consumeEvent($this);
		VidiunLog::debug('consumer [' . get_class($consumer) . '] finished handling [' . get_class($this) . ']');
		return $result;
	}
	
	/**
	 * Validate the consumer type and executes it
	 * @param VidiunEventConsumer $consumer
	 * @return bool true if should continue to the next consumer
	 */
	public final function consume(VidiunEventConsumer $consumer)
	{
		$consumerType = $this->getConsumerInterface();	
		if($consumer instanceof $consumerType)
			return $this->doConsume($consumer);	
		elseif($consumer instanceof vGenericEventConsumer)
			return $this->consumeGeneric($consumer);
			
		return true;
	}
	
	/**
	 * @return string
	 */
	public function getKey()
	{
		return null;
	}
	
	/**
	 * @return vEventScope
	 */
	public function getScope()
	{
		return new vEventScope($this);
	}
	
	/**
	 * @return int of enum EventPriority
	 */
	public function getPriority()
	{
		return EventPriority::NORMAL;
	}
}