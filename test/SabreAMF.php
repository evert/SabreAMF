<?php

include 'AMFStream.php';
include 'AMFDeserializer.php';
include 'AMFInputStream.php';


class SabreAMFRequest {

    private $amfContentType = 'application/x-amf';
    protected $credentialsUser = false;
    protected $credentialsPass = false;
    protected $credentialsSupplied = false;
    protected $headers = array();

    public function __construct() {

	
        /*
        $this->filters['deserial'] = new DeserializationFilter();
        $this->filters['describeService'] = new DescribeServiceFilter();
        $this->filters['auth'] = new AuthenticationFilter();
        $this->filters['batch'] = new BatchProcessFilter();
        $this->filters['debug'] = new DebugFilter();
        $this->filters['serialize'] = new SerializationFilter();
       
        */
      

        /*
        $this->actions['adapter'] = new AdapterAction();
        $this->actions['webService'] = new WebServiceAction();
        $this->actions['class'] = new ClassLoaderAction();
        $this->actions['meta'] = new MetaDataAction();
        $this->actions['security'] = new SecurityAction();
        $this->actions['mapper'] = new MapperAction();
        $this->actions['exec'] = new ExecutionAction();
        
        */
        
        //$this->filters['batch']->registerAction($this->actions);
    }

    function execute() {
        header('Content-Type: ' . $this->amfContentType); // define the proper header
        
        if(isset($GLOBALS["HTTP_RAW_POST_DATA"]) && $GLOBALS["HTTP_RAW_POST_DATA"] != "")
        {
       
            $input = $GLOBALS["HTTP_RAW_POST_DATA"];

            $deserializer = new AMFDeserializer();
            $inputstream = new AMFInputStream($input);
           
            $amfpacket = $deserializer->deserialize($inputstream);

            foreach($amfpacket[0]['headers'] as $header) {

                switch ($header['name']) {

                    case 'credentials' :
                        $this->credentialsSupplied = true;
                        $this->credentialsUser = $header['content']['userid'];
                        $this->credentualsPass = $header['content']['pass'];
                        break;

                }

            }

            $this->headers = $amfpacket[0]['headers'];
            $this->calls   = $amfpacket[1];

            /*

	        foreach($this->filters as $key => $filter)
	        {
	        	$filter->invokeFilter($amf); // invoke the first filter in the chain
	        }
	        
	        $outstream = &$amf->getOutputStream(); // grab the output stream
	        $output = $outstream->flush();
	        
	        //echo("Message length:" . strlen($output));
	        //echo(Logger::microtime_float() . "|End\n");
	        //Clear the current output buffer if requested
	        if($this->_looseMode)
	        {
	        	if($this->_obLogging !== FALSE)
	        	{
	        		Logger::setLocation($this->_obLogging);
	        		Logger::write(ob_get_clean());
	        	}
	        	else
	        	{
	        		ob_end_clean();
	        	}
	        }
			$output = $outstream->flush();
			
			//Send content length header
			//Thanks to Alec Horley for pointing out the necessity
			//of this for FlashComm support
			header("Content-length: " . strlen($output));
			
			//$this->_saveRawDataToFile("e:/flashservices/output.amf", $output);
	        print($output); // flush the binary data
            */
        }
    }

    
}

?>
