<?php

class Application_Form_Admin_PhotoEdit extends Zend_Form
{
	public function init() {
		
		$title = new Zend_Form_Element_Text('title');
		//$title->addFilter(new Zend_Filter_StringTrim());
		//$title->addValidator(new Zend_Validate_StringLength(array('min' => 3, 'max' => 255)));
		
		$title->addFilter('StringTrim')
			->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
			->setRequired(FALSE);
		
		$this->addElement($title);
		
		$description = new Zend_Form_Element_Textarea('description');
		$description->addFilter('StringTrim')
			->setRequired(FALSE);
		$this->addElement($description);
                
	}
}