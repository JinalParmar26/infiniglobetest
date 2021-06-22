<?php
class Database{
   
    private $host = "localhost";
    private $db_name = "infiniglobe_test";
    private $username = "jinal";
    private $password = "Admin@123";
    public $conn;
    
    public function getConnection(){
   
        $this->conn = null;
   
        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
   
        return $this->conn;
    }

    
}
?>