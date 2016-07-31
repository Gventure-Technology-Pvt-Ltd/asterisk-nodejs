<?php
abstract class Database
{
	protected $data;
	protected $link;

    public abstract function __set($key,$val);
    
    public abstract function __get($key);
    
    public abstract function Open();

    public abstract function Close();

    public abstract function Execute($sql);

	public abstract function Row($sql);

    public abstract function NonExecute($sql);
}

class MysqlManager extends Database
{
	//This getter setter for username, password, host, database, link, last_id
	public function __construct()
	{
		$this->data["username"]=DATABASE_USER;
		$this->data["password"]=DATABASE_PASS;
		$this->data["host"]=DATABASE_HOST;
		$this->data["port"]=DATABASE_PORT;
		$this->data["database"]=DATABASE_NAME;
		$this->data["last"]=0;
	}

	public function __set($key, $val)
	{
		$key=strtolower($key);
		if($key=="username" || $key=="password" || $key=="host" || $key=="database" || $key=="port")
			$this->data[$key]=$val;
		else
			$this->logger->error("Invalid variable is setting");
	}

	public function __get($key)
	{
		$key=strtolower($key);
		if($key == "username" || $key == "password" || $key == "host" || $key == "database" || $key == "last" || $key=="port")
			return $this->data[$key];
		else
			return;
	}

    public function Open()
    {
        if (is_null($this->data["database"])) 
			echo("MySQL database not selected");
        if (is_null($this->data["host"])) 
			echo("MySQL hostname not set");
		if($this->data["port"]=="3306" || $this->data["port"]=="0")
				$this->link = mysql_connect($this->data["host"], $this->data["username"], $this->data["password"]); 
		else
	        $this->link = mysql_connect($this->data["host"].":".$this->data["port"], $this->data["username"], $this->data["password"]); 

        if ($this->link === false) 
			echo("Could not connect to database(".$this->data["host"]."). Check your username(".$this->data["username"]."), password(".$this->data["password"].") and database port (".$this->data["port"].") then try again. Mysql error ".mysql_errno()." : ".mysql_error()."");
			
        if (!mysql_select_db($this->data["database"], $this->link)) { 
			echo("Could not select database(".$this->data["database"].")");
		}
    }

    public function Close()
    {
        mysql_close($this->link); 
        $this->link = null;
    }

    public function Execute($sql)
    {
        if ($this->link === false) 
		{ 
			echo("No Database Connection Found. Mysql error ".mysql_errno()." : ".mysql_error().""); 
        } 

		$result = mysql_query($sql, $this->link); 
        if ($result === false)
		{ 
			echo("Mysql query error ".mysql_errno()." : ".mysql_error().""); 
            return NULL;
        } 

		if(mysql_num_rows($result)>0)
		{
			$count=0;
			while($rows=mysql_fetch_row($result))
			{
				$data[$count++]=$rows;
			}
		}
	
		if(isset($data))
			return $data; 
		else
			return NULL; 
    }

	public function Row($sql)
	{
        if ($this->link === false) { 
			echo("No Database Connection Found. Mysql error ".mysql_errno()." : ".mysql_error().""); 
        } 

		$result = mysql_query($sql, $this->link); 
        if ($result === false) { 
			echo("Mysql query error ".mysql_errno()." : ".mysql_error().""); 
            return NULL;
        } 

		$data=$row = mysql_fetch_assoc($result);
		
		if(isset($data))
			return $data; 
		else
		{
			echo("Null Data return");
			return NULL; 
		}
	}

    public function NonExecute($sql)
    {
		if ($this->link === false) { 
            echo("No Database Connection Found. Mysql error ".mysql_errno()." : ".mysql_error().""); 
        } 

        $result = mysql_query($sql, $this->link); 
        if ($result === false) { 
            return false;
        } 
		else
			$this->data["last"] = mysql_insert_id(); 

        return $result;
    }

	public function status($values)
	{
		$sql="SELECT `status` FROM `user` WHERE `username`='".$values["filter"]."'";
		$this->logger->debug($sql);
		if ($this->link === false) { 
            echo("No Database Connection Found. Mysql error ".mysql_errno()." : ".mysql_error().""); 
        } 

		$result = mysql_query($sql, $this->link); 
        if ($result === false) { 
            return NULL;
        } 

        if(mysql_num_rows($result)>0){
			$count=0;
			while($rows=mysql_fetch_row($result)){
				$data[$count++]=$rows;
			}
		}
		if(isset($data))
			return $data; 
		else
			return NULL; 
	}
}


class NonQuery
{
	private $connection;
	private $data;
	private $sql;

	public function NonQuery($database)
	{
		$this->connection=$database;
	}

	public function __set($key,$val)
	{
		if(strlen($key)>0)
			$this->data[$key]=$val;
		else
			echo( "Invalid variable is setting");
	}

	public function __get($key)
	{
		$key=strtolower($key);
		if($key == "last" || $key=="status")
			return $this->data[$key];
		else
			return;
	}
	
	//Type = INSERT/UPDATE/DELETE
	//Filter = Which selected set will be operated
	public function prepare()
	{
		$tmp1="";
		$tmp2="";
		$tmp3="";
		
		if(isset($this->data["_table"]))
		{
			foreach($this->data as $key=>$val)
			{
				if($key != "_table" && $key != "_type" && $key != "filter"){
					if(strtoupper($this->data["_type"])=="INSERT")
					{
						$tmp1.="`".$key."`,";
						$tmp2.="'".$val."',";
					}
					else
						$tmp1.="`".$key."`='".$val."',";
				}
			}

			if(isset($tmp1))
				$tmp1=substr($tmp1,0,-1);
			if(isset($tmp2))
				$tmp2=substr($tmp2,0,-1);

			if(isset($this->data["filter"]))
			{
				foreach($this->data["filter"] as $key=>$val)
				{
					$tmp3.="`".$key."`='".$val."' and ";
				}
			}

			if(isset($tmp2))
				$tmp3=substr($tmp3,0,-4);
			
			switch(strtoupper($this->data["_type"]))
			{
				case 'INSERT':
					$this->sql="INSERT INTO `".$this->data["_table"]."` (".$tmp1.") VALUES (".$tmp2.")";
					break;
				case 'UPDATE':
					$this->sql="UPDATE `".$this->data["_table"]."` SET ".$tmp1." WHERE (".$tmp3.")";
					break;
				case 'DELETE':
					$this->sql="DELETE FROM `".$this->data["_table"]."` WHERE (".$tmp3.")";
					break;
				case 'STATUS':
				{
					
					$this->sql="UPDATE FROM `".$this->data["_table"]."` WHERE (".$tmp3.")";
					break;
				}
			}
		}
	}

	public function raw_prepare($sql)
	{
		$this->sql=$sql;
	}

	public function execute()
	{
		$this->connection->Open();
		$this->connection->NonExecute($this->sql);
		if(strtoupper($this->data["_type"])=="INSERT")
		{
			$this->data["last"]=$this->connection->last;
		}
		$this->connection->Close();
	}
}

class Query
{
	private $connection;
	private $data;
	private $sql;

	public function Query($database)
	{
		$this->connection=$database;
	}

	public function __set($key,$val)
	{
		if(strlen($key)>0)
			$this->data[strtolower($key)]=$val;
		else
			$this->logger->error( "Invalid variable is setting");
	}

	public function __get($key)
	{
		if(strtolower($key)=="status")
			return $this->status($this->data["filter"]);
		return $this->data[strtolower($key)];
	}
	
	//Filter = Which selected set will be operated
	public function prepare()
	{
		$tmp1="";
		$tmp3="";
		if(isset($this->data["table"]))
		{
			foreach($this->data as $key=>$val)
			{
				if(substr($key,0,5) == "field"){
					$tmp1.="`".$val."`,";
				}
			}

			if(isset($tmp1))
				$tmp1=substr($tmp1,0,-1);

			if(isset($this->data["filter"]))
			{
				foreach($this->data["filter"] as $key=>$val)
				{
					$tmp3.="`".$key."`='".$val."' and ";
				}
			}

			$tmp3=substr($tmp3,0,-4);
			if($this->data["filter"]!=null)
				$this->sql="SELECT ".$tmp1." FROM `".$this->data["table"]."` WHERE ".$tmp3;
			else
				$this->sql="SELECT ".$tmp1." FROM `".$this->data["table"]."`";

			if(isset($this->data["order"]))
			{
				$this->sql=$this->sql." order by ".$this->data["order"];
			}

			if(isset($this->data["group"]))
			{
				$this->sql=$this->sql." group by ".$this->data["group"];
			}

			if(isset($this->data["limit"]))
			{
				$this->sql=$this->sql." limit ".$this->data["limit"];
			}
		}
	}

	public function raw_prepare($sql)
	{
		$this->sql=$sql;
	}

	public function Execute()
	{
		$this->connection->Open();
		$data=$this->connection->Execute($this->sql);
		$this->connection->Close();
		return $data;
	}

	public function Row()
	{
		$this->connection->Open();
		$data=$this->connection->Row($this->sql);
		$this->connection->Close();
		return $data;
	}
}
?>
