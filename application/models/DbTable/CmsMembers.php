<?php

class Application_Model_DbTable_CmsMembers extends Zend_Db_Table_Abstract 
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    protected $_name = 'cms_members';

    /**
     * @param int $id
     * @return null/array Associative array with keys as cms_members table columns or returns null
     */
    public function getMemberById($id) {

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
    public function updateMember($id, $member) {
        
        if( isset($member['id']) ) {
            unset($member['id']);
        }
        
        $this->update($member, 'id = ' . $id);
        
    }
        
    /**
     * Function returns following sql query:
     * SELECT MAX(order_number) AS max FROM `cms_members`
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
     * @param Array $member Associative array with keys as column names and values as column new values
     * @return int The id of new member (auto increment)
     */
    public function insertMember($member) {
//        $member['order_number'] = $this->getMaxOrderNumber() + 1;
        
//        $this->select()->order('order_number DESC')
//                ->limit(1);
        
        $select = $this->select();
        
        $select->order('order_number DESC');

        $memberWithBiggestOrderNumber = $this->fetchRow($select);
        
        if($memberWithBiggestOrderNumber instanceof Zend_Db_Table_Row) {
            $member['order_number'] = $memberWithBiggestOrderNumber['order_number'] + 1;
        } else {
            $member['order_number'] = 1;
        }
        
        //insert vraca id od insertovanog membera
        $id = $this->insert($member);
        
        return $id;
    }
    
//    /**
//     * 
//     * @param int $id Id of the member to delete
//     */
//    public function deleteMember($id, $order) {
//        $this->delete('id = ' . $id);        
//        $this->update( array( 'order_number' => new Zend_Db_Expr('order_number - 1')), 'order_number > ' . $order);
//    }
    
    /**
     * 
     * @param int $id Id of the member to delete
     */
    public function deleteMember($id) {
        $member = $this->getMemberById($id);
        
        $this->update( array(
            'order_number' => new Zend_Db_Expr('order_number - 1') 
            ), 'order_number' > $member['order_number']);
        
        $memberPhotoFilePath = PUBLIC_PATH . '/uploads/members/' . $id . '.jpg';
        if(is_file($memberPhotoFilePath)) {
            unlink($memberPhotoFilePath);
        }
        
        $this->delete('id = ' . $id);
    }
    
    /**
     * 
     * @param int $id Id of the member to enagle
     */
    public function enableMember($id) {
        $this->update(array('status' => self::STATUS_ENABLED), 'id = ' . $id);
    }
    
    /**
     * 
     * @param int $id Id of the member to disable
     */
    public function disableMember($id) {
        $this->update(array('status' => self::STATUS_DISABLED), 'id = ' . $id);
    }
    
    public function updateOrderOfMember($sortedIds) {
        foreach($sortedIds as $orderNumber=>$id) {
            $this->update(array('order_number' => $orderNumber + 1), 'id = ' . $id);
        }
    }

    public function totalNumberOfMembers() {
        $select = $this->select()->from($this, new Zend_Db_Expr('COUNT(id) as total'));
        $total = $this->fetchRow($select);
        return $total['total'];
    }
    
    public function numberOfActiveMembers() {
        $select = $this->select()->from($this, new Zend_Db_Expr('COUNT(id) as active'))->where('status = ' . self::STATUS_ENABLED);
        $active = $this->fetchRow($select);
        return $active['active'];
    }
    

}
