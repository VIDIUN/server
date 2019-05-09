<?php
/**
 * @package Admin
 * @subpackage Partners
 */
class Form_Delivery_UrlTokenizerChinaCache extends Form_Delivery_DeliveryProfileTokenizer
{
	public function init()
	{
		parent::init();
		
		$type = new Vidiun_Form_Element_EnumSelect('algorithmId', array('enum' => 'Vidiun_Client_Enum_ChinaCacheAlgorithmType'));
		$type->setLabel('Algorithm ID:');
		$this->addElements(array($type));	
		
		$this->addElement('text', 'keyId', array(
				'label'			=> 'Key ID:',
				'validators'	=> array('Int'),
		));
	}
}
