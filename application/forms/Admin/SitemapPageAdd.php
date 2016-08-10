<?php

class Application_Form_Admin_SitemapPageAdd extends Zend_Form
{
    
    protected $parentId;
    
    public function __construct($parentId, $options = null) {
        $this->parentId = $parentId;
        parent::__construct($options);
    }

    
    public function init() {
        
        /*
         * type
         * url slug
         * short title
         * title
         * description
         * body
         */
        
        //Zend_Form_Element_Select          //  \
        //Zend_Form_Element_Multiselect     //   ) -  zajednicko im je definisanje niza mogucih vrednosti koje mogu da se nadju u elementu
        //Zend_Form_Element_Multicheckbox   //  /
        
        $type = new Zend_Form_Element_Select('type');
        //multioptions $type->setMultiOptions(array('value1' => 'label1', 'value2' => 'label2'))
        //obicno select elementi imaju prazan value
        //set f-ja brise prethodno stanje
//        $type->setMultiOptions( array(
//            '' => '-- select type --'  // ovo nece raditi
//        ));
        $type->addMultiOption('', '-- Select Sitemap Page Type --')
                ->addMultiOptions(array(
                    'StaticPage' => 'Static page',
                    'AboutUsPage' => 'About us page',
                    'ContactPage'=> 'Contact page'
                ))
                ->setRequired(TRUE);
        
        $this->addElement($type);
        
        
        
        $urlSlug = new Zend_Form_Element_Text('url_slug');
        $urlSlug->addFilter('StringTrim')
                ->addFilter(new Application_Model_Filter_UrlSlug)
                ->addValidator('StringLength', false, array('min' => 2, 'max' => 255))
                ->addValidator($validator = new Zend_Validate_Db_NoRecordExists(
                        array(
                            'table' => 'cms_sitemap_pages',
                            'field' => 'url_slug',
                            'exclude' => 'parent_id = ' . $this->parentId
                                )
                            )
                        )
                ->setRequired(TRUE);
        
        $this->addElement($urlSlug);
        
        
        
        $shortTitle = new Zend_Form_Element_Text('short_title');
        $shortTitle->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min' => 2, 'max' => 255))
                ->setRequired(TRUE)->addValidator($validator);
        
        $this->addElement($shortTitle);
        
        
        
        $title = new Zend_Form_Element_Text('title');
        $title->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min' => 2, 'max' => 500))
                ->setRequired(TRUE);
        
        $this->addElement($title);
        
        
        
        $description = new Zend_Form_Element_Textarea('description');
        $description->addFilter('StringTrim')->setRequired(FALSE);
        
        $this->addElement($description);
        
        
        
        $body = new Zend_Form_Element_Textarea('body');
        $body->setRequired(FALSE);
        
        $this->addElement($body);
        
        
        
        
    }
}