<?php
/**
 * @package Core
 * @subpackage model
 */
class vCategoryIdentifier extends vObjectIdentifier
{
	/* (non-PHPdoc)
	 * @see VObjectIdentifier::retrieveByIdentifier()
	 */
	public function retrieveByIdentifier ($value, $partnerId = null)
	{
		switch ($this->identifier)
		{
			case CategoryIdentifierField::FULL_NAME:
				return categoryPeer::getByFullNameExactMatch($value, null, $partnerId);
			case CategoryIdentifierField::ID:
				return categoryPeer::retrieveByPK($value);
			case CategoryIdentifierField::REFERENCE_ID:
				$objects = categoryPeer::getByReferenceId($value);
				return $objects[0];
		}	
	}
}