<?php
require_once "./models/trackermodel.php";
require_once "PHPWord.php";
class trackerController {

	private $oReader;
    public $client;
	public $sTargetFile;
	private $oModel;
	public $aPoliceVerification;
	public $aCourtVerification;
	public $aTracker;
	public $sFolder;

    function __construct($oReader){
    	$this->oReader = $oReader;
    	$this->oModel = new trackerModel;
    }

    Private function readTracker() {

    	$this->oReader->open($this->sTargetFile);
        foreach ($this->oReader->getSheetIterator() as $sheet) {
		    foreach ($sheet->getRowIterator() as $row) {

                $aRow = null;
		    	for($i=0;$i<count($row);$i++){
		    		if(gettype($row[$i])=="string"){
		    			$aRow[$i] = iconv('UTF-8', 'ASCII//TRANSLIT',$row[$i]);
		    		}else{
                        $aRow[$i] = $row[$i];
                    }
		    	}

                if($aRow!=null)
		          $rows[] = $aRow;
		    }
		}
		$this->oReader->close();
		//create named columns from rows[0] as keys

		for ($i=1;$i<count($rows);$i++){
			for($j=0;$j<count($rows[0]);$j++){
				$key= strtolower($rows[0][$j]);
				if(in_array($key, ["dob","deliverydate"])){
					if(gettype($rows[$i][$j])=="integer"){
                        //convert excel date to structured date
                            $timestamp = ($rows[$i][$j] - 25569) * 86400;
                            $namedCols[$i][$rows[0][$j]] = date("Y-m-d",$timestamp);
                    }else{
                            //throw exception
                            $namedCols[$i][$rows[0][$j]] = $rows[$i][$j];
                    }
				}
				else{
				 $namedCols[$i][$rows[0][$j]] = $rows[$i][$j];
				}

			}
		}
		$this->aTracker = $namedCols;
    }

    function readAndLoadTracker(){

    	$this->readTracker();
    	$this->loadTracker();
    }

    Private function loadTracker(){
        //echo "<pre>";
        //print_r($this->aTracker);
    	//$this->oModel->load($this->aTracker);
    }

    public function __getTracker(){
    	return $this->aTracker;
    }

    public function createBucketsForPoliceAndCourt(){

    	foreach ($this->aTracker as $key => $aColumnAssociatedArray) {
    		# code...
    		foreach($aColumnAssociatedArray as $k=>$v){

    			$sK = strtolower($k);
    			switch($sK){
                    case "reference":
                        $k1 = "Ref. No.";
                        $v1 = $v;
                        break;
    				case "name":
                        if($this->client=="PAMAC"){
                            $k1 = "Name of the Subject";
                        }
                        else{
    					    $k1 = "Applicant's name";
                        }
                        $v1 = $v;
    					break;
                    case "poc":
                        $k1 = "Period of Check";
                        $v1 = $v;
                        break;
    				case "dob":
    					$k1 = "Date of birth";
                        $timestamp = strtotime($v);
                        $v1 = date("d-M-Y",$timestamp);
    					break;
    				case "deliverydate":
                        if($this->client=="PAMAC"){
                            $k1 = "Date of Verification";
                        }
                        else{
    					    $k1 = "Date of information from police station";
                        }
                        $timestamp = strtotime($v);
                        $v1 = date("d-M-Y",$timestamp);
                        //echo $v1;
    					break;
    				default:
    					$k1 = $k;
                        $v1 = $v;
    			}
    			$this->aPoliceVerification[$key][$k1] = $v1;
    			if(!in_array($sK, ["reference","deliverydate"]) && $this->client=="A-Check"){
    				$this->aCourtVerification[$key][$k1] = $v1;
    			}
    		}
            if($this->client=="A-Check"){
        		$this->aPoliceVerification[$key]["Police station"] = "";
        		$this->aPoliceVerification[$key]["Ph no of Police station"] = "";
        		$this->aPoliceVerification[$key]["Designation of the interviewed police officer"] = "SHO (Station House Officer)";
        		$this->aPoliceVerification[$key]["Number of years covered in the police verification"] = "Last 2 years";
        		$this->aPoliceVerification[$key]["Verification remarks"] = "No records";
            }
    	}

    }

    public function writeFromTracker($key){

    	$oPHPWord = new PHPWord();
    	$nameOfFile = $this->sFolder.$this->aTracker[$key]['Name'].uniqid().'.docx';



    	// Define table style arrays
		$styleTable = array('borderSize'=>6, 'borderColor'=>'000');
        $styleTableSectionHeaders = array('borderSize'=>0, 'borderColor'=>'white');
		$oPHPWord->addTableStyle('customStyledTable', $styleTable);
        $oPHPWord->addTableStyle('customStyledSectionHeaders', $styleTableSectionHeaders);

		$oPHPWord->addParagraphStyle('pStyle', array('align'=>'left', 'size'=>11, 'spaceAfter'=>1, 'name'=>'Calibri', 'bold'=>true));
        $oPHPWord->addFontStyle('rStyle', array('bold'=>true,'size'=>11));
        $oPHPWord->addParagraphStyle('pHeaderStyle', array('align'=>'center','size'=>11, 'spaceAfter'=>100));

		//Header

		$section = $oPHPWord->createSection();
    	$header = $section->createHeader();
		$table = $header->addTable();
		$table->addRow();
		$table->addCell(10000)->addImage('header.jpg', array('width'=>640, 'height'=>100, 'align'=>'left'));

		//-------------End Header-----------------------

        if($this->client=="A-Check"){
    		$section->addText('To',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));
            $section->addText('M/s A-Check Global',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));
        }
        if($this->client=="PAMAC"){
            $section->addText('To Whomsoever it may concern',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));
        }
        $section->addText('This information is given with regard to the check conducted for:',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));


        //Police Verification


        if($this->client=="A-Check"){
            $section->addText("Police Verification",'rStyle', 'pHeaderStyle');
        }


        $table = $section->addTable('customStyledTable');

        foreach($this->aPoliceVerification[$key] as $index=>$value){
        	$table->addRow();
			$table->addCell(5000)->addText(" {$index}",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
			$table->addCell(5000)->addText(" {$value}",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
        }

        //------------------End Police Verification--------------------------------

        if($this->client=="A-Check"){
         //Court Verification
            $section->addText("Court Verification",'rStyle', 'pHeaderStyle');

            $table = $section->addTable('customStyledTable');

            foreach($this->aCourtVerification[$key] as $index=>$value){
            	$table->addRow();
    			$table->addCell(5000)->addText(" {$index}",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
    			$table->addCell(5000)->addText(" {$value}",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
            }
        }

        //------------------End Court Verification--------------------------------


        //Result

        $section->addText("Result",'rStyle', 'pHeaderStyle');

        if($this->client=="A-Check"){

    		$table = $section->addTable('customStyledTable');

    		$table->addRow();
    		$table->addCell(5000)->addText("Court",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
    		$table->addCell(5000)->addText("Jurisdiction",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
    		$table->addCell(5000)->addText("Location",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
    		$table->addCell(5000)->addText("Verification remarks",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));

    		$table->addRow();
    		$table->addCell(5000)->addText(" Magistrate");
    		$table->addCell(5000)->addText(" Metropolitan Magistrate / Judicial Magistrate ");
    		$table->addCell(5000)->addText(" ---");
    		$table->addCell(5000)->addText(" No records");
        }
        if($this->client=="PAMAC"){


            $section->addText('Civil Proceedings: Original Suit / Miscellaneous Suit /Execution / Arbitration Case','rStyle','pHeaderStyle');

            $table = $section->addTable('customStyledTable');

            $table->addRow();
            $table->addCell(5000)->addText("Court",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
            $table->addCell(5000)->addText("Jurisdiction",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
            $table->addCell(5000)->addText("Name of Court",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
            $table->addCell(5000)->addText("Result",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));

            $table->addRow();
            $table->addCell(5000)->addText(" Civil Court");
            $table->addCell(5000)->addText(" ");
            $table->addCell(5000)->addText(" City Civil Court");
            $table->addCell(5000)->addText(" No records");

            $table->addRow();
            $table->addCell(5000)->addText(" High Court");
            $table->addCell(5000)->addText(" ");
            $table->addCell(5000)->addText(" High Court ");
            $table->addCell(5000)->addText(" No records");


             $section->addText('Criminal Proceedings: Criminal Petitions / Criminal Appeal / Sessions Case /Special Sessions Case / Criminal Miscellaneous Petition / Criminal Revision Appeal','rStyle','pHeaderStyle');


            $table = $section->addTable('customStyledTable');

            $table->addRow();
            $table->addCell(3000)->addText("Court",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
            $table->addCell(7000)->addText("Jurisdiction",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
            $table->addCell(7000)->addText("Name of Court",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
            $table->addCell(3000)->addText("Result",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));

            $table->addRow();
            $table->addCell(3000)->addText(" Magistrate Court");
            $table->addCell(7000)->addText(" ");
            $table->addCell(7000)->addText(" Criminal Cases(CC), Private Complaint Report (PCR) ");
            $table->addCell(3000)->addText(" No records");

            $table->addRow();
            $table->addCell(3000)->addText(" Sessions Court");
            $table->addCell(7000)->addText(" ");
            $table->addCell(7000)->addText(" Criminal Appeals");
            $table->addCell(3000)->addText(" No records");

            $table->addRow();
            $table->addCell(3000)->addText(" High Court");
            $table->addCell(7000)->addText(" ");
            $table->addCell(7000)->addText(" Criminal Appeals");
            $table->addCell(3000)->addText(" No records");


        }

        //-----------------------------End result---------_------------



        //Disclaimer
        if($this->client=="A-Check"){
    		$section->addText("On line verification :Verified online litigation database and found none matching with the provided applicant's details",null, 'pStyle');

            $section->addText('Conclusion: In conclusion,as on the date of this search, and as on the records of jurisdictional courts there  is  no Civil or criminal case instituted against the subject .This report is based on the verbal confirmation of the concerned  court /police authority, having  jurisdiction over the police station  within  Whose  limits  the candidate  is said  to be  residing  as  upon the date on which it is confirmed. Hence this information is subjective',null,'pStyle');

            $section->addText('Disclaimer:Due care has been taken in conducting the search. The records are public records and theabove search has been  conducted  on behalf of your good self,as per your instruction and at your request & the undersigned is not responsible for any errors, omissions or deletions, if any ,in  the said court / police records. Please note that this is an information not a certificate', null, 'pStyle');
        }
        if($this->client=="PAMAC"){
            $section->addText('Conclusion: In conclusion, as on the date of this search, and as on the records of jurisdictional courts there is no Civil or criminal case instituted against the subject. The above search results are based on the registers of first information reports, in respect of criminal cases maintained in the above mentioned court /police station having jurisdiction over the police stations within whose limits the candidate is said to be residing. This report is based on the verbal confirmation given by the concerned authorities.',null,'pStyle');

            $section->addText('Disclaimer: Due care has been taken in conducting the search. The records are public records and the above search has been conducted on behalf of your good self, as per your instruction and at your request & the undersigned is not responsible for any errors, omissions or deletions,if any ,in the said court /police records .', null, 'pStyle');
        }


        //---------------------------_------End Disclaimer--------------------------_-


        //footer
        $styleTable = array('borderSize'=>2, 'borderColor'=>'000000');
        $footer = $section->createFooter();
        $table = $footer->addTable('footerTable',$styleTable);
		$table->addRow();
        $table->addCell(10000)->addImage('signature.png', array('width'=>180, 'height'=>180, 'align'=>'left'));


        //----------------------------End Footer---------------------------------------

		$objWriter = PHPWord_IOFactory::createWriter($oPHPWord, 'Word2007');
		$objWriter->save($nameOfFile);
		chmod($nameOfFile, 0777);

    }
}


?>