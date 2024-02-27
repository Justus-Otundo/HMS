<?php
/**
 * Created by PhpStorm.
 * User: troot
 * Date: 1/1/15
 * Time: 10:55 PM
 */

namespace dbPlayer;

class dbPlayer {

    private $db_host = "localhost";
    private $db_name = "hms";
    private $db_user = "root";
    private $db_pass = "";
    protected $con;

      public function __construct() {
        $this->open(); // Call the open method to establish connection
    }

    public function open() {
        $this->con = new \mysqli($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
        if ($this->con->connect_errno) {
            return "Connection failed: " . $this->con->connect_error;
        } else {
            return true;
        }
    }
    

    public function close() {
        $this->con->close();
        return "true";
    }
     public function login($loginId, $password) {
        $userPass = md5("hms2015" . $password);
        $query = "SELECT loginId, userGroupId, password, name, userId FROM users WHERE loginId = ? AND password = ?";
        
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("ss", $loginId, $userPass);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $info = $result->fetch_assoc();
            return $info;
        } else {
            return false; // Login failed
        }
    }
    public function insertData($table, $data) {
        $keys = "`" . implode("`, `", array_keys($data)) . "`";
        $values = "'" . implode("', '", $data) . "'";
        $query = "INSERT INTO `$table` ($keys) VALUES ($values)";
        if ($this->con->query($query)) {
            return $this->con->insert_id;
        } else {
            return $this->con->error;
        }
    }

    public function registration($query, $query2) {
        if ($this->con->query($query)) {
            if ($this->con->query($query2)) {
                return "true";
            } else {
                return $this->con->error;
            }
        } else {
            return $this->con->error;
        }
    }

    public function getData($query) {
        $result = $this->con->query($query);
        if (!$result) {
            return "Can't get data " . $this->con->error;
        } else {
            return $result;
        }
    }

    public function update($query) {
        if ($this->con->query($query)) {
            return "true";
        } else {
            return "Can't update data " . $this->con->error;
        }
    }

    public function updateData($table, $conColumn, $conValue, $data) {
        $updates = array();
        foreach ($data as $key => $value) {
            $value = $this->con->real_escape_string($value);
            $value = "'" . $value . "'";
            $updates[] = "$key = $value";
        }
        $implodeArray = implode(', ', $updates);
        $query = "UPDATE $table SET $implodeArray WHERE $conColumn = '$conValue'";
        if ($this->con->query($query)) {
            return "true";
        } else {
            return "Can't Update data " . $this->con->error;
        }
    }

    public function delete($query) {
        if ($this->con->query($query)) {
            return "true";
        } else {
            return "Can't delete data " . $this->con->error;
        }
    }

    public function getAutoId($prefix) {
        $uId = "";
        $q = "SELECT number FROM auto_id WHERE prefix = '$prefix'";
        $result = $this->getData($q);
        $userId = array();
        while ($row = $result->fetch_assoc()) {
            array_push($userId, $row['number']);
        }

        if (strlen($userId[0]) >= 1) {
            $uId = $prefix . "00" . $userId[0];
        } elseif (strlen($userId[0]) == 2) {
            $uId = $prefix . "0" . $userId[0];
        } else {
            $uId = $prefix . $userId[0];
        }
        array_push($userId, $uId);
        return $userId;
    }

    public function updateAutoId($value, $prefix) {
        $id = intval($value) + 1;
        $query = "UPDATE auto_id SET number = $id WHERE prefix = '$prefix'";
        return $this->update($query);
    }

    public function execNonQuery($query) {
        if ($this->con->query($query)) {
            return "true";
        } else {
            return "Can't Execute Query" . $this->con->error;
        }
    }

    public function execDataTable($query) {
        $result = $this->con->query($query);
        if (!$result) {
            return "Can't Execute Query" . $this->con->error;
        } else {
            return $result;
        }
    }
}
