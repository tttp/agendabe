<?php

require_once 'CRM/Core/Page.php';

class CRM_Agendabe_Page_AgendaBE extends CRM_Core_Page {
  function run() {
    header("Content-Type: application/xhtml+xml; charset=utf-8");
    echo civicrm_api3("Event","generateagendabe",array()); 
    CRM_Utils_System::civiExit();
  }
}
