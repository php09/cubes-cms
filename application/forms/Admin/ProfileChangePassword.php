<?php


class Application_Form_Admin_ProfileChangePassword extends Zend_Form
{
    public function init() {
        
        $newPassword = new Zend_Form_Element_Password('new_password');
        $newPassword->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
                ->setRequired(TRUE);
        
        $this->addElement($newPassword);
        
        $confirmPassword = new Zend_Form_Element_Password('new_password_confirm');
        $confirmPassword->addValidator('Identical', false, array('token' => 'new_password'))
                ->setRequired(TRUE);
        
        $this->addElement($confirmPassword);
        
    }
}



