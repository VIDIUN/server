<?php
/**
 * Consumer to disable caching after an object is saved.
 *
 * @package api
 * @subpackage cache
 */
class VidiunCacheDisabler implements vObjectSavedEventConsumer
{
	public function objectSaved(BaseObject $object)
	{
		VidiunResponseCacher::disableCache();
	}
	
	public function shouldConsumeSavedEvent(BaseObject $object)
	{
		return VidiunResponseCacher::isCacheEnabled();
	}
}