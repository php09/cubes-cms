<?php

class IndexController extends Zend_Controller_Action
{
    
    public function init()
    {
        /* Initialize action controller here */
    }

    /* old indexAction
    public function indexAction()
    {

//              $cmsClientsDbTable = new Application_Model_DbTable_CmsClients();
//		$select = $cmsClientsDbTable->select();
//		$select->where('status = ?', Application_Model_DbTable_CmsClients::STATUS_ENABLED)->order('order_number');
//		$clients = $cmsClientsDbTable->fetchAll($select);
//		$this->view->clients = $clients;
        
        $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();
        $slides = $cmsIndexSlidesTable->search(
                array(
                    'filters' => array('status' => Application_Model_DbTable_CmsIndexSlides::STATUS_ENABLED),
                    'orders' => array( 'order_number' => 'ASC')
                    )
                );
        $this->view->slides = $slides;
        
        
        $cmsServicesTAble = new Application_Model_DbTable_CmsServices();
        $services = $cmsServicesTAble->search(
                array(
                    'filters' => array('status' => Application_Model_DbTable_CmsServices::STATUS_ENABLED),
                    'orders' => array('order_number' => 'ASC'),
                    'limit' => 4
                ));
        $this->view->services = $services;
        
        
        //preuzeto sa: https://github.com/aleksandar-dimic/cubes-cms/blob/master/application/controllers/IndexController.php#L25
        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
		
        $servicesSitemapPages = $cmsSitemapPagesDbTable->search(array(
                'filters' => array(
                        'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
                        'type' => 'ServicesPage'
                ),
                'limit' => 1
        ));
        $servicesSitemapPage = !empty($servicesSitemapPages) ? $servicesSitemapPages[0] : null;
        
        $this->view->servicesSitemapPage = $servicesSitemapPage;
        
        /* 
         * u Application/Bootstrap.php, u $rootSitemapPageTypes varijabli navedeno ukupno koliko
         * stranica tipa Services moze da bude (jedna jedina takva stranica)
         * proverava se da li u sajtmapi ima unesena stranica takvog tipa (tako sto se izvuce i proveri rezultat)
         * 
         * U Bootstrap.php namesten je routing da baca na ServicesController, index akciju (tu je sva ona logika od prethodnog
         * puta za prikaz SVIH usluga
         * 
         * U prezentacionoj logici indexController-a, namesta se link view all (services) da baca na tu stranicu
         */
        
    //}
    
	public function testAction()
	{
		
	}
        
        
    //videti https://github.com/aleksandar-dimic/cubes-cms/blob/master/application/controllers/IndexController.php
        
    public function indexAction() {
        $cmsIndexSlidesDbTable = new Application_Model_DbTable_CmsIndexSlides();

        $indexSlides = $cmsIndexSlidesDbTable->search(array(
                'filters' => array(
                        'status' => Application_Model_DbTable_CmsIndexSlides::STATUS_ENABLED
                ),
                'orders' => array(
                        'order_number' => 'ASC'
                )
        ));


        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();

        $servicesSitemapPages = $cmsSitemapPagesDbTable->search(array(
                'filters' => array(
                        'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
                        'type' => 'ServicesPage'
                ),
                'limit' => 1
        ));
        $servicesSitemapPage = !empty($servicesSitemapPages) ? $servicesSitemapPages[0] : null;

        $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();
        $services = $cmsServicesDbTable->search(array(
                'filters' => array(
                        'status' => Application_Model_DbTable_CmsServices::STATUS_ENABLED
                ),
                'orders' => array(
                        'order_number' => 'ASC'
                ),
                'limit' => 4
        ));

        $photoGalleriesSitemapPages = $cmsSitemapPagesDbTable->search(array(
                'filters' => array(
                        'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
                        'type' => 'PhotoGalleriesPage'
                ),
                'limit' => 1
        ));
        $photoGalleriesSitemapPage = !empty($photoGalleriesSitemapPages) ? $photoGalleriesSitemapPages[0] : null;

        $cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();
        $photoGalleries = $cmsPhotoGalleriesDbTable->search(array(
                'filters' => array(
                        'status' => Application_Model_DbTable_CmsServices::STATUS_ENABLED
                ),
                'orders' => array(
                        'order_number' => 'ASC'
                ),
                'limit' => 3
        ));

        $this->view->indexSlides = $indexSlides;
        $this->view->servicesSitemapPage = $servicesSitemapPage;
        $this->view->services = $services;
        $this->view->photoGalleriesSitemapPage = $photoGalleriesSitemapPage;
        $this->view->photoGalleries = $photoGalleries;
    }
        
        
}

