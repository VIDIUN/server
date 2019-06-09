<?php
/**
 * Represents the current session user e-mail address context 
 * @package Core
 * @subpackage model.data
 */
class vUserEmailContextField extends vStringField
{
	/* (non-PHPdoc)
	 * @see vStringField::getFieldValue()
	 */
	protected function getFieldValue(vScope $scope = null) 
	{
		if(!$scope)
			$scope = new vScope();
			
		$vuser = vuserPeer::getVuserByPartnerAndUid($scope->getVs()->partner_id, $scope->getVs()->user);
		return $vuser->getEmail();
	}
}