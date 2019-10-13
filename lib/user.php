<?php

class User extends fActiveRecord {

    public function getMemberNumber() {
        return 'HS' . sprintf('%05d', $this->getId());
    }

    public function isMember() {
        return $this->getSubscribed() == '1';
    }

    public function isGoCardlessUser() {
        return $this->getGocardlessUser() == '1';
    }

    public function isAdmin() {
        return $this->getAdmin() == '1';
    }

    public function buildTransactions($from=null, $to=null) {
      if ($from == null){
        $from = new fDate('2009-01-01');
      }
      if ($to == null) {
        $to = new fDate('now');
      }
        return fRecordSet::build(
            'Transaction',
            array('user_id=' => $this->getId(),
            'timestamp>' => $from,
            'timestamp<' => $to),
            array('timestamp' => 'desc')
        );
    }

    public function firstTransaction($from=null, $to=null) {
      if ($from == null){
        $from = new fDate('2009-01-01');
      }
      if ($to == null) {
        $to = new fDate('now');
      }
        $result = fRecordSet::build(
            'Transaction',
            array('user_id=' => $this->getId(),
            'timestamp>' => $from,
            'timestamp<' => $to),
            array('timestamp' => 'asc')
        );
		
		return ($result->count() != 0) ? date('F Y', strtotime($result[0]->getTimestamp())) : null;
    }

    public function buildCards() {
        return fRecordSet::build(
            'Card',
            array('user_id=' => $this->getId()),
            array('added_date' => 'asc')
        );
    }

    public function buildUsersAliases() {
        $record = fRecordSet::build(
            'UsersAliase',
            array('user_id=' => $this->getId()),
            array('aliases.type' => 'asc', 'aliases.id' => 'asc')
        );
		return $record;
    }

    public function getInterests() {
        return fRecordSet::build(
            'Interest',
            array('suggested=' => 1),
            array('category' => 'asc', 'name' => 'asc')
        );
    }
	
	public function setLearnings($list) {
        global $db;
        $db->execute("DELETE FROM users_learnings WHERE user_id = %s", $this->getId());
		foreach($list as $lid) {
        	$db->execute("INSERT INTO users_learnings (user_id, learning_id) VALUES (%s, %s)", $this->getId(), $lid);
		}
	}

	public function setAliases($list) {
        global $db;
        $db->execute("DELETE FROM users_aliases WHERE user_id = %s", $this->getId());
		foreach($list as $aid=>$username) {
        	$db->execute("INSERT INTO users_aliases (user_id, alias_id, username) VALUES (%s, %s, %s)", $this->getId(), $aid, $username);
		}
	}

	public function setInterests($list) {
        global $db;
        $db->execute("DELETE FROM users_interests WHERE user_id = %s", $this->getId());
		foreach($list as $lid) {
        	$db->execute("INSERT INTO users_interests (user_id, interest_id) VALUES (%s, %s)", $this->getId(), $lid);
		}
	}

	public function addInterest($name,$category) {
        global $db;
        $db->execute("INSERT INTO interests (category,name) VALUES (%s, %s)", $category, $name);
        $record = fRecordSet::build(
            'Interest',
            array('name=' => $name, 'category=' => $category)
        );
        return $record[0]->getInterestId();
   	}

    public function getResetPasswordToken() {
        global $db;
        $db->execute("DELETE FROM password_resets WHERE expires < now()");
        $token = fCryptography::randomString(15);
        $db->execute("INSERT INTO password_resets (key, user_id, expires)
                                    VALUES (%s, %s, now() + INTERVAL '1 day')",
                                    $token, $this->getId());
        return $token;
    }
    
    public static function checkPasswordResetToken($token) {
        global $db;
        $result = $db->query("SELECT * FROM password_resets
            WHERE key = %s AND expires > now()", $token);
        if ($result->countReturnedRows() > 0) {
            $res = $result->fetchRow();
            $result = $db->execute('DELETE FROM password_resets WHERE key = %s', $token);
            return new User($res['user_id']);
        }
        return false;
    }
}
