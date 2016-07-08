# tplparser

Use the flexibility of Smarty with the powerful TCPDF: 
generate PDF files with PHP from Smarty templates

### Installation

Copy tplparser folder into your php project, then include tplparser.class.php

### Usage

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


Make sure to give (if Linux/Debian) write permission to templates folder

### License

- TCPDF / HTML2PDF: GPL
- SMARTY: GPL
- TPLPARSER: MIT