<?php

class Admin_SitemapController extends Zend_Controller_Action
{
    public function indexAction() {
        
        $request = $this->getRequest();
        
        $flashMessenger = $this->getHelper('FlashMessenger');
		
        $systemMessages = array(

                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors')
        );
        
        $this->view->systemMessages = $systemMessages;
        
        $id = (int) $request->getParam('id', 0);
        
        if($id < 0) {
            throw new Zend_Controller_Router_Exception('Invalid parent id for sitemap pages.', 404);
        }
        
        $cmsSitemapPages = new Application_Model_DbTable_CmsSitemapPages();
        
        $sitemapPage = $cmsSitemapPages->getSitemapPageById($id);
        
        if( !$sitemapPage && $id != 0) {
            throw new Zend_Controller_Router_Exception('No sitemap page is found for sitemap pages.', 404);
        }
        
        $childSitemapPages = $cmsSitemapPages->search(
                array(
                    'filters' => array('parent_id' => $id),
                    'orders' => array('order_number' => 'ASC'),
        //            'limit' => '50',
        //            'page' => '3'
              ) 
            );
        
        $sitemapPagesBreadcrumbs = $cmsSitemapPages->getSitemapPageBreadcrumbs($id);
        
        $this->view->sitemapPages = $childSitemapPages;
        $this->view->sitemapPagesBreadcrumbs = $sitemapPagesBreadcrumbs;
    }
    
}