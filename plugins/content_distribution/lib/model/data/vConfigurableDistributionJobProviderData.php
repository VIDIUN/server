<?php
/**
 * @package plugins.contentDistribution
 * @subpackage model.data
 */
abstract class vConfigurableDistributionJobProviderData extends vDistributionJobProviderData
{
	/**
	 * @var array
	 */
	public $fieldValues;
	
	
	/**
     * @return the $fieldValues
     */
    public function getFieldValues ()
    {
        return $this->fieldValues;
    }

	/**
     * @param array $fieldValues
     */
    public function setFieldValues ($fieldValues)
    {
        $this->fieldValues = $fieldValues;
    }
    
}
