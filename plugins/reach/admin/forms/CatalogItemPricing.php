<?php
/**
 * @package plugins.reach
 * @subpackage Admin
 */
class Form_VendorCatalogItemPricing extends ConfigureSubForm
{
	public function init()
	{
 		$this->setName("catalogItemPricing");
		$this->addElement('text', 'pricePerUnit', array(
			'label'			=> 'Price Per Unit*:',
			'required'		=> true,
			'validators'	=> array('Float'),
		));
		
		$priceFunction = new Vidiun_Form_Element_EnumSelect('priceFunction', array('enum' => 'Vidiun_Client_Reach_Enum_VendorCatalogItemPriceFunction'));
		$priceFunction->setRequired(true);
		$priceFunction->setLabel("Price Function*:");
		$priceFunction->setValue(Vidiun_Client_Reach_Enum_VendorCatalogItemPriceFunction::PRICE_PER_MINUTE);
		$this->addElement($priceFunction);
	}
	
	
	public function populateFromObject($pricing)
	{
		if ($pricing)
		{
			$this->setDefault('pricePerUnit', $pricing->pricePerUnit);
			$this->setDefault('priceFunction', $pricing->priceFunction);
		}
		else
		{
			$this->setDefault('pricePerUnit', 'N\A');
			$this->setDefault('priceFunction', 'N\A');
		}
	}
	
	public function getObject($properties) 
	{
		$pricingObject = new Vidiun_Client_Reach_Type_VendorCatalogItemPricing();
		$pricingObject->pricePerUnit = $properties['pricePerUnit'];
		$pricingObject->priceFunction = $properties['priceFunction'];
		return $pricingObject;
	}
}