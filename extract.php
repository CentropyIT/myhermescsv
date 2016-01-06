<?php
//functions
include_once('app/functions.php');
//simple dom model
include_once('app/simple_html_dom.php');
//set some variables
$target_dir  = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$csv_file    = $target_dir . basename($_FILES["csvfile"]["name"]);
$uploadOk    = 1;
$FileType    = pathinfo($target_file, PATHINFO_EXTENSION);

// Check if post submitted
if (isset($_POST["submit"])) {
    // put any check here
    $check = true;
    if ($check !== false) {
        //upload is ok, do some more checks
        $uploadOk = 1;
    } else {
        echo "File not valid. This might not work as you expect..";
        $uploadOk = 0;
    }
}
// Check if file already exists
if (file_exists($target_file)) {
    //if it does, remove both csv and html files. we don't need either. 
    unlink($target_file);
    unlink($csv_file);
    $uploadOk = 1;
}
// Check file size
if ($_FILES["fileToUpload"]["size"] > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}
// Allow html files
if ($FileType != "html") {
    echo "Sorry, only html files are allowed.";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
} else {
	//move the uploaded file
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        //echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.<br>";
    } else {
        echo "Sorry, there was an error uploading your html file.";
    }
	//move the csv file
    if (move_uploaded_file($_FILES["csvfile"]["tmp_name"], $csv_file)) {
        //echo "The file ". basename( $_FILES["csvfile"]["name"]). " has been uploaded.<br>";
    } else {
        echo "Sorry, there was an error uploading your csv file.";
}
    //shortn the names a bit so they're manageable.
    $csvcheck = $target_dir . basename($_FILES["csvfile"]["name"]);
    $url      = file_get_html($target_file);
    //echo $target_file."<br>";
    //echo $csvcheck;
    //find all html <tr> tag
    foreach ($url->find('tr') as $e) {
        //echo $e."<<<<<<<<";
        //find the word Track. this appears in all the target tables so returns a lot of results. 
        if (strpos($e, 'Track') !== false) {
            //strip out any html tags and convert to plaintext.  the last speech marks clear the end and the space in the middle leaves a gap between words	
            $e2           = preg_replace('/\s+/', ' ', $e->plaintext . "");
            //echo $e2;	
            //there's still a lot of data so strip out anything that's more than 100 chars after removing all the html bits. 	
            $isittracking = strlen($e2);
            if ($isittracking <= 50) {
                //this is the tracking information 
                $contents[] = $e2;
                //var_dump ( $contents );
            }
			//if it's more than 51 and less than 100 chars, it's our target row
            if (($isittracking >= 51) && ($isittracking <= 100)) {
                
                $contents[] = $e2;
                //$contents[] = substr($e2,24, 42);   
                //	var_dump ($contents);
            }
        }
    }
    //make sure we haven't messed up previous steps and aren't left with a blank array
    if (!$e2 == NULL) {
    }
    //break up the content into chunks. 
	//this is a currently single string with spaces in each row of the array
    foreach ($contents as $hrmph) {
        //so explode the pieces separated by space marks
        $pieces = explode(" ", $hrmph);
        foreach ($pieces as $checkif) {
			//the tracking numbers is 16 chars long
            if (strlen($checkif) === 16) {
                $tracking = $checkif;
                //echo $tracking." is a tracking no <br>";
            }
			//postcode values are chunked into both value parts, max 4 chars
            if (strlen($checkif) < 5) {
				//use the postcode check function to validate
                $newpcodecheck = postcode_check($checkif);
				//if the string is a valid postcode chunk
                if ($newpcodecheck) {
                    //	echo $checkif." "; 
                    $element  = current($pieces);
				//rebuild the postcode into a single string. the -1 is the previous string value
				//because each postcode sections value was previously broken into strings
				//the space is added manually 
                    $lowpcode = $pieces[key($pieces) - 1] . " " . $pieces[key($pieces)];
                } else {
                }
            }
            
        }
        //$lowpcode=$pieces[1]." ".$pieces[2];
        //$lowpcode=$pieces[1]." ".$pieces[2];
        //echo $pieces[2];
        //echo $pieces[7];
        
        $valid = postcode_check($lowpcode);
        if ($valid) {
            $postcode = $lowpcode;
            
            //	echo "valid".$postcode;
        } else {
            
            echo "invalid" . $postcode;
        }
        
        //echo "Postcode: ".$postcode." "; // postcode part one
        //echo " Tracking Reference: ".$pieces[11]."<br>"; // tracking ref
        //an array of the postcodes
        $htmlpcode[]        = $postcode;
        //the tracking reference as a separate variable.
        $trackref[]         = $tracking;
        //$fuldata['postcode'][]=$postcode;
        //an array of the postcode and tracking number as an array that is like 
		//array(postcode=>'tracking numbers') to keep them both together.
        $fuldata[$postcode] = $tracking;
    }
    //  if (isset($contents)){	var_dump ($contents); }
    //var_dump ($e);
    $row = 1;
    if (($handle = fopen($csvcheck, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);
            //	        echo "<p> $num fields in line $row: <br /></p>\n";
            for ($c = 0; $c < $num; $c++) {
                $lines[$row][] = $data[$c] . "\n";
            }
            $row++;
        }
        fclose($handle);
    }
}
$count = 0;
//read the lines from the csv file
foreach ($lines as $cout) {
    $count++;
    //var_dump ($cout[11]);
    //echo $cout[ $count ];	
    //var_dump ($cout);
	//eout2 is the postcode from the csv file
    $eout2      = rtrim(preg_replace('/\s+/', ' ', $cout[4] . ""), " \t.");
	//outorder is the order ref from the csv file
    $eoutorder  = rtrim(preg_replace('/\s+/', ' ', $cout[11] . ""), " \t.");
	//grab the postcodes in the array
    $csvpcode[] = $eout2;
	//some people don't know how to write in their own postcodes so
	//run a postcode check on all the entries in the csv file
    $valid      = postcode_check($eout2);
	//spit out the valid postcodes
    $pcodecsv   = $eout2;
	
	//if the content is invalid, run a procedure below.  
	//This can be built in to the postcode validator above i.e.
	//if ($valid = postcode_check($eout2)){ $pcodecsv=$eout2;} else { //error }
	//but I didn't do that because it's a given that the value will be provided.
	//and will be at least a complete postcode
    if ($valid) {
        //	echo "valid".$pcodecsv;
    } else {
        //	echo "invalid".$pcodecsv;
    }
    $csvorderref[]     = $eoutorder;
    //$fulcsv[]['postcode']=$eout2;
    $fulcsv[$pcodecsv] = $eoutorder;
}
//remove the headings from the output
array_splice($csvpcode, 0, 1);
array_splice($csvorderref, 0, 1);
//dumps of the arrays
	//var_dump($fuldata);
	//var_dump($fulcsv);
	//var_dump($csvorderref);
	//var_dump ($csvpcode);
	//var_dump ($htmlpcode);
	//var_dump ($trackref);
	//var_dump ($lines);
//rechecking each entry
$errorcorr  = 0;
$csverrors  = 0;
$htmlerrors = 0;

foreach ($htmlpcode as $postcode) {
  //if anything goes wrong with the csv  
    if (!array_key_exists($postcode, $fulcsv)) {
		//add to errors
        $csverrors++;
		//explain the error
        echo "<div id='error'>something not right. " . $postcode . " Doesn't have an entry in the CSV file. It shipped via hermes reference: " . $fuldata[$postcode] . "</div>";
  //if anything goes wrong with the csv  
        if (!array_key_exists($postcode, $fuldata)) {
		//add to errors
            $htmlerrors++;
			//explain the error
            echo "<div id='error'>something not right. " . $postcode . " Doesn't have an entry in the HTML file. Was it Shipped via Hermes?: " . $fulcsv[$postcode] . "</div>";
        }
    } else {
//        echo "<div id='output'><spanid='first'>Output:" . $postcode . "</span><span id='second'> " . $fulcsv[$postcode] . "</span><span id='third'>" . $fuldata[$postcode] . "</span></div>";
    //build an array of the order reference from the csv file 
	//and shipping reference from the html file
	//that match each other through the postcode 
        $list[] = array(
            $fulcsv[$postcode],
            $fuldata[$postcode]
        );
    }
 //count of total number of entries   
    $errorcorr++;
}
//	var_dump ($list);
//if there are still no errors then
if (($csverrors == 0) && ($htmlerrors == 0)) {
	//output a csv wiith the array using the convert_to_csv function 
    convert_to_csv($list, 'report.csv', ',');
} else {
	//otherwise dump the results along with the content above to 
    echo "<p>no file until corrections have been corrected";
    echo "<p>Total Entries  " . $errorcorr;
    echo "<p>csv errors  " . $csverrors;
    echo "<p>html errors  " . $htmlerrors;
}
?>
