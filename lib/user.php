<?php
class User extends fActiveRecord {

    public function getMemberNumber() {
        return 'HS' . sprintf('%05d', $this->getId());
    }

    public function isMember() {
        return $this->getSubscribed();
    }

    public function buildTransactions() {
        return fRecordSet::build(
            'Transaction',
            array('user_id=' => $this->getId()),
            array('timestamp' => 'asc')
        );
    }
}
