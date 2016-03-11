<?php
require_once "./models/trackermodel.php";
require_once "PHPWord.php";
class trackerController { 
    
	private $oReader;
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

		    	for($i=0;$i<count($row);$i++){
		    		if(gettype($row[$i])=="string"){
		    			$row[$i] = iconv('UTF-8', 'ASCII//TRANSLIT',$row[$i]);
		    		}
		    	}
		        $rows[] = $row;
		    }
		}
		$this->oReader->close();
		//create named columns from rows[0]
        //echo "<pre>";
        //print_r($rows);

		for ($i=1;$i<count($rows);$i++){
			for($j=0;$j<count($rows[0]);$j++){
				//echo $rows[$i][$j];
				$key= strtolower($rows[0][$j]);
				//echo $key."\n";
				if(in_array($key, ["dob","deliverydate"])){
					if(gettype($rows[$i][$j])=="integer"){
                            $timestamp = ($rows[$i][$j] - 25569) * 86400;                   
                            $namedCols[$i][$rows[0][$j]] = date("Y-m-d",$timestamp);
                    }else{
                            //throw exception
                            //$namedCols[$i][$rows[0][$j]] = $rows[$i][$j];
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
    	//print_r($this->aTracker);

    	$this->oModel->load($this->aTracker);
    }

    public function __getTracker(){
    	return $this->aTracker;
    }

    public function createBucketsForPoliceAndCourt(){

    	foreach ($this->aTracker as $key => $aColumnAssociatedArray) {
    		# code...
    		foreach($aColumnAssociatedArray as $k=>$v){
    			$k1=null;
    			$sK = strtolower($k);
    			switch($sK){
    				case "reference":
    					$k1 = "Ref. No";
                        $v1 = $v;
    					break;
    				case "applicant":
    					$k1 = "Applicant's name";
                        $v1 = $v;
    					break;
    				case "father":
    					$k1 = "Father's name";
                        $v1 = $v;
    					break;
    				case "dob":
    					$k1 = "Date of birth";
                        $timestamp = strtotime($v);
                        $v1 = date("d-M-Y",$timestamp);
    					break;
    				case "deliverydate":
    					$k1 = "Date of information from police station";
                        $timestamp = strtotime($v);
                        $v1 = date("d-M-Y",$timestamp);
    					break;
    				default:
    					$k1 = $k;
                        $v1 = $v;
    			}
    			$this->aPoliceVerification[$key][$k1] = $v1;
    			if(!in_array($sK, ["reference","deliverydate"])){
    				$this->aCourtVerification[$key][$k1] = $v1;
    			}
    		} 
    		$this->aPoliceVerification[$key]["Police station"] = "";   		
    		$this->aPoliceVerification[$key]["Ph no of Police station"] = "";   		
    		$this->aPoliceVerification[$key]["Designation of the interviewed police officer"] = "SHO (Station House Officer)";   		
    		$this->aPoliceVerification[$key]["Number of years covered in the police verification"] = "Last 2 years";
    		$this->aPoliceVerification[$key]["Verification remarks"] = "No records";   		   		
    	}
    	//print_r($this->aTracker);
		//print_r($this->aPoliceVerification);
		//print_r($this->aCourtVerification);
    	
    }

    public function writeFromTracker($key){

    	$oPHPWord = new PHPWord();
    	$nameOfFile = $this->sFolder.$this->aTracker[$key]['Applicant'].uniqid().'.docx';
    	


    	// Define table style arrays
		$styleTable = array('borderSize'=>6, 'borderColor'=>'000');
		$oPHPWord->addTableStyle('customStyledTable', $styleTable);

		$oPHPWord->addParagraphStyle('pStyle', array('align'=>'left', 'size'=>11, 'spaceAfter'=>1, 'name'=>'calibri', 'bold'=>true));

		//Header

		$section = $oPHPWord->createSection();
    	$header = $section->createHeader();
		$table = $header->addTable();
		$table->addRow();
		$table->addCell(10000)->addImage('header.jpg', array('width'=>640, 'height'=>100, 'align'=>'left'));

		//-------------End Header-----------------------


		$section->addText('To',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));
		
        $section->addText('M/s A-Check Global',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));
        
        $section->addText('This information is given with regard to the check conducted for:',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));


        //Police Verification

        $section->addText('Police Verification',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
        $table = $section->addTable('customStyledTable');

        foreach($this->aPoliceVerification[$key] as $index=>$value){
        	$table->addRow();
			$table->addCell(5000)->addText("  {$index}",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
			$table->addCell(5000)->addText("  {$value}",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
        }

        //------------------End Police Verification--------------------------------


         //Court Verification

        $section->addText('Court Verification',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
        $table = $section->addTable('customStyledTable');

        foreach($this->aCourtVerification[$key] as $index=>$value){
        	$table->addRow();
			$table->addCell(5000)->addText("  {$index}",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
			$table->addCell(5000)->addText("  {$value}",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
        }

        //------------------End Court Verification--------------------------------


        //Result


        $section->addText('Result',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
			//$section->addTextBreak(1);
		$table = $section->addTable('customStyledTable');

		$table->addRow();
		$table->addCell(5000)->addText(" Court",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
		$table->addCell(5000)->addText(" Jurisdiction",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
		$table->addCell(5000)->addText(" Location",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
		$table->addCell(5000)->addText(" Verification remarks",array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));

		$table->addRow();
		$table->addCell(5000)->addText("Magistrate");
		$table->addCell(5000)->addText("Metropolitan Magistrate / Judicial Magistrate ");
		$table->addCell(5000)->addText(" ---");
		$table->addCell(5000)->addText("No records");


        //-----------------------------End result---------_------------



        //Disclaimer

		$section->addText("On line verification :Verified online litigation database and found none matching with the provided applicant's details",null, 'pStyle');
			//$section->addTextBreak(1);
        $section->addText('Conclusion: In conclusion,as on the date of this search, and as on the records of jurisdictional courts there  is  no Civil or criminal case instituted against the subject .This report is based on the verbal confirmation of the concerned  court /police authority, having  jurisdiction over the police station  within  Whose  limits  the candidate  is said  to be  residing  as  upon the date on which it is confirmed. Hence this information is subjective',null,'pStyle');
        //$section->addTextBreak(1);
        $section->addText('Disclaimer:Due care has been taken in conducting the search. The records are public records and theabove search has been  conducted  on behalf of your good self,as per your instruction and at your request & the undersigned is not responsible for any errors, omissions or deletions, if any ,in  the said court / police records. Please note that this is an information not a certificate', null, 'pStyle');


        //---------------------------_------End Disclaimer--------------------------_-


        //footer
        $styleTable = array('borderSize'=>2, 'borderColor'=>'000000');
        $footer = $section->createFooter();
        $table = $footer->addTable('footerTable',$styleTable);
		$table->addRow();
        $table->addCell(150)->addImage('sign.png', array('width'=>100, 'height'=>100, 'align'=>'left'));
        $table->addCell(9850)->addImage('seal.jpg', array('width'=>100, 'height'=>100, 'align'=>'left'));
        $table->addRow();
        $table->addCell(5000)->addText('S.Shylaja',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));
        $table->addRow();
        $table->addCell(5000)->addText('Advocate & Notary',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));
        $table->addRow();
        $table->addCell(5000)->addText('SS Law Associates',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));

        //----------------------------End Footer---------------------------------------

		$objWriter = PHPWord_IOFactory::createWriter($oPHPWord, 'Word2007');
		$objWriter->save($nameOfFile);
		chmod($nameOfFile, 0777);
    	
    }
} 


?>