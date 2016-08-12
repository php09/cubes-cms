<?php
class Application_Model_DbTable_CmsSitemapPages extends Zend_Db_Table_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    protected $_name = 'cms_sitemap_pages';
    
    protected static $sitemapPagesMap;
    
    /**
     * 
     * @return array with keys as sitemap page ids and values as associative array with keys url and type
     */
    public static function getSitemapPagesMap() { //primer lazy loading metoda
        
        if( !self::$sitemapPagesMap ) {
            
            $sitemapPagesMap = array();

            $cmsSitemapPagesDbTable = new self(); // self == Application_Model_DbTable_CmsSitemapPages();

            $sitemapPages = $cmsSitemapPagesDbTable->search( 
                    array(
                        'orders' => array(
                            'parent_id' => 'ASC', 'order_number' => 'ASC'
                            )
                        )
                    );

            foreach($sitemapPages as $sitemapPage) {
                $type = $sitemapPage['type'];
                $url = $sitemapPage['url_slug'];

                if(isset($sitemapPagesMap[$sitemapPage['parent_id']])) {
                    $url = $sitemapPagesMap[$sitemapPage['parent_id']]['url'] . '/' . $url;
                }
                
                $sitemapPagesMap[$sitemapPage['id']] = array('url' => $url, 'type' => $type);
            }
            
            
                    
            self::$sitemapPagesMap = $sitemapPagesMap;
        }
        
        return self::$sitemapPagesMap;
        
    }
    
    /**
     * @param int $id
     * @return null/array Associative array with keys as cms_sitemap_pages table columns or returns null
     */
    public function getSitemapPageById($id) {

        $select = $this->select();
        $select->where('id = ?', $id);

        $row = $this->fetchRow($select);
        
        if( $row instanceof Zend_Db_Table_Row ) {
            return $row->toArray();
        } else {
            return null;
        }
    }
    
        /**
     * 
     * @param int $id
     * @param array $user Associative array with keys as column names and values as column new values
     */
    public function updateSitemapPage($id, $sitemapPage) {
        
        if( isset($sitemapPage['id']) ) {
            unset($sitemapPage['id']);
        }
        
        $this->update($sitemapPage, 'id = ' . $id);
        
    }
        
    /**
     * Function returns following sql query:
     * SELECT MAX(order_number) AS max FROM `cms_sitemap_pages`
     * @return int Maximum of the order_number column
     */
    public function getMaxOrderNumber() {
        
        $max = $this->select();
        $max->from($this, new Zend_Db_Expr('MAX(order_number) AS max'));
        $max = $this->fetchRow($max);     
        
        return $max['max'];
    }
    
    
    /**
     * 
     * @param Array $sitemapPage Associative array with keys as column names and values as column new values
     * @return int The id of new sitemapPage (auto increment)
     */
    public function insertSitemapPage($sitemapPage) {

        $select = $this->select();
        
        $select->where('parent_id = ? ', $sitemapPage['parent_id'])
                ->order('order_number DESC');

        $sitemapPageWithBiggestOrderNumber = $this->fetchRow($select);
        
        if($sitemapPageWithBiggestOrderNumber instanceof Zend_Db_Table_Row) {
            $sitemapPage['order_number'] = $sitemapPageWithBiggestOrderNumber['order_number'] + 1;
        } else {
            $sitemapPage['order_number'] = 1;
        }
        
        //insert vraca id od insertovanog sitemapPagea
        $id = $this->insert($sitemapPage);
        
        return $id;
    }
    

    
//    /**
//     * 
//     * @param int $id Id of the sitemapPage to delete
//     */
//    public function deleteSitemapPage($id) {
//        $sitemapPage = $this->getSitemapPageById($id);
//        
//        $this->update( array(
//            'order_number' => new Zend_Db_Expr('order_number - 1') 
//            ), 'order_number' > $sitemapPage['order_number'] . ' AND parent_id = ' . $sitemapPage['parent_id']);
//        
//        $this->delete('id = ' . $id);
//    }
    
        /**
     * 
     * @param int $id Id of the sitemapPage to delete
     */
    public function deleteSitemapPage($id, $pozvanoVisePuta = FALSE) {
        $sitemapPage = $this->getSitemapPageById($id);
       
//        $select = $this->select();
//        $select->where('parent_id = ?', $sitemapPage['id']);
//        $result = $this->fetchAll($select)->toArray();
        
        $result = $this->search(array('filters' => array('parent_id' => $id)));
        
        if($pozvanoVisePuta === FALSE) {
            $this->update( 
                    array(
                        'order_number' => new Zend_Db_Expr('order_number - 1')
                        ), 'parent_id = ' . $sitemapPage['parent_id'] . ' AND order_number > ' . $sitemapPage['order_number']
                    );
        }
        
//        if( !empty($result) ) {
            foreach($result as $page) {
                $this->deleteSitemapPage($page['id'], TRUE);
            }
//        }
        $this->delete('id = ' . $sitemapPage['id']);
    }
    
    
    /**
     * 
     * @param int $id Id of the sitemapPage to enagle
     */
    public function enableSitemapPage($id) {
        $this->update(array('status' => self::STATUS_ENABLED), 'id = ' . $id);
    }
    
    /**
     * 
     * @param int $id Id of the sitemapPage to disable
     */
    public function disableSitemapPage($id) {
        $this->update(array('status' => self::STATUS_DISABLED), 'id = ' . $id);
    }
    
    public function updateOrderOfSitemapPage($sortedIds) {
        foreach($sortedIds as $orderNumber=>$id) {
            $this->update(array('order_number' => $orderNumber + 1), 'id = ' . $id);
        }
    }
    
    /**
     * Array parameters is keeping search parameters
     * Array parameters must be  in following format:
     *      array(
     *          "filters" => array(
     *                  "status" => 1,
     *                  "id= => array(3, 8 ,11),
     *                  "orders" => array(
     *                                  "username" => ASC, 
     *                                  "lastname" => DESC,
     *                                   ),
     *                  "limit" => 50, //limit result set to 50 rows
     *                  "page" => 3 //start from page 3, if no limit is set, page is ignored
     *                  )
     * @param array $parameters Associative array with keys filters, orders, limit and page
     */
    public function search(array $parameters = array() ) {
        $select = $this->select();
        
        if(isset($parameters['filters'])) {
            $filters = $parameters['filters'];
            
            $this->processFilters($filters, $select);
            
            
        }
        
        if(isset($parameters['orders'])) {
            $orders = $parameters['orders'];
            
            foreach($orders AS $field => $orderDirection) {
                
                switch($field) {
                    case 'id':
                    case 'short_title':
                    case 'url_slug':
                    case 'title':
                    case 'parent_id':
                    case 'type':
                    case 'order_number':
                    case 'status':
                         if($orderDirection === 'DESC') {
                             $select->order($field . ' DESC ');
                         } else {
                             $select->order($field);
                         }
                        break;
                }
                
            }
            
        }
        
        if(isset($parameters['limit'])) {
            
            if(isset($parameters['page'])) {
                //pagination is set, do limit by page
                $select->limitPage($parameters['page'], $parameters['limit']);
            } else {
                //page is not set, just do regular
                $select->limit($parameters['limit']);
            }
            
        }
        return $this->fetchAll($select)->toArray();
    }
    
    /**
     * 
     * @param array $filters see function search $parameters['fields']
     * @return int Count of rows that match $filters
     */
    public function count( array $filters = array()) {
        $select = $this->select();
        
        $this->processFilters($filters, $select);
        
        $select->reset('columns');
        $select->from( $this->_name, 'COUNT(*) AS total');
        
        $row = $this->fetchRow($select)->total;
        
        return $row;
    }
    
    
    /**
     * fill $select object with WHERE conditions
     * @param array $filters
     * @param Zend_Db_Select $select
     */
    protected function processFilters(array $filters = array(), Zend_Db_Select $select) {
    
        //selected object will be modified outside this function
        //objects are always passed by reference
        
        foreach($filters as $field => $value) {
                
//                if($field == 'id') {
//                    if(is_array($value)) {
//                        $select->where('id IN ( ? )', $value);
//                    } else {
//                        $select->where('id = ?', $value);
//                    }
//                }
                
                
                switch($field) {
                    case 'id':
                    case 'short_title':
                    case 'url_slug':
                    case 'title':
                    case 'parent_id':
                    case 'type':
                    case 'order_number':
                    case 'status':
                        
                        if(is_array($value)) {
                            $select->where( $field . ' IN (?) ', $value);
                        } else {
                            $select->where( $field . ' = ? ' , $value);
                        }
                        break;
                    case 'short_title_search':
                        $select->where('short_title LIKE ?', '%' . $value . '%');
                        break;
                    case 'title_search':
                        $select->where('title LIKE ?', '%' . $value . '%');
                        break;
                    case 'description_search':
                        $select->where('description LIKE ?', '%' . $value . '%');
                        break;
                    case 'body_search':
                        $select->where('body LIKE ?', '%' . $value . '%');
                        break;
                    case 'id_exclude':
                        if(is_array($value)) {
                            $select->where('id NOT IN (?)', $value);
                        } else {
                            $select->where('id != ?', $value);
                        }    
                        break;
                    
                }
                
            }
    }
    
    /**
     * 
     * @param int $id
     * @return array Sitemap page rows on path
     */
    public function getSitemapPageBreadcrumbs($id) {
        
        $sitemapPagesBreadcrumbs = array();
        
        while($id > 0) {
            
            $sitemapPageInPath = $this->getSitemapPageById($id);
            
            if($sitemapPageInPath) {
                $id = $sitemapPageInPath['parent_id'];
                array_unshift($sitemapPagesBreadcrumbs, $sitemapPageInPath);
            } else {
                $id = 0;
            }
        }
        
        
            
        return $sitemapPagesBreadcrumbs;
    }
    
    
    /**
     * Returns count by type, example:
     * array(
     *  'StaticPage' => 1,
     *  'AboutUsPage' => 2,
     *  'ContactPage' => 1
     * )
     * @param type $filters
     * @return array count by type
     */
    public function countByTypes($filters = array()) {
        
        $select = $this->select();
        
        $this->processFilters($filters, $select);
        
        $select->reset('columns');
        $select->from( $this->_name, array('type', 'COUNT(*) as totalByType'))
                ->group('type');
        
        $rows = $this->fetchAll($select);
        
        $countByTypes = array();
        
        foreach($rows AS $row) {
            $countByTypes[$row['type']] = $row['totalByType'];
        }
        
        return $countByTypes;
        
    }
    
}