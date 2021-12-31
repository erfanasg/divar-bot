<?php

header("Content-Type: text/html;charset=UTF-8");
class DB
{
    private $link;
    private $db_host;
    private $db_username;
    private $db_password;
    private $db_name;
    public function __construct($db_host, $db_username, $db_password, $db_name)
    {
        $this->db_host = $db_host;
        $this->db_username = $db_username;
        $this->db_password = $db_password;
        $this->db_name = $db_name;
        
    }
    
    public function Connect()
    {
        $this->link = mysqli_connect($this->db_host, $this->db_username,$this->db_password, $this->db_name);
        $this->link->set_charset("utf8");
    }
    
    public function createUser($telegram_id)
    {
        $query = "insert into users(telegram_id) values('$telegram_id');";
        return mysqli_query($this->link, $query);
    }
    
    public function getUserInfo($telegram_id)
    {
        $query = "select * from users where telegram_id='$telegram_id'";
        $result = mysqli_query($this->link, $query);
        if($result == false)
            return false;
        return mysqli_fetch_assoc($result);
    }
    
    public function SetFirstname($telegram_id, $firstname)
    {
        $query = "update users set firstname='$firstname' where telegram_id='$telegram_id'";
        return mysqli_query($this->link, $query);
    }
    
    public function SetLastname($telegram_id, $lastname)
    {
        $query = "update users set lastname='$lastname' where telegram_id='$telegram_id'";
        return mysqli_query($this->link, $query);
    }
    
    public function SetPhone($telegram_id, $phone_number)
    {
        $query = "update users set phone_number='$phone_number' where telegram_id='$telegram_id'";
        return mysqli_query($this->link, $query);
    }
    
    public function SetAd($telegram_id, $value)
    {
        $query = "update users set ad_status='$value' where telegram_id='$telegram_id'";
        return mysqli_query($this->link, $query);
    }
    
    public function GetAd($telegram_id)
    {
        $query = "select ad_status from users where telegram_id='$telegram_id'";
        $result = mysqli_query($this->link, $query);
        if($result == false)
            return false;
        return mysqli_fetch_assoc($result)['ad_status'];
    }
    
    public function userExists($telegram_id)
    {
        $query = "select * from users where telegram_id='$telegram_id';";
        $result = mysqli_query($this->link, $query);
        if(mysqli_fetch_assoc($result))
            return true;
        return false;
    }
    
    public function SendAdvertise($telegram_id, $title)
    {
        $phone_number = $this->getUserInfo($telegram_id)['phone_number'];
        $query = "insert into advertises(owner,title,phone_number) values('$telegram_id', '$title', '$phone_number'); ";
        return mysqli_query($this->link, $query);
    }
    
    public function SetAdText($telegram_id, $text)
    {
        $query = "update advertises set text='$text' where owner='$telegram_id' and text='none'; ";
        return mysqli_query($this->link, $query);
    }
    
    public function getAdvertises()
    {
        $query = "select * from advertises;";
        $result = mysqli_query($this->link, $query);
        $advertises = array();
        
        while($row = mysqli_fetch_assoc($result))
            $advertises[] = $row;
        
        return $advertises;
    }
    
    public function getUserAdvertises($telegram_id)
    {
        $query = "select * from advertises where owner='$telegram_id';";
        $result = mysqli_query($this->link, $query);
        $advertises = array();
        
        while($row = mysqli_fetch_assoc($result))
            $advices[] = $row;
        
        return $advertises;
    }

}
?>