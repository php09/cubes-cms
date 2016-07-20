<?php

class Application_Form_Admin_UserEdit extends Zend_Form
{
    protected $editedUserId;
    
    
    public function __construct($editedUserId, $options = null) {
        if( empty($editedUserId)) {
            throw new InvalidArgumentException("Edited user id cannot be empty.");
        }
        $this->editedUserId = $editedUserId;
        parent::__construct($options);
    }

    public function init() {

            $userName = new Zend_Form_Element_Text('username');
            $userName->addFilter('StringTrim')
                    ->addValidator('StringLength', false, array('min' => 3, 'max' => 50))
                    ->setRequired(TRUE)
                    ->addValidator($validator = new Zend_Validate_Db_NoRecordExists(
                        array(
                            'table' => 'cms_users',
                            'field' => 'username',
                            'exclude' => array(
                                'field' => 'id',
                                'value' => $this->editedUserId
                            )
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