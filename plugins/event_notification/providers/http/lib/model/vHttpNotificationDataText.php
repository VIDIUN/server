<?php
/**
 * @package plugins.httpNotification
 * @subpackage model.data
 */
class vHttpNotificationDataText extends vHttpNotificationData
{
	/**
	 * @var vStringValue
	 */
	protected $content;
	
	/**
	 * Contains the calculated data to be sent
	 * @var string
	 */
	protected $data;
	
	/**
	 * @return vStringValue $content
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @param vStringValue $content
	 */
	public function setContent(vStringValue $content)
	{
		$this->content = $content;
	}
	
	/* (non-PHPdoc)
	 * @see vHttpNotificationData::setScope()
	 */
	public function setScope(vScope $scope)
	{
		if($this->content instanceof vStringField)
			$this->content->setScope($scope);
			
		$this->data = $this->content->getValue();
		
		$replace = $scope->getDynamicValues('{', '}');
		$search = array_keys($replace);
		$this->data = str_replace($search, $replace, $this->data);
	}
	
	/**
	 * Returns the calculated data
	 * @return string
	 */
	public function getData() 
	{
		return $this->data;
	}	
}