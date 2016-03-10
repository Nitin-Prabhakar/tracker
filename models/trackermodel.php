<?php
require_once __DIR__."/database.php";

class trackerModel{

	public $db = null;
	private $aColumns = [
		"Reference",
		"Applicant",
		"Father",
		"DOB",
		"Address",
		"Deliverydate"
	];

	function __construct(){

		$this->db = database::__getDB();
		//var_dump($this->db);

	}

	function load($aTracker){


		$sColumns = implode(",", $this->aColumns);

		foreach($this->aColumns as $index=>$column){
			$aColumnBinder[] = ":{$column}";
		}

		$sBoundColumns = implode(",", $aColumnBinder);
		
		foreach ($aTracker as $index=>$aRow){
			//print_r($aRow);

			$this->db->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			try{
				$sSql = $this->db->link->prepare("INSERT INTO trackerapp ({$sColumns}) VALUES ({$sBoundColumns})");
				foreach($this->aColumns as $key=>$value){
					$sSql->bindParam(":$value", $aRow[$value]);					
				}				
				$sSql->execute();
			}catch(PDOException $e){
				echo "Error: " . $e->getMessage();
			}
		}


	}
		
}

?>