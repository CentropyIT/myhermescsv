<?php 
function convert_to_csv($input_array, $output_file_name, $delimiter)
{
    /** open raw memory as file, no need for temp files, be careful not to run out of memory thought */
    $f = fopen('php://memory', 'w');
    /** loop through array  */
    foreach ($input_array as $line) {
        /** default php csv handler **/
        fputcsv($f, $line, $delimiter);
    }
    /** rewrind the "file" with the csv lines **/
    fseek($f, 0);
    /** modify header to be downloadable csv file **/
    header('Content-Type: application/csv');
    header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
    /** Send file to browser for download */
    fpassthru($f);
}
/**
 * Function to see if a string is a UK postcode or not. The postcode is also 
 * formatted so it contains no strings. Full or partial postcodes can be used.
 * 
 * @param string $toCheck
 * @return boolean 
 */
function postcode_check(&$toCheck) {
  // Permitted letters depend upon their position in the postcode.
  $alpha1 = "[abcdefghijklmnoprstuwyz]";                          // Character 1
  $alpha2 = "[abcdefghklmnopqrstuvwxy]";                          // Character 2
  $alpha3 = "[abcdefghjkstuw]";                                   // Character 3
  $alpha4 = "[abehmnprvwxy]";                                     // Character 4
  $alpha5 = "[abdefghjlnpqrstuwxyz]";                             // Character 5
  
  // Expression for postcodes: AN NAA, ANN NAA, AAN NAA, and AANN NAA with a space
  // Or AN, ANN, AAN, AANN with no whitespace
  $pcexp[0] = '^(' . $alpha1 . '{1}' . $alpha2 . '{0,1}[0-9]{1,2})([[:space:]]{0,})([0-9]{1}' . $alpha5 . '{2})?$';
 
  // Expression for postcodes: ANA NAA
  // Or ANA with no whitespace
  $pcexp[1] = '^(' . $alpha1 . '{1}[0-9]{1}' . $alpha3 . '{1})([[:space:]]{0,})([0-9]{1}' . $alpha5 . '{2})?$';
 
  // Expression for postcodes: AANA NAA
  // Or AANA With no whitespace
  $pcexp[2] = '^(' . $alpha1 . '{1}' . $alpha2 . '[0-9]{1}' . $alpha4 . ')([[:space:]]{0,})([0-9]{1}' . $alpha5 . '{2})?$';
 
  // Exception for the special postcode GIR 0AA
  // Or just GIR
  $pcexp[3] = '^(gir)([[:space:]]{0,})?(0aa)?$';
 
  // Standard BFPO numbers
  $pcexp[4] = '^(bfpo)([[:space:]]{0,})([0-9]{1,4})$';
 
  // c/o BFPO numbers
  $pcexp[5] = '^(bfpo)([[:space:]]{0,})(c\/o([[:space:]]{0,})[0-9]{1,3})$';
  
  // Overseas Territories
  $pcexp[6] = '^([a-z]{4})([[:space:]]{0,})(1zz)$';
 
  // Anquilla
  $pcexp[7] = '^(ai\-2640)$';
  
  // Load up the string to check, converting into lowercase
  $postcode = strtolower($toCheck);
 
  // Assume we are not going to find a valid postcode
  $valid = false;
 
  // Check the string against the six types of postcodes
  foreach ($pcexp as $regexp) {
    if (preg_match('/' . $regexp . '/i', $postcode, $matches)) {
 
      // Load new postcode back into the form element
      $postcode = strtoupper($matches[1]);
      if (isset($matches[3])) {
        $postcode .= ' ' . strtoupper($matches[3]);
      }
 
      // Take account of the special BFPO c/o format
      $postcode = preg_replace('/C\/O/', 'c/o ', $postcode);
      
      // Remember that we have found that the code is valid and break from loop
      $valid = true;
      break;
    }
  }
 
  // Return with the reformatted valid postcode in uppercase if the postcode was 
  // valid
  if ($valid) {
    $toCheck = $postcode;
    return true;
  } else {
    return false;
  }
}

?>
