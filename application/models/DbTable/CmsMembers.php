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
     * 
     * @param Array $member Associative array with keys as column names and values as column new values
     * @return int The id of new member (auto increment)
     */
    public function insertMember($member) {
        
        
        
        
        //insert vraca id od insertovanog membera
        $id = $this->insert($member);
        
        return $id;
    }
    
    /**
     * 
     * @param int $id Id of the member to delete
     */
    public function deleteMember($id) {
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
    

}
