# tplparser

Use the flexibility of Smarty with the powerful TCPDF: 
generate PDF files with PHP from Smarty templates

### Installation

Copy tplparser folder into your php project, then include tplparser.class.php

### Usage
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


Make sure to give (if Linux/Debian) write permission to templates folder

### License

- TCPDF / HTML2PDF: GPL
- SMARTY: GPL
- TPLPARSER: MIT