<?php 
/**
 * @package plugins.dropFolder
 * @subpackage Admin
 */
class Form_CreateDropFolder extends Infra_Form
{
	public function init()
	{
		$this->setAttrib('id', 'frmCreateDropFolder');
		$this->setDecorators(array(
			'FormElements', 
			array('HtmlTag', array('tag' => 'fieldset')),
			array('Form', array('class' => 'simple')),
		));
		
		$this->addElement('text', 'newPartnerId', array(
			'label'			=> 'Publisher ID:',
			'onkeypress'	=> "return supressFormSubmit(event)",
			'filters'		=> array('StringTrim'),
		));	

		$newDropFolderType = new Vidiun_Form_Element_EnumSelect('newDropFolderType', array(
			'enum' => 'Vidiun_Client_DropFolder_Enum_DropFolderType',
			'excludes' => array(
				Vidiun_Client_DropFolder_Enum_DropFolderType::S3,
				Vidiun_Client_DropFolder_Enum_DropFolderType::SCP,
			)
		));
		
		$newDropFolderType->setLabel('Type:');
		$newDropFolderType->setRequired(true);
		$this->addElement($newDropFolderType);
				
		// submit button
		$this->addElement('button', 'submit', array(
			'ignore'	=> true,
			'label'		=> 'Create New',
			'onclick'		=> "newDropFolder($('#newPartnerId').val(), $('#newDropFolderType').val())",
			'decorators'	=> array('ViewHelper'),
		));
	}
}