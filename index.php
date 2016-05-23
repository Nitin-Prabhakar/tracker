<?php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__.'/controllers/trackercontroller.php';
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;



$oReader = ReaderFactory::create(Type::XLSX);

if(isset( $_FILES ) && !empty($_FILES) && isset($_POST)){

	$oTracker = new trackerController($oReader);
	$oTracker->client =  $_POST['client'];

	$sName = str_ireplace(".xlsx", "", $_FILES["fileToUpload"]["name"]);

	$oTracker->sFolder = "trackers/{$oTracker->client}/{$sName}/".time()."/";

	//rmdir("trackers/{$oTracker->client}");

	if (!file_exists( $oTracker->sFolder )) {
		mkdir($oTracker->sFolder,0777,true);
	}

	$oTracker->sTargetFile = $oTracker->sFolder.basename($_FILES["fileToUpload"]["name"]);

	move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $oTracker->sTargetFile);


	$oTracker->readAndLoadTracker();

	$aTracker = $oTracker->__getTracker();

	$oTracker->createBucketsForPoliceAndCourt();


	//print_r($aNamedCols);exit();

	foreach ($aTracker as $key => $aValue) {
		# code...

		$oTracker->writeFromTracker($key);
	}
	$zip = new ZipArchive();

	$sZipName = $oTracker->sFolder.'/'.$oTracker->client.'-'.$sName.'.zip';

	if($zip->open($sZipName, ZIPARCHIVE::CREATE)!==TRUE){
		die("Could not create archive");
	}
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$oTracker->sFolder}"));
	//echo "<pre>";
	foreach ($iterator as $key=>$value) {
		//echo realpath($key)."\n\n";
		$zip->addFile(realpath($key), $key) or die ("ERROR: Could not add file: $key");
	}
	$zip->close();
	echo $file = $sZipName;
	header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Cache-Control: must-revalidate');
    readfile($file);
    flush();





    function deleteDirectory($dir) {
	    if (!file_exists($dir)) {
	        return true;
	    }

	    if (!is_dir($dir)) {
	        return unlink($dir);
	    }

	    foreach (scandir($dir) as $item) {
	        if ($item == '.' || $item == '..') {
	            continue;
	        }

	        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
	            return false;
	        }

	    }

	    return rmdir($dir);
	}
	deleteDirectory($oTracker->sFolder);
	deleteDirectory("trackers/{$oTracker->client}");
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