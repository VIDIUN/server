<?php
/**
 * Calculate value of an object ID based on a specific context.
 * 
 * @package Core
 * @subpackage model.data
 *
 */
class vObjectIdField extends vStringField
{
	/* (non-PHPdoc)
	 * @see vStringField::getFieldValue()
	 */
	protected function getFieldValue(vScope $scope = null)
	{
		if(!$scope)
		{
			VidiunLog::info('No scope specified');
			return null;
		}
		
		if (!($scope instanceof vEventScope))
		{
			VidiunLog::info('Scope must be of type vEventScope, [' . get_class($scope) . '] given');
			return;
		}
		
		if (!($scope->getEvent()))
		{
			VidiunLog::info('$scope->getEvent() must return a value');
			return;
		}
		
		if ($scope->getEvent() && !($scope->getEvent() instanceof  IVidiunObjectRelatedEvent))
		{
			VidiunLog::info('Scope event must realize interface IVidiunObjectRelatedEvent');
			return;
		}
		
		if ($scope->getEvent() && !($scope->getEvent()->getObject()))
		{
			VidiunLog::info('Object not found on scope event');
			return;
		}
		
		if (!method_exists($scope->getEvent()->getObject(), 'getId'))
		{
			VidiunLog::info('Getter method for object id not found');
			return;
		}
		
		return $scope->getEvent()->getObject()->getId();
	}

	
}