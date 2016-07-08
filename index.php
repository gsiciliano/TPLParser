<?php
    require_once(dirname(__FILE__).'/tplparser/tplparser.class.php');
    /* Create parser object
        with 'L' : landscape
             'A4': A4 page size
             'it': Italian locale
             array(2,2,2,2): margins
    */
    $parser = new TPLPARSER('L','A4','it',array(2,2,2,2));

    /* setting the template name into the object */
    $tplpath = 'templates/test.tpl';
    $parser->setTplName($tplpath);
    
    /* add constants */
    $const['const_1'] = 'this is a test'; 
    $parser->assignConst($const);

    /* add data array with recursive elements */
    $data = array();
    for ($i=0;$i<5;$i++){
      $item = array('element'=>'this is a recursive_test: '.$i.' element');  
      array_push($data, $item);  
    }
    $parser->assignBodyData($data,'bodydataset');

    /* render the output */
    $parser->output();
    exit;
