<?php
/**
 * @package Core
 * @subpackage model
 */
class vEntryIdentifier extends vObjectIdentifier
{
	/* (non-PHPdoc)
	 * @see VObjectIdentifier::retrieveByIdentifier()
	 */
	public function retrieveByIdentifier($value, $partnerId = null)
	{
		switch ($this->identifier)
		{
			case EntryIdentifierField::ID:
				return entryPeer::retrieveByPK($value);
			case EntryIdentifierField::REFERENCE_ID:
				return entryPeer::retrieveByReferenceId($value);
				
		}
		
	}
}