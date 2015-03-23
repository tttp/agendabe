<?php

function civicrm_api3_event_generateagendabe ($params) {
  $fields="title,start_date,event_type,intro_text,description,end_date,event_type_id,is_monetary,loc_block_id";
  //$fields="title,start_date,loc_block_id";

  $r = civicrm_api3 ("event","get", array (
    "start_date" => array (">=" => "now"),
    "return" => $fields,
    'event_type_id' => array('NOT IN' => array(17, 22, 16, 19, 21, 20,23)),
    "options" => array ("limit" => 1000),
  ));

  $locs = civicrm_api3("LocBlock","get",array("options"=>array("limit",1000)));
  $locs = $locs["values"];
  $organizer = civicrm_api3("Domain","getsingle");

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
    $d->addAttribute("language","NL");
    $d->addChild("title",$event["title"]); 
    $d->addChild("url",CRM_Utils_System::url( 'civicrm/event/info',"id={$event['id']}&amp;reset=1",true, null, false,true)); 
    if ($event["summary"])
      $d->addChild("shortdescription",CRM_Utils_String::htmlToText($event["summary"])); 
    $d->addChild("longdescription",CRM_Utils_String::htmlToText($event["description"])); 

    $d = $xe->addChild("dates");
    $d1 = $d->addChild("date");
    $d1->addChild("day",substr($event["start_date"],0,10));
    $d1->addChild("hourstart",substr($event["start_date"],11,5));
    $d1->addChild("hourend",substr($event["end_date"],11,5));
  //  foreach ($event as $k => $v) {    $xe->addChild($k,$v);  }
    preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $event["description"], $img);
    if ($img[1]) {
      $m = $xe->addChild("medias")->addChild("media");
      $m->addAttribute("type","photo");
      $m->addChild("url",$img[1]);
    }    


    if ($event["custom_311"]) {
      $event["custom_311_id"];
      $t = civicrm_api3("contact","getsingle",array("id"=>$event["custom_311_id"],"return"=>"display_name,street_address,postal_code,city"));
      $o = $xe->addChild("organizer");
      $o->addChild("id",$t["id"]);
      $o->addChild("name",$t["display_name"]);
      $o->addChild("street",$t["street_address"]);
      $o->addChild("zip",$t["postal_code"]);
      $o->addChild("city",$t["city"]);
    } else {
      $o = $xe->addChild("organizer");
      $o->addChild("id",$organizer["contact_id"]);
      $o->addChild("name",$organizer["name"]);
      $o->addChild("street",$organizer["domain_address"]["street_address"]);
      $o->addChild("zip",$organizer["domain_address"]["postal_code"]);
      $o->addChild("city",$organizer["domain_address"]["city"]);
    }
    if ($event["loc_block_id"] && array_key_exists ($event["loc_block_id"],$locs)) {
      $l = $locs[$event["loc_block_id"]];
      if (array_key_exists("address_id",$l) && !array_key_exists("address",$l)) {
        $r = civicrm_api3 ("address","getsingle", array ("id"=> $l["address_id"]));
        $locs[$event["loc_block_id"]]["address"] = $r;
      }
      if (array_key_exists("phone_id",$l) && !array_key_exists("address",$l)) {
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
