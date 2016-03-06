<?php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__.'/PHPWord.php';
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
$reader = ReaderFactory::create(Type::XLSX);
$today = date("m.d.y");
//echo "<pre>";

if(isset( $_FILES ) && !empty($_FILES)){

	$folder = "trackers/{$_FILES["fileToUpload"]["name"]}/".time()."/";
	if (!file_exists( $folder )) {
		mkdir($folder,0777,true);
	}
	chmod($folder, 0777);
	$target_file = $folder.basename($_FILES["fileToUpload"]["name"]);

	
	if(isset( $_POST["submit"] )){
		move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
		$reader->open($target_file);
		foreach ($reader->getSheetIterator() as $sheet) {
		    foreach ($sheet->getRowIterator() as $row) {
		        // do stuff with the row
		        //print_r($row);
		        $rows[] = $row;
		    }
		}
		//print_r($rows); exit;
		//echo count($rows);
		$reader->close();
		for ($i=1;$i<count($rows);$i++){
			for($j=0;$j<count($rows[0]);$j++){
				//echo $rows[$i][$j];
				$key= strtolower($rows[0][$j]);
				//echo $key."\n";
				if($key=="dob" || $key=="date of birth"){
					$timestamp = ($rows[$i][$j] - 25569) * 86400;					
					$namedCols[$i][$rows[0][$j]] = date("d/m/Y",$timestamp);
				}
				else{
				 $namedCols[$i][$rows[0][$j]] = iconv('UTF-8', 'ASCII//TRANSLIT',$rows[$i][$j]);
				}
			}
		}
		//print_r($namedCols);exit();
		
		foreach($namedCols as $index=>$namedColArray){
			$nameOfFile = $folder.$namedColArray['Applicant\'s Name'].uniqid().".docx";
			//iconv( "Windows-1252", "UTF-8", $key )
			$PHPWord = new PHPWord();
			
			
			$section = $PHPWord->createSection();


			$header = $section->createHeader();
			$table = $header->addTable();
			$table->addRow();
			$table->addCell(10000)->addImage('header.jpg', array('width'=>640, 'height'=>100, 'align'=>'left'));

			//$section->addImage('header.jpg', array('width'=>640, 'height'=>160, 'align'=>'left'));

			$section->addText('To',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
			
            $section->addText('M/s A-Check Global',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
            
            $section->addText('This information is given with regard to the check conducted for:',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));

			// Define table style arrays
			$styleTable = array('borderSize'=>6, 'borderColor'=>'006699');
			// Define cell style arrays
			$styleCell = array('valign'=>'center');
			// Add table style
			$PHPWord->addTableStyle('myOwnTableStyle', $styleTable);

			// Define font style for first row
			$fontStyle = array('bold'=>true, 'align'=>'center');

			$section->addText('Police verification',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
			//$section->addTextBreak(1);

			
			$table = $section->addTable('myOwnTableStyle');

			foreach ($namedColArray as $key => $value) {
				if(strtolower($key)=="date"){
					$key = "Date of information from police station"; 
				}
				
				$table->addRow();
				$table->addCell(5000)->addText("$key",array('name'=>'Calibri', 'size'=>'9','bold'=>true,'align'=>'center'));
				$table->addCell(5000)->addText("$value",array('name'=>'Calibri', 'size'=>'9','bold'=>true,'align'=>'center'));
			}


			$table->addRow();
			$table->addCell(5000)->addText("Police station",array('name'=>'Calibri', 'size'=>'9','bold'=>true,'align'=>'center'));
			$table->addCell(5000)->addText(" ");

			$table->addRow();
			$table->addCell(5000)->addText("Ph number of police station",array('name'=>'Calibri', 'size'=>'9','bold'=>true,'align'=>'center'));
			$table->addCell(5000)->addText(" ");

			$table->addRow();
			$table->addCell(5000)->addText("Designation of the interviewed police officer",array('name'=>'Calibri', 'size'=>'9','bold'=>true,'align'=>'center'));
			$table->addCell(5000)->addText("SHO (Station House Officer)",array('name'=>'Calibri', 'size'=>'9','bold'=>true,'align'=>'center'));

			$table->addRow();
			$table->addCell(5000)->addText("Number of years covered in the police verification",array('name'=>'Calibri', 'size'=>'9','bold'=>true,'align'=>'center'));
			$table->addCell(5000)->addText("Last 2 years",array('name'=>'Calibri', 'size'=>'9','bold'=>true,'align'=>'center'));

			$table->addRow();
			$table->addCell(5000)->addText("Verification remarks",array('name'=>'Calibri', 'size'=>'9','bold'=>true,'align'=>'center'));
			$table->addCell(5000)->addText("No records",array('name'=>'Calibri', 'size'=>'9','bold'=>true,'align'=>'center'));

			//$section->addTextBreak(1);
			$section->addText('Court verification',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
			//$section->addTextBreak(1);
			
			$table = $section->addTable('myOwnTableStyle');

			foreach ($namedColArray as $key => $value) {
				if(!in_array(strtolower($key), ['ref. no','date','ref. no.'])){

					$table->addRow();
					$table->addCell(5000)->addText("$key",array('name'=>'Calibri', 'size'=>'9','bold'=>true,'align'=>'center'));
					$table->addCell(5000)->addText("$value",array('name'=>'Calibri', 'size'=>'9','bold'=>true,'align'=>'center'));
				}
			}

			//$section->addTextBreak(1);
			$section->addText('Result',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'center'));
			//$section->addTextBreak(1);
			$table = $section->addTable('myOwnTableStyle');

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

			//$section->addTextBreak(2);
			$PHPWord->addParagraphStyle('pStyle', array('align'=>'left', 'spaceAfter'=>10, 'bold'=>true));

			$section->addText("On line verification :Verified online litigation database and found none matching with the provided applicant's details",null, 'pStyle');
			//$section->addTextBreak(1);
            $section->addText('Conclusion: In conclusion,as on the date of this search, and as on the records of jurisdictional courts there  is  no Civil or criminal case instituted against the subject .This report is based on the verbal confirmation of the concerned  court /police authority, having  jurisdiction over the police station  within  Whose  limits  the candidate  is said  to be  residing  as  upon the date on which it is confirmed. Hence this information is subjective',null,'pStyle');
            //$section->addTextBreak(1);
            $section->addText('Disclaimer:Due care has been taken in conducting the search. The records are public records and theabove search has been  conducted  on behalf of your good self,as per your instruction and at your request & the undersigned is not responsible for any errors, omissions or deletions, if any ,in  the said court / police records. Please note that this is an information not a certificate', null, 'pStyle');


            $footer = $section->createFooter();
            $styleTable = array('borderSize'=>0);
            $table = $footer->addTable('footerTable',$styleTable);
			$table->addRow();
            $table->addCell(100)->addImage('sign.png', array('width'=>75, 'height'=>75, 'align'=>'left'));
            $table->addCell(9900)->addImage('seal.jpg', array('width'=>75, 'height'=>75, 'align'=>'left'));
            $table->addRow();
            $table->addCell(5000)->addText('S.Shylaja',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));
            $table->addRow();
            $table->addCell(5000)->addText('Advocate & Notary',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));
            $table->addRow();
            $table->addCell(5000)->addText('SS Law Associates',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));


            /*$footer->addImage('sign.png', array('width'=>75, 'height'=>75, 'align'=>'left'));
            $footer->addImage('seal.jpg', array('width'=>75, 'height'=>75, 'align'=>'left'));
            $footer->addText('S.Shylaja',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));
            $footer->addText('Advocate & Notary',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));
            $footer->addText('SS Law Associates',array('name'=>'Calibri', 'size'=>'10','bold'=>true,'align'=>'left'));*/

			$objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
			$objWriter->save("{$nameOfFile}");
			chmod($nameOfFile, 0777);
		}

	}
}

?>

<!DOCTYPE html> 
<html>
	<head>

		<title>Tracker App</title>

		<script src="bower_components/jquery/dist/jquery.min.js"></script>	
		
		<script type="text/javascript" src="bower_components/webcomponentsjs/webcomponents.min.js"></script>

		<link rel="import" href="bower_components/polymer/polymer.html">

		<link rel="import" href="elements/form.html">

		<link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">

	</head>
	<body>
		<div class="container">
			<upload-form customheader="Select a tracker" callback="upload"></upload-form>
		</div>
		<div class="container-fluid">
		</div>
		<script>
			$(document).ready(function(){
				//$( "#tracker-loader" ).submit(function( event ) {
	                
            	//});
			});
            

    	</script>	
	</body>
</html>