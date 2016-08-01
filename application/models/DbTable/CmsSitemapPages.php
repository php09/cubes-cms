<?php
class Application_Model_DbTable_CmsSitemapPages extends Zend_Db_Table_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    protected $_name = 'cms_sitemap_pages';
    
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
    

    
    /**
     * 
     * @param int $id Id of the sitemapPage to delete
     */
    public function deleteSitemapPage($id) {
        $sitemapPage = $this->getSitemapPageById($id);
        
        $this->update( array(
            'order_number' => new Zend_Db_Expr('order_number - 1') 
            ), 'order_number' > $sitemapPage['order_number'] . ' AND parent_id = ' . $sitemapPage['parent_id']);
        
        $this->delete('id = ' . $id);
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
    
}