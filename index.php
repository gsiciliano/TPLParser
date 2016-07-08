<?php
    require_once(dirname(__FILE__).'/tplparser/tplparser.class.php');
    $parser = new TPLPARSER('L','A4','it',array(2,2,2,2));
    $tplpath = 'templates/test.tpl';
    $parser->setTplName($tplpath);
    
    $const['const_1'] = 'this is a test'; 
    $parser->assignConst($const);
    $data = array();
    for ($i=0;$i<5;$i++){
      $item = array('element'=>'this is a recursive_test: '.$i.' element');  
      array_push($data, $item);  
    }
    $parser->assignBodyData($data,'bodydataset');
    $parser->output();
    exit;
