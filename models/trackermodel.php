<?php
require_once __DIR__."/database.php";

class trackerModel{

	public $db = null;
	private $aColumns = [
		"Client",
		"Reference",
		"Customer-id",
		"Applicant",
		"Father",
		"DOB",
		"Address",
		"Contact",
		"Deliverydate"
	];
	private $aRelatives = [
		"Client"=>[
						"table"=>"Clients",
						"key"=>"client"
				  ]
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
				$sSql = $this->db->link->prepare("INSERT INTO trackers ({$sColumns}) VALUES ({$sBoundColumns})");
				foreach($this->aColumns as $key=>$value){
					if(!isset($aRow[$value])){
						$aRow[$value] = null;
					}
					if(array_key_exists($value, $this->aRelatives)){
						$tab = $this->aRelatives[$value]['table'];
						$col = $this->aRelatives[$value]['key'];
						//$sFkQuery =
					}
					$sSql->bindParam(":$value", $aRow[$value]);
				}
				//$sSql->execute();
			}catch(PDOException $e){

				echo "Error: " . $e->getMessage();
			}
		}


	}

}

?>