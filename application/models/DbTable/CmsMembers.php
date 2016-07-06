<?php

class Application_Model_DbTable_CmsMembers extends Zend_Db_Table_Abstract 
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    protected $_name = 'cms_members';

    /**
     * @param int $id
     * @return null/array Associative arrao with keys as cms_members table columns or returns null
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

}
