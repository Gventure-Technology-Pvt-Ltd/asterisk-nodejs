<?php
require_once("setting.php");
require_once("lib.db.php");
require_once("socket.io.php");
class CallerID{
	private $result;
	private $agi;
        private $sql;
        private $db;
        private $nonquery;
        private $query;

	public function __construct($agi)
	{
		$this->agi=$agi;
                $this->agi->exec("NoOp", "IVR_constructor called");
                $this->db = new MysqlManager();
                $this->nonquery = new NonQuery($this->db);
                $this->query = new Query($this->db);
	}

	public function getDetail()
	{
		$r_name=rand(0,50);
		$r_city=rand(0,99);
		$names=array("Lucas Etter","Collin Burns","Patrick Ponce","Max Park","Kennan LeJeune","Kevin Costello","Drew Brads","Andrew Ricci","Anthony Brooks","Keaton Ellis","Pavan Ravindra","Phillip Lewicki","Daniel Wannamaker","Andy Denney","Daniel Karnaukh","Rowe Hessler","Andy Smith","Alex Johnson","Edward Lin","Jabari Nuruddin","Rami Sbahi","Kevin Hays","Justin Mallari","Blake Thompson","Jonah Crosby","Tanzer Balimtas","Luke Tycksen","Dana Yi","Christopher Olson","Samuel Brenner","Brandon Huang","Kavin Tangtartharakul","Riley Woo","James Hamory","Mitchell Lane","Sean Belke","Ben Yu","Gavin Wills","Phillip Espinoza","Paul Mahvi","Nick Stanton","David Ludwig","Ricardo Lutchman","Andy Huang","Nathan Soria","Ty Marshall","Benjamin Christie","Dylan Miller","Justin Adsuara","Vishantak Srikrishna");
		$city=array("New York City, New York","Los Angeles, California","Chicago, Illinois","Houston, Texas","Philadelphia, Pennsylvania","Phoenix, Arizona","San Antonio, Texas","San Diego, California","Dallas, Texas","San Jose, California","Austin, Texas","Jacksonville, Florida","Indianapolis, Indiana","San Francisco, California","Columbus, Ohio","Fort Worth, Texas","Charlotte, North Carolina","Detroit, Michigan","El Paso, Texas","Memphis, Tennessee","Boston, Massachusetts","Seattle, Washington","Denver, Colorado","Washington, DC","Nashville-Davidson, Tennessee","Baltimore, Maryland","Louisville/Jefferson, Kentucky","Portland, Oregon","Oklahoma , Oklahoma","Milwaukee, Wisconsin","Las Vegas, Nevada","Albuquerque, New Mexico","Tucson, Arizona","Fresno, California","Sacramento, California","Long Beach, California","Kansas , Missouri","Mesa, Arizona","Virginia Beach, Virginia","Atlanta, Georgia","Colorado Springs, Colorado","Raleigh, North Carolina","Omaha, Nebraska","Miami, Florida","Oakland, California","Tulsa, Oklahoma","Minneapolis, Minnesota","Cleveland, Ohio","Wichita, Kansas","Arlington, Texas","New Orleans, Louisiana","Bakersfield, California","Tampa, Florida","Honolulu, Hawaii","Anaheim, California","Aurora, Colorado","Santa Ana, California","St. Louis, Missouri","Riverside, California","Corpus Christi, Texas","Pittsburgh, Pennsylvania","Lexington-Fayette, Kentucky","Anchorage municipality, Alaska","Stockton, California","Cincinnati, Ohio","St. Paul, Minnesota","Toledo, Ohio","Newark, New Jersey","Greensboro, North Carolina","Plano, Texas","Henderson, Nevada","Lincoln, Nebraska","Buffalo, New York","Fort Wayne, Indiana","Jersey , New Jersey","Chula Vista, California","Orlando, Florida","St. Petersburg, Florida","Norfolk, Virginia","Chandler, Arizona","Laredo, Texas","Madison, Wisconsin","Durham, North Carolina","Lubbock, Texas","Winston-Salem, North Carolina","Garland, Texas","Glendale, Arizona","Hialeah, Florida","Reno, Nevada","Baton Rouge, Louisiana","Irvine, California","Chesapeake, Virginia","Irving, Texas","Scottsdale, Arizona","North Las Vegas, Nevada","Fremont, California","Gilbert town, Arizona","San Bernardino, California","Boise, Idaho","Birmingham, Alabama");
		$n=$names[$r_name];
		$tmp=explode(" ",$n);
		$ret["first"]=$tmp[0];
		$ret["last"]=$tmp[1];
		$c=$city[$r_city];
		$tmp=explode(",",$c);
		$ret["city"]=trim($tmp[0]);
		$ret["state"]=trim($tmp[1]);
		$ret["postal"]=rand(100000,1000000);
		return $ret;
	}

	public function makeLead()
	{
		$st=rand(10,100);
		$st=rand(10,100);
		$num=rand(10000,100000);
		$pnum=rand(10000,100000);
		$detail=$this->getDetail();
		$sql="INSERT INTO vicidial_list set title='Mr.', first_name='".$detail["first"]."', status='XFER', middle_initial='A', last_name='".$detail["last"]."', address1='".$st." Main St', address2='Apt ".$st."', donation='".$st."', city='".$detail["city"]."', state='".$detail["state"]."', province='', postal_code='".$detail["postal"]."', gender='', alt_phone='', email='', security_phrase='GAINBOUND', comments='Something', phone_number='973772".$pnum."', vendor_lead_code='', phone_code='1', list_id='".$num."', record_type='none', transfromip='test', owner='test';";
                $this->agi->exec("NoOp", $sql);
                $this->nonquery->raw_prepare($sql);
                $this->nonquery->execute();
		return "973772".$pnum;
	}

	public function GetQueue()
	{
		$this->sql="SELECT `id_campaign` FROM `did` WHERE `number`='".$this->agi->request["agi_extension"]."' order by `id` desc limit 1";
                $this->agi->exec("NoOp", $this->sql);
                $this->query->raw_prepare($this->sql);
                $q=$this->query->Row();
		$this->sql="SELECT `id`, `queue` FROM `campaign` WHERE `id`='".$q["id_campaign"]."' order by `id` desc limit 1";
                $this->agi->exec("NoOp", $this->sql);
                $this->query->raw_prepare($this->sql);
                $tmp=$this->query->Row();
                return $tmp;
	}

	public function GetLead($ph)
	{
		$this->sql="SELECT `lead_id` FROM `vicidial_list` WHERE `phone_number`='".$ph."' order by `lead_id` desc limit 1";
                $this->agi->exec("NoOp", $this->sql);
                $this->query->raw_prepare($this->sql);
                $tmp=$this->query->Row();
		if(isset($tmp["lead_id"]))
                	return $tmp["lead_id"];
		else
			return 0;
	}

	public function GetAgent($uqid)
	{
		$this->sql="SELECT `agentId` FROM `agent_status` WHERE `callid`='".$uqid."' limit 1";
                $this->agi->exec("NoOp", $this->sql);
                $this->query->raw_prepare($this->sql);
                $tmp=$this->query->Row();
		if(isset($tmp["agentId"]))
                	return $tmp["agentId"];
		else
			return 0;
	}

	public function AddDisposition($uqid, $ch)
	{
		$this->sql="SELECT `data2` `callerid`, `queuename` `queue`, `callid` FROM `queue_log` WHERE `callid`='".$uqid."' AND `event`='ENTERQUEUE' limit 1";
                $this->agi->exec("NoOp", $this->sql);
                $this->query->raw_prepare($this->sql);
                $data=$this->query->Row();
                $tmp=explode("-",$data["callerid"]);
		$var=$this->agi->get_variable("MEMBERINTERFACE");
		$this->agi->exec("NoOp", $var["data"]);
		$this->sql="SELECT `id`, `name` FROM `sipuser` WHERE concat('SIP/', `name`)='".$var["data"]."' limit 1";
                $this->agi->exec("NoOp", $this->sql);
                $this->query->raw_prepare($this->sql);
                $agent=$this->query->Row();
		$this->sql="SELECT *, '".$agent["name"]."' `agent`, '".$ch."' `channel` FROM `vicidial_list` WHERE `lead_id`='".$tmp["2"]."' limit 1";
                $this->agi->exec("NoOp", $this->sql);
                $this->query->raw_prepare($this->sql);
                $crm=$this->query->Row();
		$this->notifyNode($crm);
		sleep(2);
		$sql="UPDATE `agent_status` SET `channel`='".$ch."' WHERE `agentId`='".$var["data"]."';";
                $this->agi->exec("NoOp", $sql);
		$this->nonquery->raw_prepare($sql);
                $this->nonquery->execute();
		$sql="INSERT INTO `disposition` (`id_agent`, `id_lead`, `id_campaign`, `callid`, `campaign`, `disposition`) VALUES ('".$agent["id"]."', '".$tmp[2]."', '".$tmp[3]."', '".$uqid."', '".$tmp[4]."', 'UD');";
                $this->agi->exec("NoOp", $sql);
                $this->nonquery->execute();
	}
	
	public function notifyNode($data) {
		$socketio = new SocketIO();
		if ($socketio->send('localhost', 8000, 'astcrm', json_encode($data))){
    			$this->agi->exec("NoOp", 'we sent the message and disconnected');
		} 
		else 
		{
    			$this->agi->exec("NoOp", 'Sorry, we have a mistake :\'(');
		}
	}
}
?>
