<?php
include_once 'database.php';

class Crud extends Database {

    public function create($table, $data) {
        $columns = implode(",", array_keys($data));
        $values = implode("','", array_values($data));
        $sql = "INSERT INTO $table ($columns) VALUES ('$values')";
        return $this->conn->query($sql);
    }

    public function read($table, $conditions = "") {
        $sql = "SELECT * FROM $table";
        if ($conditions != "") {
            $sql .= " WHERE $conditions";
        }
        $result = $this->conn->query($sql);
        return $result;
    }

    public function update($table, $data, $conditions) {
        $updates = [];
        foreach ($data as $key => $value) {
            $updates[] = "$key='$value'";
        }
        $update_str = implode(",", $updates);
        $sql = "UPDATE $table SET $update_str WHERE $conditions";
        return $this->conn->query($sql);
    }

    public function delete($table, $conditions) {
        $sql = "DELETE FROM $table WHERE $conditions";
        return $this->conn->query($sql);
    }
}
?>
