<?php

/**
 * Interface vObjectReadyForElasticIndexEventConsumer
 */
interface vObjectReadyForElasticIndexEventConsumer extends VidiunEventConsumer
{
    /**
     * @param $object
     * @param $params
     * @return bool true if should continue to the next consumer
     */
    public function objectReadyForElasticIndex($object ,$params = null);

    /**
     * @param $object
     * @param $params
     * @return bool true if the consumer should handle the event
     */
    public function shouldConsumeReadyForElasticIndexEvent($object, $params = null);
}
