<?php

class Zend_View_Helper_PhotoGalleryUrl  extends Zend_View_Helper_Abstract
{
    //originalno kako je uradjeno:
    /*
	public function photoGalleryUrl($photoGallery) {
            return $this->view->url(array(
                'id' => $photoGallery['id'],
                'photo_gallery_slug' => $photoGallery['title']
            ), 'photo-gallery-route', true);
	}
      */  
        
    //videti https://github.com/aleksandar-dimic/cubes-cms/blob/master/application/views/helpers/PhotoGalleryUrl.php
    
    protected $urlSlugFilter;

    protected function getUrlSlugFilter() {

            /*** Lazy Loading ***/

            if (!$this->urlSlugFilter) {
                    $this->urlSlugFilter = new Application_Model_Filter_UrlSlug();
            }

            return $this->urlSlugFilter;
    }

    public function photoGalleryUrl($photoGallery) {

            $urlSlugFilter = $this->getUrlSlugFilter();

            return $this->view->url(array(
                    'id' => $photoGallery['id'],
                    'photo_gallery_slug' => $urlSlugFilter->filter($photoGallery['title'])

            ), 'photo-gallery-route', true);
    }

}