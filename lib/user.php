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

    public function getResetPasswordToken() {
        global $db;
        $db->execute("DELETE FROM password_resets WHERE expires < datetime('now')");
        $token = fCryptography::randomString(15);
        $db->execute("INSERT INTO password_resets (key, user_id, expires) 
                                    VALUES (%s, %s, datetime('now', '+1 day'))",
                                    $token, $this->getId());
        return $token;
    }

    public static function checkPasswordResetToken($token) {
        global $db;
        $result = $db->query("SELECT * FROM password_resets
            WHERE key = %s AND expires > datetime('now')", $token);
        if ($result->countReturnedRows() > 0) {
            $res = $result->fetchRow();
            $result = $db->execute('DELETE FROM password_resets WHERE key = %s', $token);
            return new User($res['user_id']);
        }
        return false;
    }
}
