<?php

class StaticpageController extends Zend_Controller_Action
{
    public function indexAction() {
        
        
        $request = $this->getRequest();
        
        $sitemapPageId = (int) $request->getParam('sitemap_page_id');
        
        if($sitemapPageId <= 0) {
            throw new Zend_Controller_Router_Exception('Invalid parent id ' . $sitemapPageId . ' for sitemap pages.', 404);
        }
        
        $cmsSitemapPageDbTable = new Application_Model_DbTable_CmsSitemapPages();
        
        $sitemapPage = $cmsSitemapPageDbTable->getSitemapPageById($sitemapPageId);
        
        if(!$sitemapPage) {
            throw new Zend_Controller_Router_Exception('No id found for sitemap page id ' . $sitemapPageId, 404);
        }
        
        if(
            $sitemapPage['status'] == Application_Model_DbTable_CmsSitemapPages::STATUS_DISABLED
            && !Zend_Auth::getInstance()->hasIdentity()
        ) {
            throw new Zend_Controller_Router_Exception('Sitemap page is disabled.' ,404);
        }
        
        $this->view->sitemapPage = $sitemapPage;
        
        
        $sitemapPageLinks = $cmsSitemapPageDbTable->search(
                array(
                    "filters" => array(
                        "parent_id" => $sitemapPage["parent_id"],
                        "status" => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
                        "type" => "StaticPage"
                        )
                    )
                );
        
        $this->view->sitemapPageLinks = $sitemapPageLinks;
        
        $sitemapPageChildLinks = $cmsSitemapPageDbTable->search(
                array(
                    "filters" => array("parent_id" => $sitemapPage["id"],
                    "status" => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED)
                ));
        
        $this->view->sitemapPageChildLinks = $sitemapPageChildLinks;
        
        
    }
}