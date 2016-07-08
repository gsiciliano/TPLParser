<?php
/**
 * TPLPARSER
 *
 * Uses HTML2PDF e SMARTY (under licenza LGPL)
 * distribuited under LGPL
 *
 * @author  Gianluca Siciliano
 * @version 1.00
 */
if (!defined('__CLASS_TPLPARSER__')) {

    define('CLASS_HTML2PDF_USED_VERSION', '4.03');
    define('CLASS_SMARTY_USED_VERSION', '3.1.13');
    define('__CLASS_TPLPARSER__', '1.00');
    define('CLASS_TPLPARSER_BASE_DIR', dirname(__FILE__));
    
    require_once(CLASS_TPLPARSER_BASE_DIR.'/_smarty/libs/Smarty.class.php');
    require_once(CLASS_TPLPARSER_BASE_DIR.'/_html2pdf/html2pdf.class.php');

    class TPLPARSER
    {
        public    $html2pdf = null;    
        public    $smarty = null;
        protected $_tplname = null;
        protected $_pages = null;
        protected $_follows = null;
        protected $_datasetname = null;
        protected $_orientation = null;
        protected $_format = null;
        protected $_language = null;   
        protected $_marges = null;
        protected $_pageTable = null;
        
        protected $_wmtext      = null;
        protected $_wmalpha     = null;
        protected $_wmfontname  = null;
        protected $_wmfontstyle = null;
        protected $_wmfontsize  = null;

        
        /**
         * class constructor
         *
         * @access public
         * @param  string    $orientation page orientation, same as TCPDF
         * @param  mixed     $format      The format used for pages, same as TCPDF
         * @param  string    $language    fr, en, it...
         * @param  string    $marges      left,right,top,bottom
         * @return TPLPARSER $this
         */
        public function __construct($orientation = 'P', $format = 'A4', $language='it', $marges = array(5, 5, 5, 8)){
            $this->_orientation = $orientation;
            $this->_format = $format;
            $this->_language = $language;
            $this->_marges =$marges;
            $this->smarty = new smarty;
            $this->smarty->caching = false;
            return $this;
        }
        
        /**
         * Destructor
         *
         * @access public
         * @return null
         */
        public function __destruct()
        {
        }
        
        /**
         * setTplName
         *
         * setup the tpl's name used for fetching data
         * 
         * @access public
         * @param string    $tplName Name of the template  
         * 
         */
        public function setTplName($tplName){
            $this->_tplname = $tplName;
            $this->smarty->setCompileDir(dirname($tplName . DS));
        }    

        /**
         * assignPageTable
         *
         * setup a table with pages (use instead of couple assignConst / assignBodyData)
         * 
         * @access public
         * @param mixed     $pageNr     page number (from 1 to...)
         * @param array     $const      array of couple Name => Value
         * @param array     $data       datarow with datavalues
         * @param mixed     $dataname   dataname name of the section in template
         * 
         */
        public function assignPageTable($pageNr=1, $const=null, $data=null, $dataname=null){
            if ($this->smarty){
                $this->_pageTable[$pageNr-1]['pagenr'] = $pageNr;
                $this->_pageTable[$pageNr-1]['const'] = $const;
                $this->_pageTable[$pageNr-1]['data'] = $data;
                $this->_pageTable[$pageNr-1]['dataname'] = $dataname;
            }
        }
        /**
         * writePageTable
         *
         * setup a table with pages (use instead of couple assignConst / assignBodyData)
         * 
         * @access public
         * @param mixed     $pageNr     page number (from 1 to...)
         * @param array     $const      array of couple Name => Value
         * @param array     $data       datarow with datavalues
         * @param mixed     $dataname   dataname name of the section in template
         * 
         */
        public function writePageTable($pageNr=1, $const=null, $data=null, $dataname=null){
            if ($this->smarty){
                $this->_pageTable[$pageNr-1]['pagenr'] = $pageNr;
                $this->_pageTable[$pageNr-1]['const'] = $const;
                $this->_pageTable[$pageNr-1]['data'] = $data;
                $this->_pageTable[$pageNr-1]['dataname'] = $dataname;
                if (!$this->html2pdf){
                    $this->html2pdf = new html2pdf($this->_orientation,$this->_format,$this->_language,true,'UTF-8',$this->_marges);
                }    
                $this->html2pdf->pdf->SetDisplayMode('default');
                $pageitem = $this->_pageTable[$pageNr-1];
                $this->smarty->clearAllAssign();
                $this->smarty->assign('pagenr',$pageitem['pagenr']);
                if (isset($pageitem['dataname'])){$this->smarty->assign($pageitem['dataname'],$pageitem['data']);}
                if (isset($pageitem['const']))   {$this->smarty->assign($pageitem['const']);}
                $this->html2pdf->writeHTML($this->smarty->fetch($this->_tplname));
                if ($this->_wmtext){
                    $this->setWaterMark($this->_wmtext, $this->_wmalpha, $this->_wmfontname,$this->_wmfontstyle,$this->_wmfontsize);
                }    
            }
        }
        
        /**
         * assignConst
         *
         * setup smarty's constants inside the TPL (param can be an array)
         * 
         * @access public
         * @param mixed     $constName  const name
         * @param mixed     $value      const value
         * 
         */
        public function assignConst($constName, $value){
            $this->_pageTable = null;
            if ($this->smarty){
                $this->smarty->assign($constName,$value);
            }
        }
        
        /**
         * assignBodyData
         *
         * setup smarty's data for SECTIONS inside the TPL
         * il maxelem not NULL insert page breaks
         * 
         * @access public
         * @param array     $data       datarow with datavalues
         * @param mixed     $dataname   dataname name of the section in template
         * @param numeric   $maxelem    num of max elements per page (counted for pagebreaks)
         */
        public function assignBodyData($data, $dataname, $maxelem=null){
            $this->_pageTable = null;
            $this->_datasetname = $dataname; 
            $c=0; $p=0;
            if (!$maxelem == null){
                foreach ($data as $dataitem){
                    if ($c == $maxelem){
                      $this->_pages[$p] = $rowset;
                      $this->_follows[$p] = true;
                      $rowset=null;
                      $c=0;$p++;
                    }
                    $rowset[$c] = $dataitem;
                    $c++;
                }    
                if ($c <> $maxelem){
                    $this->_pages[$p] = $rowset;
                    $this->_follows[$p] = false;
                }    
            } else {
                $this->_pages[$p] = $data;   
                $this->_follows[$p] = false;
 
            }
        }    
        /**
         * assignWaterMark
         *
         * assing WaterMark with requested text
         * 
         * 
         * @access public
         * @param  mixed    $text       text to watermark
         * @param  mixed    $alpha      weight of the watermark
         * @param  mixed    $fontname   font's name
         * @param  mixed    $fontstyle  font's style
         * @param  mixed    $fontsize   font's size
         */
        public function assignWaterMark($text, $fontsize=35, $fontname='Courier', $fontstyle='', $alpha='0.50'){

            $this->_wmtext      = $text;
            $this->_wmalpha     = $alpha;            
            $this->_wmfontname  = $fontname;            
            $this->_wmfontstyle = $fontstyle;            
            $this->_wmfontsize  = $fontsize;            
        } 
         
        /**
         * setWaterMark
         *
         * setup WaterMark with requested text
         * 
         * 
         * @access protected
         * @param  mixed    $text       text to watermark
         * @param  mixed    $alpha      weight of the watermark
         * @param  mixed    $fontname   font's name
         * @param  mixed    $fontstyle  font's style
         * @param  mixed    $fontsize   font's size
         */
        
        protected function setWaterMark($text, $alpha, $fontName, $fontStyle, $fontSize){
            
            // Simple watermark
            // This will set it to page one and lay over anything written before it on the first page
            $stringWidth = $this->html2pdf->pdf->GetStringWidth($text,$fontName,$fontStyle,$fontSize,false);
            $factor = round(($stringWidth * sin(deg2rad(45))) / 2 ,0);
            $this->html2pdf->pdf->setPage( 1 );
            // Get the page width/height
            $myPageWidth = $this->html2pdf->pdf->getPageWidth();
            $myPageHeight = $this->html2pdf->pdf->getPageHeight();
            // Find the middle of the page and adjust.
            $myX = ( $myPageWidth / 2 ) - $factor;
            $myY = ( $myPageHeight / 2 ) + $factor;
            // Set the transparency of the text to really light
            $this->html2pdf->pdf->setAlpha($alpha);
            // Rotate 45 degrees and write the watermarking text
            $this->html2pdf->pdf->startTransform();
            $this->html2pdf->pdf->Rotate(45, $myX, $myY);
            $this->html2pdf->pdf->SetFont($fontName, $fontStyle, $fontSize);
            $this->html2pdf->pdf->Text($myX, $myY,$text); 
            $this->html2pdf->pdf->stopTransform();
            // Reset the transparency to default
            $this->html2pdf->pdf->setAlpha(1);            
            
        }
        
        /**
         * output
         *
         * publish pdf on the browser page
         * @access public
         * @param string        $filename  filename for output (if required)
         * @param string        $opt       options  for saving (if required)
         * @return HTML2PDF->Output
         */
        public function output($filename=null, $opt=null){
            if (!$filename) {$filename = 'mypdf.pdf';}
            if ($this->_tplname){
                $this->html2pdf = new html2pdf($this->_orientation,$this->_format,$this->_language,true,'UTF-8',$this->_marges);
                $this->html2pdf->pdf->SetDisplayMode('default');
                if (isset($this->_pageTable)){
                   foreach ($this->_pageTable as $pageitem){
                      $this->smarty->clearAllAssign();
                      $this->smarty->assign('pagenr',$pageitem['pagenr']);
                      if (isset($pageitem['dataname'])){$this->smarty->assign($pageitem['dataname'],$pageitem['data']);}
                      if (isset($pageitem['const']))   {$this->smarty->assign($pageitem['const']);}
                      $this->html2pdf->writeHTML($this->smarty->fetch($this->_tplname));
                   }    
                } else {
                    $pcounter=1;   
                    if ($this->_pages){
                     $k = 0;   
                     foreach ($this->_pages as $pageitem){
                         $this->smarty->assign($this->_datasetname,$pageitem);
                         $this->smarty->assign('pagenr',$pcounter);
                         if ($this->_follows[$k]){$this->smarty->assign('rowbreak',true);}
                         else{$this->smarty->assign('rowbreak',false);}
                         $this->html2pdf->writeHTML($this->smarty->fetch($this->_tplname));
                         $pcounter++;
                         $k++;
                     }    
                    } else {
                         $this->smarty->assign('pagenr',$pcounter);
                         $this->smarty->assign('rowbreak',false);
                         $this->html2pdf->writeHTML($this->smarty->fetch($this->_tplname));
                    }        
                }
            }        
            if ($this->_wmtext){
                $this->setWaterMark($this->_wmtext, $this->_wmalpha, $this->_wmfontname,$this->_wmfontstyle,$this->_wmfontsize);
            }    
            return $this->html2pdf->Output($filename,$opt);
        }
        /**
         * output_2
         *
         * publish pdf on the browser page (for use with writePageTable)
         * @access public
         * @param string        $filename  filename for output (if required)
         * @param string        $opt       options  for saving (if required)
         * @return HTML2PDF->Output
         */
        public function output_2($filename=null, $opt=null){
            if (!$filename) {$filename = 'mypdf.pdf';}
            return $this->html2pdf->Output($filename,$opt);
        }
        /**
         * outputDBG
         *
         * publish pdf on the browser page
         * @access public
         * @param string        $filename  filename for output (if required)
         * @param string        $opt       options  for saving (if required)
         * @return HTML2PDF->Output
         */
        public function outputDBG($filename=null, $opt=null){
            if (!$filename) {$filename = 'mypdf.pdf';}
            if ($this->_tplname){
                $this->html2pdf = new html2pdf($this->_orientation,$this->_format,$this->_language,true,'UTF-8',$this->_marges);
                $this->html2pdf->setModeDebug();
                $this->html2pdf->pdf->SetDisplayMode('default');
                if (isset($this->_pageTable)){
                   foreach ($this->_pageTable as $pageitem){
                      $this->smarty->clearAllAssign();
                      $this->smarty->assign('pagenr',$pageitem['pagenr']);
                      if (isset($pageitem['dataname'])){$this->smarty->assign($pageitem['dataname'],$pageitem['data']);}
                      if (isset($pageitem['const']))   {$this->smarty->assign($pageitem['const']);}
                      $this->html2pdf->writeHTML($this->smarty->fetch($this->_tplname));
                   }    
                } else {
                    $pcounter=1;   
                    if ($this->_pages){
                     $k = 0;   
                     foreach ($this->_pages as $pageitem){
                         $this->smarty->assign($this->_datasetname,$pageitem);
                         $this->smarty->assign('pagenr',$pcounter);
                         if ($this->_follows[$k]){$this->smarty->assign('rowbreak',true);}
                         else{$this->smarty->assign('rowbreak',false);}
                         $this->html2pdf->writeHTML($this->smarty->fetch($this->_tplname));
                         $pcounter++;
                         $k++;
                     }    
                    } else {
                         $this->smarty->assign('pagenr',$pcounter);
                         $this->smarty->assign('rowbreak',false);
                         $this->html2pdf->writeHTML($this->smarty->fetch($this->_tplname));
                    }        
                }
            }        
            if ($this->_wmtext){
                $this->setWaterMark($this->_wmtext, $this->_wmalpha, $this->_wmfontname,$this->_wmfontstyle,$this->_wmfontsize);
            }    
            return $this->html2pdf->Output($filename,$opt);
        }
        
        /**
         * saveTo
         *
         * save pdf to disk in client or server
         * @access public
         * @param string        $filename  filename for output (if required)
         * @param string        $path      path for saving (if required)
         * @return HTML2PDF->Output
         */
        public function saveTo($filename,$path=null){
            if ($path){
                if (substr($path,-1,1) == '/'){
                    $fileout = $path.$filename;
                } else {
                    $fileout = $path.'/'.$filename;
                }    
                $opt = 'F'; // save on server
            } else {
                $opt = 'D'; // download by client
                $fileout = $filename;
            }    
            return $this->output($fileout,$opt);
        }   
        /**
         * saveTo_2
         *
         * save pdf to disk in client or server (for use with writePageTable)
         * @access public
         * @param string        $filename  filename for output (if required)
         * @param string        $path      path for saving (if required)
         * @return HTML2PDF->Output
         */
        public function saveTo_2($filename,$path=null){
            if ($path){
                if (substr($path,-1,1) == '/'){
                    $fileout = $path.$filename;
                } else {
                    $fileout = $path.'/'.$filename;
                }    
                $opt = 'F'; // save on server
            } else {
                $opt = 'D'; // download by client
                $fileout = $filename;
            }    
            return $this->output_2($fileout,$opt);
        }   
    }    
}
?>
