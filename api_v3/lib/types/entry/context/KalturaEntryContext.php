<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunEntryContext extends VidiunContext
{
    /**
     * The entry ID in the context of which the playlist should be built
     * @var string
     */
    public $entryId;
    
    /**
     * Is this a redirected entry followup?
     * @var VidiunNullableBoolean
     */
    public $followEntryRedirect;
    
    private static $map_between_objects = array
    (
    	'followEntryRedirect',
    );
    
    public function getMapBetweenObjects()
    {
    	return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
    }
    
    /* (non-PHPdoc)
     * @see VidiunPlaylistContext::validate()
     */
    protected function validate ()
    {
        //Validate the provided entryId belongs to the partner and that it is a valid entry (status READY, etc)
        if ( !is_null($this->entryId) )
        {
	        $entry = entryPeer::retrieveByPK($this->entryId);
	        if (!$entry)
	        {
	            throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $this->entryId);
	        }
        }        
    }
    
    /* (non-PHPdoc)
     * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
     */
    public function toObject($dbObject = null, $skip = array())
    {
        $this->validate();
        if (!$dbObject)
        {
            $dbObject = new vEntryContext();
        }
        
        parent::toObject($dbObject);
        if ( !is_null($this->entryId) )
        {
        	$dbObject->setEntry(entryPeer::retrieveByPK($this->entryId));
        }

        return $dbObject;
    }
}