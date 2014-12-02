<?php>

function _civicrm_api3_job_uploadagendabe_spec ($params) {
  $params['ftp_url']['api.required'] = 1;
  $params['ftp_userpwd']['api.required'] = 1;
}

function civicrm_api3_job_uploadagendabe($params) {
  $ch = curl_init();
  $temp = tmpfile();
  fwrite($temp, "writing to tempfile");
  $filename= "events.xml";
    curl_setopt($ch, CURLOPT_URL, $params['ftp_url'].$filename);
    curl_setopt($ch, CURLOPT_USERPWD, $params['ftp_userpwd']);
    curl_setopt($ch, CURLOPT_UPLOAD, 1);
    curl_setopt($ch, CURLOPT_INFILE, $temp);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($temp));
    curl_exec ($ch);
    curl_close ($ch);
    fclose($temp); // this removes the file
    if (curl_errno($ch) !== 0) {
          $error = 'File upload error.';
    }
}

