<?php
/**
 * Consume any type of event
 * @package Core
 * @subpackage events
 */
interface vGenericEventConsumer extends VidiunEventConsumer
{
	/**
	 * @param VidiunEvent $event
	 * @return bool true if should continue to the next consumer
	 */
	public function consumeEvent(VidiunEvent $event);
	
	/**
	 * @param VidiunEvent $event
	 * @return bool true if the consumer should handle the event
	 */
	public function shouldConsumeEvent(VidiunEvent $event);
}