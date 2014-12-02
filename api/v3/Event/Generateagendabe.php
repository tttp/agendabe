<?php

function civicrm_api3_event_generateagendabe ($params) {
  $fields="title,start_date,event_type,intro_text,description,end_date,event_type_id,is_monetary,loc_block_id";
  //$fields="title,start_date,loc_block_id";

  $r = civicrm_api3 ("event","get", array (
    "start_date" => array (">=" => "now"),
    "return" => $fields,
    "options" => array ("limit" => 100),
  ));

  $locs = civicrm_api3("LocBlock","get",array("options"=>array("limit",1000)));
  $locs = $locs["values"];

  $types = civicrm_api3('Event', 'getoptions', array(
   'field' => "event_type_id",
  ));
  $types= $types["values"];

  $xml = new SimpleXMLElement('<events/>');
  foreach ($r["values"] as $event) {
    $xe = $xml->addChild("event");
    $xe->addChild("id",$event["id"]);
    $xe->addChild("category",$types[$event["event_type_id"]]);
    $d=$xe->addChild("detail");
    $d->addChild("title",$event["title"]); 
    $d->addChild("shortDescription",CRM_Utils_String::htmlToText($event["intro_text"])); 
    $d->addChild("longDescription",CRM_Utils_String::htmlToText($event["description"])); 

    $d = $xe->addChild("dates");
    $d1 = $d->addChild("date");
    $d1->addChild("day",substr($event["start_date"],0,10));
    $d1->addChild("hourstart",substr($event["start_date"],11));
    $d1->addChild("hourend",substr($event["end_date"],11));
  //  foreach ($event as $k => $v) {    $xe->addChild($k,$v);  }
    if ($event["loc_block_id"] && array_key_exists ($event["loc_block_id"],$locs)) {
      $l = $locs[$event["loc_block_id"]];
      if (array_key_exists("address_id",$l) && !array_key_exists("address",$l)) {
        $r = civicrm_api3 ("address","getsingle", array ("id"=> $l["address_id"]));
        $locs[$event["loc_block_id"]]["address"] = $r;
      }
      $p = $xe->addChild("place");
      $p->addChild("id",$locs[$event["loc_block_id"]]["address"]["id"]);
      $p->addChild("name",$locs[$event["loc_block_id"]]["address"]["name"]);
      $p->addChild("street",$locs[$event["loc_block_id"]]["address"]["street_address"]);
      $p->addChild("zip",$locs[$event["loc_block_id"]]["address"]["postal_code"]);
      $p->addChild("city",$locs[$event["loc_block_id"]]["address"]["city"]);

    }
  } 
  print $xml->asXML();
}
