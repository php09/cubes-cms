<?php

class Application_Form_Admin_UserAdd extends Zend_Form
{
	public function init() {
            
                $userName = new Zend_Form_Element_Text('username');
                $userName->addFilter('StringTrim')
                        ->addValidator('StringLength', false, array('min' => 3, 'max' => 50))
                        ->setRequired(TRUE)
                        ->addValidator($validator = new Zend_Validate_Db_NoRecordExists(
                            array(
                                'table' => 'cms_users',
                                'field' => 'username'
                                )
                            )
                        );

                $this->addElement($userName);
		
		$firstName = new Zend_Form_Element_Text('first_name');
		//$firstName->addFilter(new Zend_Filter_StringTrim());
		//$firstName->addValidator(new Zend_Validate_StringLength(array('min' => 3, 'max' => 255)));
		
		$firstName->addFilter('StringTrim')
			->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
			->setRequired(false);
		
		$this->addElement($firstName);
		
		$lastName = new Zend_Form_Element_Text('last_name');
		$lastName->addFilter('StringTrim')
			->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
			->setRequired(false);
		$this->addElement($lastName);
		
		$email = new Zend_Form_Element_Text('email');
		$email->addFilter('StringTrim')
			->addValidator('EmailAddress', false, array('domain' => false))
			->setRequired(false);
		$this->addElement($email);
		
	}
}