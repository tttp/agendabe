<?php

require_once 'CRM/Core/Page.php';

class CRM_Agendabe_Page_AgendaBE extends CRM_Core_Page {
  function run() {
    echo civicrm_api3("Event","generateagendabe",array()); 
  }
}
