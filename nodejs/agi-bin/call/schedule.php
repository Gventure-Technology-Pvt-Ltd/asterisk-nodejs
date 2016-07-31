<?php
require_once("setting.php");
require_once("lib.db.php");
class IVR{
	private $result;
	private $agi;
	
	public function __construct($agi)
	{
		$this->agi=$agi;
		$this->agi->exec("NoOp", "IVR_constructor called");
		$this->agi->exec("Playback", PATH.welcome);
	}

	public function billing()
	{
		
		
	}
	public function tech()
	{
		
		
	}
}
?>
