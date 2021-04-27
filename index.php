<?php
  
  function flatten($dotKey, $value) {
    if (is_array($value) || is_object($value)) {
      if ($dotKey !== '') {
        $dotKey .= '.';
      }
      $result = array();
      foreach ($value as $key => $val) {
        $newKey = $dotKey . $key;
        $result = array_merge($result, flatten($newKey, $val));
      }
      return $result;
    }
    return array($dotKey => $value);
  }
  
  function insertAtPosition(&$array, $position, $value) {
    $array = array_pad($array, $position + 1, "");
    $array[$position] = $value;
  }

  $json = file_get_contents("php://input");
  $jsonData = json_decode($json);
  
  $file = fopen("collected.csv", "r");
  
  $tail = array();
  $header = false;
  while($csvData = fgetcsv($file)) {
    if (!$header) {
      $header = $csvData;
    }
    else {
      $tail[] = $csvData;
    }
  }

  fclose($file);
  
  if (!$header) {
    $header = array();
  }
  
  $flatten = flatten('', $jsonData);
  $csv = array();
  
  foreach ($flatten as $key => $value) {
    $pos = array_search($key, $header);
    if (!$pos) {
      $pos = count($header);
      insertAtPosition($header, $pos, $key);
    }
    insertAtPosition($csv, $pos, $value);
  }
  
  $file = fopen("collected.csv", "w");
  
  fputcsv($file, $header);
  foreach ($tail as $line) {
    fputcsv($file, $line);
  }
  fputcsv($file, $csv);
  
  fclose($file);
  
  echo json_encode(array(
    "message" => "Data saved successfully",
    "data" => $jsonData
  ));
?>