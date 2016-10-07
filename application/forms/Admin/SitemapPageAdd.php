<?php

class Application_Form_Admin_SitemapPageAdd extends Zend_Form
{
    
    protected $parentId;
    protected $parentType;
    
    public function __construct($parentId, $parentType, $options = null) {
        $this->parentId = $parentId;
        $this->parentType = $parentType;
        parent::__construct($options);
    }

    
    public function init() {
        
        $sitemapPageTypes = Zend_Registry::get("sitemapPageTypes");
        $rootSitemapPages = Zend_Registry::get('rootSitemapPageTypes');
        
        if($this->parentId == 0) {
            $parentSubtypes = $rootSitemapPages;
        } else {
            $parentSubtypes = $sitemapPageTypes[$this->parentType]['subtypes'];
        }
        
        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
        
        $parentSubtypesCount = $cmsSitemapPagesDbTable->countByTypes(array('parent_id' => $this->parentId));
        
        
        
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
//                ->addMultiOptions(array(
//                    'StaticPage' => 'Static page',
//                    'AboutUsPage' => 'About us page',
//                    'ContactPage'=> 'Contact page'
//                ))
                ->setRequired(TRUE);
        
        //foreach($sitemapPageTypes as $sitemapPageType => $sitemapPageTypeProperties) {
        foreach($parentSubtypes as $sitemapPageType => $sitemapPageTypeMax) {
            
            $sitemapPageTypeProperties = $sitemapPageTypes[$sitemapPageType];
            
            $totalExistingSitemapPagesType = isset($parentSubtypesCount[$sitemapPageType]) ? $parentSubtypesCount[$sitemapPageType] : 0 ;
            
            if($sitemapPageTypeMax == 0 || $sitemapPageTypeMax > $totalExistingSitemapPagesType) {
                $type->addMultiOption($sitemapPageType, $sitemapPageTypeProperties['title']);
            }
            
        }
        
        $this->addElement($type);
        
        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages;
        
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
        
        $csrftoken = new Zend_Form_Element_Hash("csrf_token");
        $this->addElement($csrftoken);
        
        
    }
}