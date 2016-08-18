<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initRouter() {
        
        $this->bootstrap('db');
        
        $sitemapPageTypes = array(
            'StaticPage' => array(
                'title' => 'Static page',
                'subtypes' => array(
                    'StaticPage' => 0
                )
            ),
            'AboutUsPage' => array(
                'title' => 'About us',
                'subtypes' => array()
                )
            ,
            'ServicesPage' => array(
                'title' => 'Services',
                'subtypes' => array()
            ),
            'ContactPage' => array(
                'title' => 'Contact page',
                'subtypes' => array()
            )
        );
        
        $rootSitemapPageTypes = array(
            'StaticPage' => 0,
            'AboutUsPage' => 1,
            'ServicesPage' => 1,
            'ContactPage' => 1
        );
        
        Zend_Registry::set('sitemapPageTypes', $sitemapPageTypes);
        Zend_Registry::set('rootSitemapPageTypes', $rootSitemapPageTypes);
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $router instanceof Zend_Controller_Router_Rewrite;
        $router->addRoute(
                'about-us', 
                new Zend_Controller_Router_Route_Static(        
                    'about-us',
                    array(
                        'controller' => 'aboutus',
                        'action' => 'index'
                        )
                    )
                )
//                ->addRoute(
//                        'member-route',
//                        new Zend_Controller_Router_Route(
//                            'about-us/member/:id/:member_slug',
//                            array(
//                                'controller' => 'aboutus',
//                                'action' => 'member',
//                                'member_slug' => ''
//                            )
//                        ))
//                ->addRoute(
//                        'contact-us',
//                        new Zend_Controller_Router_Route_Static(
//                                'contact-us',
//                                array(
//                                    'controller' => 'contact',
//                                    'action' => 'index'
//                                )
//                            )
//                        )
                ->addRoute( 'ask-member',
                        new Zend_Controller_Router_Route(
                            'contact-us/ask-member/:id/:member_slug',
                            array(
                                'controller' => 'contact',
                                'action' => 'askmember',
                                'member_slug' => ''
                            )
                        ));
        
        $sitemapPagesMap = Application_Model_DbTable_CmsSitemapPages::getSitemapPagesMap();
//        
//        print_r($sitemapPagesMap);
//        die();
        foreach($sitemapPagesMap AS $sitemapPageId => $sitemapPageMap) {
            
            if($sitemapPageMap['type'] == 'StaticPage') {
                
                $router->addRoute(
                'static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(        
                    $sitemapPageMap['url'],
                    array(
                        'controller' => 'staticpage',
                        'action' => 'index',
                        'sitemap_page_id' => $sitemapPageId
                        )
                    )
                );
            }
            
            if($sitemapPageMap['type'] == 'AboutUsPage') {
                
                $router->addRoute(
                'static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(        
                    $sitemapPageMap['url'],
                    array(
                        'controller' => 'aboutus',
                        'action' => 'index',
                        'sitemap_page_id' => $sitemapPageId
                        )
                    )
                );
                
                $router->addRoute( 'member-route', new Zend_Controller_Router_Route(
                            $sitemapPageMap['url'] . '/member/:id/:member_slug',
                            array(
                                'controller' => 'aboutus',
                                'action' => 'member',
                                'member_slug' => ''
                                )
                            )
                        );
                
            }
            
            if($sitemapPageMap['type'] == 'ContactPage') {
                
                $router->addRoute(
                'static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(        
                    $sitemapPageMap['url'],
                    array(
                        'controller' => 'contact',
                        'action' => 'index',
                        'sitemap_page_id' => $sitemapPageId
                        )
                    )
                );
                
                $router->addRoute( 'ask-member',
                        new Zend_Controller_Router_Route(
                            $sitemapPageMap['url'] . '/ask-member/:id/:member_slug',
                            array(
                                'controller' => 'contact',
                                'action' => 'askmember',
                                'member_slug' => ''
                            )
                        ));
                
            }
            
            
            if($sitemapPageMap['type'] == 'ServicesPage') {
                $router->addRoute(
                'static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(        
                    $sitemapPageMap['url'],
                    array(
                        'controller' => 'services',
                        'action' => 'index',
                        'sitemap_page_id' => $sitemapPageId
                        )
                    )
                );
            }
            
                
        }
        
    }

}

