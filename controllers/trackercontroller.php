<?php
require_once "./models/trackermodel.php";

class trackerController

	{
	private $oReader;
	public $client;

	public $sTargetFile;

	private $oModel;
	public $aPoliceVerification;

	public $aCourtVerification;

	public $aTracker;

	public $sFolder;

	function __construct($oReader)
		{
		$this->oReader = $oReader;

		// $this->oModel = new trackerModel;

		}

	Private
	function readTracker()
		{
		$this->oReader->open($this->sTargetFile);
		foreach($this->oReader->getSheetIterator() as $sheet)
			{
			foreach($sheet->getRowIterator() as $row)
				{
				$aRow = null;
				for ($i = 0; $i < count($row); $i++)
					{
					if (gettype($row[$i]) == "string")
						{
						$aRow[$i] = iconv('UTF-8', 'ASCII//TRANSLIT', $row[$i]);
						}
					  else
						{
						$aRow[$i] = $row[$i];
						}
					}

				if ($aRow != null) $rows[] = $aRow;
				}
			}

		$this->oReader->close();

		// create named columns from rows[0] as keys

		for ($i = 1; $i < count($rows); $i++)
			{
			for ($j = 0; $j < count($rows[0]); $j++)
				{
				$key = strtolower($rows[0][$j]);
				if (in_array($key, ["dob", "deliverydate"]))
					{
					if (gettype($rows[$i][$j]) == "integer")
						{

						// convert excel date to structured date

						$timestamp = ($rows[$i][$j] - 25569) * 86400;
						$namedCols[$i][$rows[0][$j]] = date("d-M-Y", $timestamp);
						}
					elseif (is_object($rows[$i][$j]))
						{

						// code...
						// echo "<pre>";

						$aDate = get_object_vars($rows[$i][$j]);
						$timestamp = strtotime($aDate['date']);
						$namedCols[$i][$rows[0][$j]] = date("d-M-Y", $timestamp);
						}
					  else
						{

						// throw exception

						$timestamp = strtotime($rows[$i][$j]);
						$namedCols[$i][$rows[0][$j]] = date("d-M-Y", $timestamp);
						}
					}
				  else
					{
					$namedCols[$i][$rows[0][$j]] = $rows[$i][$j];
					}
				}
			}

		$this->aTracker = $namedCols;
		}

	function readAndLoadTracker()
		{
		$this->readTracker();

		// $this->loadTracker();

		}

	Private
	function loadTracker()
		{

		// echo "<pre>";
		// print_r($this->aTracker);
		// $this->oModel->load($this->aTracker);

		}

	public

	function __getTracker()
		{
		return $this->aTracker;
		}

	public

	function createBucketsForPoliceAndCourt()
		{
		foreach($this->aTracker as $key => $aColumnAssociatedArray)
			{

			// code...

			foreach($aColumnAssociatedArray as $k => $v)
				{
				$sK = strtolower($k);
				switch ($sK)
					{
				case "reference":
					$k1 = "Ref. No.";
					$v1 = $v;
					break;

				case "name":
					if ($this->client == "PAMAC")
						{
						$k1 = "Name of the Subject";
						}
					  else
						{
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
					$v1 = $v;
					break;

				case "deliverydate":
					if ($this->client != "A-Check")
						{
						$k1 = "Date of Verification";
						}
					  else
						{
						$k1 = "Date of information from police station";
						}

					$v1 = $v;

					// echo $v1;

					break;

				default:
					$k1 = $k;
					$v1 = $v;
					}

				$this->aPoliceVerification[$key][$k1] = $v1;
				if (!in_array($sK, ["reference", "deliverydate"]) && $this->client == "A-Check")
					{
					$this->aCourtVerification[$key][$k1] = $v1;
					}
				}

			if ($this->client == "A-Check")
				{
				$this->aPoliceVerification[$key]["Police station"] = "";
				$this->aPoliceVerification[$key]["Ph no of Police station"] = "";
				$this->aPoliceVerification[$key]["Designation of the interviewed police officer"] = "SHO (Station House Officer)";
				$this->aPoliceVerification[$key]["Number of years covered in the police verification"] = "Last 3 years";
				$this->aPoliceVerification[$key]["Verification remarks"] = "No records";
				}
			}
		}

	public

	function writeFromTracker($key)
		{
		$oPHPWord = new \PhpOffice\PhpWord\PhpWord();
		$nameOfFile = $this->sFolder . $this->aTracker[$key]['Name'] . uniqid() . '.docx';

		// Define table style arrays

		$styleTable = array(
			'borderSize' => 6,
			'borderColor' => '000',
			'cellMargin' => 80
		);
		$oPHPWord->addTableStyle('customStyledTable', $styleTable);

		$styleHeaderTable = array(
			'borderSize' => 6,
			'borderColor' => '000'
		);
		$oPHPWord->addTableStyle('customHeaderTable', $styleHeaderTable);

		$styleFooterTable = array(
			'borderSize' => 0,
			'borderColor' => 'ffffff'
		);
		$oPHPWord->addTableStyle('customFooterTable', $styleFooterTable);


		//fonts
		$fontStyle = new \PhpOffice\PhpWord\Style\Font();
		$fontStyle->setBold(true);
		$fontStyle->setName('Calibri');
		$fontStyle->setSize(11);



		$paragraphStyleName = 'pStyle';
		$fontStyleName = 'fStyle';
		$paraHeaderStyleName = 'pHeaderStyle';
		$sectionHeaderStyleName = 'sHeaderStyle';



		$oPHPWord->addParagraphStyle($paragraphStyleName, array(
			'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::JUSTIFY,
			'spaceAfter' => 100
		));
		$oPHPWord->addParagraphStyle($paraHeaderStyleName, array(
			'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
			'spaceAfter' => 100
		));
		$oPHPWord->addParagraphStyle($sectionHeaderStyleName, array(
			'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT
		));

		// Header

		$section = $oPHPWord->addSection();
		$header = $section->addHeader();
		$table = $header->addTable();
		$table->addRow();
		$table->addCell(10000)->addImage('header.jpg', array(
			'width' => 640,
			'height' => 100,
			'align' => 'left'
		));

		// -------------End Header-----------------------
		$section->addTextBreak(1);
		if ($this->client == "A-Check")
			{
			$section->addText('To', $fontStyle);
			$section->addText('Ms A-Check Global', $fontStyle);
			}
		  else
			{
			$section->addText('To Whomsoever it may concern', $fontStyle);
			}
		$section->addTextBreak(1);
		$section->addText('This information is given with regard to the check conducted for:', $fontStyle);

		// Police Verification
		$section->addTextBreak(1);
		if ($this->client == "A-Check")
			{
			$section->addText("Police Verification", $fontStyle, $paraHeaderStyleName);
			}

		$table = $section->addTable('customStyledTable');
		foreach($this->aPoliceVerification[$key] as $index => $value)
			{
			$table->addRow();
			$table->addCell(5000)->addText("{$index}", $fontStyle);
			$table->addCell(5000)->addText("{$value}", $fontStyle);
			}

		// ------------------End Police Verification--------------------------------
		$section->addTextBreak(1);
		if ($this->client == "A-Check")
			{

			// Court Verification

			$section->addText("Court Verification", $fontStyle, $paraHeaderStyleName);
			$table = $section->addTable('customStyledTable');
			foreach($this->aCourtVerification[$key] as $index => $value)
				{
				$table->addRow();
				$table->addCell(5000)->addText("{$index}", $fontStyle);
				$table->addCell(5000)->addText("{$value}", $fontStyle);
				}
				$section->addTextBreak(1);
			}

		// ------------------End Court Verification--------------------------------
		// Result

		$section->addText("Result", $fontStyle, $paraHeaderStyleName);
		if ($this->client == "A-Check")
			{
			$table = $section->addTable('customStyledTable');
			$table->addRow();
			$table->addCell(3000)->addText("Court", $fontStyle);
			$table->addCell(3000)->addText("Jurisdiction", $fontStyle);
			$table->addCell(3000)->addText("Location", $fontStyle);
			$table->addCell(3000)->addText("Verification remarks", $fontStyle);
			$table->addRow();
			$table->addCell(3000)->addText("Magistrate", $fontStyle);
			$table->addCell(3000)->addText("Metropolitan Magistrate / Judicial Magistrate ", $fontStyle);
			$table->addCell(3000)->addText("---");
			$table->addCell(3000)->addText("No records", $fontStyle);
			}
		  else
			{
			$section->addText('Civil Proceedings: Original Suit / Miscellaneous Suit /Execution / Arbitration Case', $fontStyle, $sectionHeaderStyleName);
			$table = $section->addTable('customStyledTable');
			$table->addRow();
			$table->addCell(3000)->addText("Court", $fontStyle);
			$table->addCell(3000)->addText("Jurisdiction", $fontStyle);
			$table->addCell(5000)->addText("Name of Court", $fontStyle);
			if ($this->client == "PCC")
				{
				$table->addCell(3000)->addText("Duration covered", $fontStyle);
				}

			$table->addCell(3000)->addText("Result", $fontStyle);
			$table->addRow();
			$table->addCell(3000)->addText("Civil Court", $fontStyle);
			$table->addCell(3000)->addText("");
			$table->addCell(5000)->addText("City Civil Court", $fontStyle);
			if ($this->client == "PCC")
				{
				$table->addCell(3000)->addText("07 Years", $fontStyle);
			}

			$table->addCell(3000)->addText("No records", $fontStyle);
			$table->addRow();
			$table->addCell(3000)->addText("High Court", $fontStyle);
			$table->addCell(3000)->addText(" ");
			$table->addCell(5000)->addText("High Court ", $fontStyle);
			if ($this->client == "PCC")
				{
				$table->addCell(3000)->addText("07 Years", $fontStyle);
			}

			$table->addCell(3000)->addText("No records", $fontStyle);
			$section->addTextBreak(1);
			$section->addText('Criminal Proceedings: Criminal Petitions / Criminal Appeal / Sessions Case /Special Sessions Case / Criminal Miscellaneous Petition / Criminal Revision Appeal', $fontStyle, $sectionHeaderStyleName);
			$section->addTextBreak(1);
			$table = $section->addTable('customStyledTable');
			$table->addRow();
			$table->addCell(3000)->addText("Court", $fontStyle);
			$table->addCell(3000)->addText("Jurisdiction", $fontStyle);
			$table->addCell(5000)->addText("Name of Court", $fontStyle);
			if ($this->client == "PCC")
				{
				$table->addCell(3000)->addText("Duration covered", $fontStyle);
				}

			$table->addCell(3000)->addText("Result", $fontStyle);
			$table->addRow();
			$table->addCell(3000)->addText("Magistrate Court", $fontStyle);
			$table->addCell(3000)->addText(" ");
			$table->addCell(5000)->addText("Criminal Cases(CC), Private Complaint Report (PCR) ", $fontStyle);
			if ($this->client == "PCC")
				{
				$table->addCell(3000)->addText("07 Years", $fontStyle);
				}

			$table->addCell(3000)->addText("No records", $fontStyle);
			$table->addRow();
			$table->addCell(3000)->addText("Sessions Court", $fontStyle);
			$table->addCell(3000)->addText(" ");
			$table->addCell(5000)->addText("Criminal Appeals", $fontStyle);
			if ($this->client == "PCC")
				{
				$table->addCell(3000)->addText("07 Years", $fontStyle);
				}

			$table->addCell(3000)->addText("No records", $fontStyle);
			$table->addRow();
			$table->addCell(3000)->addText("High Court", $fontStyle);
			$table->addCell(3000)->addText(" ");
			$table->addCell(5000)->addText("Criminal Appeals", $fontStyle);
			if ($this->client == "PCC")
				{
				$table->addCell(3000)->addText("07 Years", $fontStyle);
				}

			$table->addCell(3000)->addText("No records", $fontStyle);
			if ($this->client == "PCC")
				{
				$section->addTextBreak(1);
				$table = $section->addTable('customStyledTable');
				$table->addRow();
				$table->addCell(5000)->addText("Name of the police station", $fontStyle);
				$table->addCell(5000)->addText("Name &amp; Designation of the verifier", $fontStyle);
				$table->addCell(5000)->addText("Date of Verification", $fontStyle);
				$table->addCell(5000)->addText("Police Station’s Contact No.", $fontStyle);
				$table->addCell(5000)->addText("Period Covered", $fontStyle);
				$table->addCell(5000)->addText("Remarks", $fontStyle);
				$table->addRow();
				$table->addCell(5000)->addText(" ");
				$table->addCell(5000)->addText(" ");
				$table->addCell(5000)->addText(" ");
				$table->addCell(5000)->addText(" ");
				$table->addCell(5000)->addText("Upto 3 years", $fontStyle);
				$table->addCell(5000)->addText("No records", $fontStyle);
				$section->addTextBreak(1);
				$section->addText('On line Verification (OCRC-Online Criminal Record Check):', $fontStyle, $paraHeaderStyleName);
				$section->addTextBreak(1);
				$table = $section->addTable('customStyledTable');
				$table->addRow();
				$table->addCell(5000)->addText("Disposed Cases", $fontStyle);
				$table->addCell(5000)->addText("Pending Cases", $fontStyle);
				$table->addCell(5000)->addText("Remarks", $fontStyle);
				$table->addRow();
				$table->addCell(5000)->addText("Original, Miscellaneous, Arbitration", $fontStyle);
				$table->addCell(5000)->addText("For Civil &amp; Criminal records", $fontStyle);
				$table->addCell(5000)->addText("No Records", $fontStyle);
				}
			}

		// -----------------------------End result---------_------------

		// Disclaimer
		$section->addTextBreak(1);
		if ($this->client == "A-Check")
			{
			$section->addText("On line verification :Verified online litigation database and found none matching with the provided applicant's details", $fontStyle, $paragraphStyleName);
			$section->addText('Conclusion: In conclusion,as on the date of this search, and as on the records of jurisdictional courts there  is  no Civil or criminal case instituted against the subject .This report is based on the verbal confirmation of the concerned  court /police authority, having  jurisdiction over the police station  within  Whose  limits  the candidate  is said  to be  residing  as  upon the date on which it is confirmed. Hence this information is subjective', $fontStyle, $paragraphStyleName);
			$section->addText('Disclaimer:Due care has been taken in conducting the search. The records are public records and theabove search has been  conducted  on behalf of your good self,as per your instruction and at your request &amp; the undersigned is not responsible for any errors, omissions or deletions, if any ,in  the said court / police records. Please note that this is an information not a certificate', $fontStyle, $paragraphStyleName);
			}

		if ($this->client == "PAMAC")
			{
			$section->addText('Conclusion: In conclusion, as on the date of this search, and as on the records of jurisdictional courts there is no Civil or criminal case instituted against the subject. The above search results are based on the registers of first information reports, in respect of criminal cases maintained in the above mentioned court /police station having jurisdiction over the police stations within whose limits the candidate is said to be residing. This report is based on the verbal confirmation given by the concerned authorities.', $fontStyle, $paragraphStyleName);
			$section->addText('Disclaimer: Due care has been taken in conducting the search. The records are public records and the above search has been conducted on behalf of your good self, as per your instruction and at your request &amp; the undersigned is not responsible for any errors, omissions or deletions,if any ,in the said court /police records .', $fontStyle, $paragraphStyleName);
			}

		if ($this->client == "PCC")
			{
			$section->addText('Note: This covers the Jurisdictional Civil Court, Magistrate Court, Session court, High Court, paid and Proprietary databases including law firm databases.', $fontStyle, $paragraphStyleName);
			$section->addText('Conclusion:  In conclusion, as on the date of this search, and as on the records of jurisdictional courts there is no Civil or criminal case instituted against the said subject. This report is based on the verbal confirmation of the concerned court / police authority, as upon the date on which It is so confirmed. Hence this information is subjective.', $fontStyle, $paragraphStyleName);
			$section->addText('Disclaimer: Due care has been taken in conducting the search.  The records are public records and the above search has been conducted on behalf of your good self, as per your instruction &amp; at your request The undersigned is not responsible for any errors, omissions or deletions nor accepts any responsibility or liability for any damage or loss arising from the direct /indirect use of the information if any in the said court / police records. Please note that this is an information &amp; not a certificate.', $fontStyle, $paragraphStyleName);
			}

		// ---------------------------_------End Disclaimer--------------------------_-

		// footer
		$footer = $section->addFooter();
		$table = $footer->addTable('customFooterTable');
		$table->addRow();
		$table->addCell(10000)->addImage('signature.png', array(
			'width' => 180,
			'height' => 180,
			'align' => 'left'
		));

		// ----------------------------End Footer---------------------------------------
		$oPHPWord->getCompatibility()->setOoxmlVersion(14);
		$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($oPHPWord, 'Word2007');
		$objWriter->save($nameOfFile);
		chmod($nameOfFile, 0777);
		}
	}

?>
