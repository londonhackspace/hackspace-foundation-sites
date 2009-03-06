<?php
class User extends fActiveRecord {

    
    public function getMemberNumber() {
        return 'HS' . sprintf('%05d', $this->getId());
    }

    public function isMember() {
        return false;
    }
}
