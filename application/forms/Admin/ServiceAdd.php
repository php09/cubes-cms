<?php

class Application_Form_Admin_ServiceAdd extends Zend_Form {
    
    public function init() {
        
        $serviceTitle = new Zend_Form_Element_Text('title');
        $serviceTitle->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min' => 2, 'max' => 255))
                ->setRequired(TRUE);
        $this->addElement($serviceTitle);
        
        $serviceIcon = new Zend_Form_Element_Text('icon');
        $serviceIcon->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min' => 2, 'max' => 255))
                ->setRequired(TRUE);
        $this->addElement($serviceIcon);
        
        $serviceDescription = new Zend_Form_Element_Textarea('description');
        $serviceDescription->addFilter('StringTrim')
                ->setRequired(TRUE);
        $this->addElement($serviceDescription);
        
        
    }
    
}