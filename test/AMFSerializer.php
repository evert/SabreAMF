<?php
/**
 * THIS SOFTWARE IS PROVIDED "AS IS" AND ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING,
 * BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
 * PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
 * IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage io
 */
/**
 * AMFSerializer manages the job of translating PHP objects into
 * the actionscript equivalent via amf.  The main method of the serializer
 * is the serialize method which takes and AMFObject as it's argument
 * and builds the resulting amf body.
 * 
 * @package flashservices
 * @subpackage io
 * @version $Id: AMFSerializer.php,v 1.35 2005/04/02 18:38:16 pmineault Exp $
 */
class AMFSerializer {
    /**
     * The output buffer
     * 
     * @access private 
     * @var string 
     */
    var $out;

    /**
     * The AMFOutputStream instance
     * 
     * @access private 
     * @var string 
     */
    var $amfout;

    /**
     * The pagesize of a recordset
     * 
     * @access private 
     * @var int 
     */
    var $pagesize;

    /**
     * AMFSerializer is the constructor function.  You must pass the
     * method an AMFOutputStream as the single argument.
     * 
     * @param object $stream The AMFOutputStream
     */
    function AMFSerializer(&$stream) {
        $this->out = &$stream; // save
    } 

    /**
     * serialize is the run method of the class.  When serialize is called
     * the AMFObject passed in is read and converted into the amf binary
     * representing the PHP data represented.
     * 
     * @param object $d the AMFObject to serialize
     */
    function serialize(&$d) {
        $this->amfout = &$d;
        $this->out->writeInt(0); // write the version ???
        $count = $this->amfout->numOutgoingHeader();
        $this->out->writeInt($count); // write header count
        for ($i = 0; $i < $count; $i++) {
            $this->writeHeader($i);
        } 
        $count = $this->amfout->numBody();
        $this->out->writeInt($count); // write the body count
        for ($i = 0; $i < $count; $i++) {
            $this->writeBody($i); // start writing the body
        } 
    } 

    /**
     * writeHeader will write all of the header information
     * that the Flash client can react to.  This method is currently
     * unimplemented and is only really used for the NetConnection Debugger
     * and pageable record sets.
     * 
     * @param array $i The header object
     */
    function writeHeader($i) {
        $header = &$this->amfout->getOutgoingHeaderAt($i);
        $this->out->writeUTF($header->getName());
        $this->out->writeByte(0);
        $this->out->writeLong(-1);
        $this->writeData($header->getValue(), -1);
    }

    /**
     * writeBody converts the body part of the AMFObject into the amf binary
     * 
     * @param array $i The numeric index of the body object within the AMFObject
     */
    function writeBody($i) {

        $body = &$this->amfout->getBodyAt($i);
        if (!$body->getIgnoreResults()) {
            $this->pagesize = $body->getPageSize(); // save pagesize
            $this->out->writeUTF($body->getResponseURI()); // write the responseURI header
            $this->out->writeUTF("null"); // write null, haven't found another use for this
            $this->out->writeLong(-1); // always, always there is four bytes of FF, which is -1 of course
            $this->writeData($body->getResults(), $body->getType()); // write the data to the output stream
         }
    }

    /**
     * writeBoolean writes the boolean code (0x01) and the data to the output stream
     * 
     * @param bool $d The boolean value
     */
    function writeBoolean($d) {
        $this->out->writeByte(1); // write the boolean flag
        $this->out->writeByte($d); // write the boolean byte
    } 

    /**
     * writeString writes the string code (0x02) and the UTF8 encoded
     * string to the output stream.
     * 
     * @param string $d The string data
     */
    function writeString($d) {
        $this->out->writeByte(2); // write the string code
        $this->out->writeUTF($d); // write the string value
    } 

    /**
     * writeXML writes the xml code (0x0F) and the XML string to the output stream
     * 
     * @param string $d The XML string
     */
    function writeXML($d) {
        $this->out->writeByte(15);
        $this->out->writeLongUTF($d);
    } 

    /**
     * writeData writes the date code (0x0B) and the date value to the output stream
     * 
     * @param date $d The date value
     */
    function writeDate($d) {
        $this->out->writeByte(11); // write date code
        $this->out->writeDouble($d); // write date (milliseconds from 1970)
        /**
         * write timezone
         * ?? this is wierd -- put what you like and it pumps it back into flash at the current GMT ??
         * have a look at the amf it creates...
         */
        $this->out->writeLong(0);
    }

    /**
     * writeNumber writes the number code (0x00) and the numeric data to the output stream
     * All numbers passed through remoting are floats.
     * 
     * @param int $d The numeric data
     */
    function writeNumber($d) {
        $this->out->writeByte(0); // write the number code
       	$this->out->writeDouble(floatval($d)); // write the number as a double
    } 

    /**
     * writeNull writes the null code (0x05) to the output stream
     */
    function writeNull() {
        $this->out->writeByte(5); // null is only a 0x05 flag
    } 

    /**
     * writeArray first deterines if the PHP array contains all numeric indexes
     * or a mix of keys.  Then it either writes the array code (0x0A) or the
     * object code (0x03) and then the associated data.
     * 
     * @param array $d The php array
     */
    function writeArray($d) 
    {
        $numeric = array(); // holder to store the numeric keys
        $string = array(); // holder to store the string keys
        $len = count($d); // get the total number of entries for the array
        foreach($d as $key => $data) { // loop over each element
            if (is_int($key) && ($key >= 0)) { // make sure the keys are numeric
                $numeric[$key] = $data; // The key is an index in an array
            } else {
                $string[$key] = $data; // The key is a property of an object
            } 
        } 
        $num_count = count($numeric); // get the number of numeric keys
        $str_count = count($string); // get the number of string keys
        
        if ($num_count > 0) { // if there are numeric keys
            $this->array_empty_fill($numeric); // null fill the empty slots to preseve sparsity
            $num_count = count($numeric); // get the new count
        } 

        if ($num_count > 0 && $str_count > 0) { // this is a mixed array
            $this->out->writeByte(8); // write the mixed array code
            $this->out->writeLong($num_count); // write the count of items in the array
            $this->writeObject($numeric + $string); // write the numeric and string keys in the mixed array
        } else if ($num_count > 0) { // this is just an array
            $this->out->writeByte(10); // write the mixed array code
            $this->out->writeLong($num_count); // write the count of items in the array
            for($i = 0 ; $i < $num_count ; $i++) { // write all of the array elements
                $this->writeData($numeric[$i]);
            } 
        } else { // this is an object
            $this->out->writeByte(3); // this is an object so write the object code
            $this->writeObject($string); // write the object name/value pairs
        } 
    } 
    
    /**
     * Write a plain numeric array without anything fancy
     */
    function writePlainArray($d)
    {
    	$num_count = count($d);
        $this->out->writeByte(10); // write the mixed array code
        $this->out->writeLong($num_count); // write the count of items in the array
        for($i = 0 ; $i < $num_count ; $i++) { // write all of the array elements
            $this->writeData($d[$i]);
        } 
    }

    /**
     * array_empty_fill fills in all of the empty numeric slots with null to preserve the
     * indexes of a sparse array.
     */
    function array_empty_fill(&$array, $fill = null) {
        $indexmax = -1;
        for (end($array); $key = key($array); prev($array)) { // loop over the array
            if (is_int($key)) { // if the key is an integer
                if ($key > $indexmax) { // is this key greater than the previous max
                    $indexmax = $key; // save this key as the high
                } 
            } 
        } 
        for ($i = 0; $i <= $indexmax; $i++) { // loop over all possible indexes from 0 to max
            if (!isset($array[$i])) { // is it set already
                $array[$i] = $fill; // fill it with the $fill value
            } 
        } 
        ksort($array); // resort the keys
        reset($array); // reset the pointer to the beginning
    } 

    /**
     * writeObject handles writing a php array with string or mixed keys.  It does
     * not write the object code as that is handled by the writeArray and this method
     * is shared with the CustomClass writer which doesn't use the object code.
     * 
     * @param array $d The php array with string keys
     */
    function writeObject($d) {
        foreach($d as $key => $data) { // loop over each element
            $this->out->writeUTF($key); // write the name of the object
            $this->writeData($data); // write the value of the object
        } 
        $this->out->writeInt(0); // write the end object flag 0x00, 0x00, 0x09
        $this->out->writeByte(9);
    } 

    /**
     * writePHPObject takes an instance of a class and writes the variables defined
     * in it to the output stream.
     * To accomplish this we just blanket grab all of the object vars with get_object_vars
     * 
     * @param object $d The object to serialize the properties
     */
    function writePHPObject($d) {
        $this->out->writeByte(16); // write the custom class code
        $this->out->writeUTF(get_class($d)); // write the class name
        foreach(get_object_vars($d) as $key => $data) { // loop over each element
            $this->out->writeUTF($key); // write the name of the object
            $this->writeData($data); // write the value of the object
        } 
        $this->out->writeInt(0); // write the end object flag 0x00, 0x00, 0x09
        $this->out->writeByte(9);
    } 

    /**
     * writeRecordSet is the abstracted method to write a custom class recordset object.
     * Any recordset from any datasource can be written here, it just needs to be properly formatted
     * beforehand.
     *
     * This was unrolled with at the expense of readability for a 
     * 10 fold increase in speed in large recordsets
     * 
     * @param object $rs The formatted RecordSet object
     */
     
    function writeRecordSet(&$rs) 
    {
    	$ob = "";
    	
    	//Low-level everything here to make things faster
    	//This is the bottleneck of AMFPHP, hence the attention in making things faster
    	
        $this->out->writeByte(16); // write the custom class code
        $this->out->writeUTF("RecordSet"); // write the class name
        $this->out->writeUTF("serverInfo");
        
        //Start writing inner object
        $this->out->writeByte(3); // this is an object so write the object code
        
        //Write total count
        $this->out->writeUTF("totalCount");
        $this->writeNumber($rs->getRowCount());
        
        //Write initial data
        $this->out->writeUTF("initialData");
		
		//Inner numeric array
		$data = $rs->getRecordSet($this->pagesize);
		
		$colnames = $rs->getColumnNames();
		
        $num_count = $rs->numRows; // get the number of numeric keys
        $this->out->writeByte(10); // write the mixed array code
        $this->out->writeLong($num_count); // write the count of items in the array
        $numcols = count($colnames);
        $ob = "";
        $be = $this->out->isBigEndian;
        
        //Allow recordsets to create their own serialized data, which is faster
        //since the recordset array is traversed only once
        if(!isset($rs->serializedData))
        {
	        for($i = 0 ; $i < $num_count ; $i++) { // write all of the array elements
				
				//Write inner inner (data) array
		        //$num_count_inner = count($data[$i]); // get the number of numeric keys
		        //$this->out->writeByte(10); // write the mixed array code
		        //$ob .= pack("c", 10);
		        $ob .= "\12";
		        
		        //$this->out->writeLong($numcols); // write the count of items in the array
		        $ob .= pack('N', $numcols);
		        
		        for($j = 0 ; $j < $numcols; $j++) { // write all of the array elements
		            
		            $d = $data[$i][$j];
					if (is_string($d)) 
			        { // string
			            //Write inner string
			            //$this->out->writeByte(2); // write the string code
			            //$ob .= pack("c", 2);
			            $ob .= "\2";
			            
			            //Low level writeUTF
			            //$os = $d;
			            $os = $this->out->charsetHandler->transliterate($d);
			            //$this->out->writeInt(strlen($os));
				        $ob .= pack('n', strlen($os));
				        //$this->out->outBuffer .= $os; // write the string chars
				        $ob .= $os;
			        }
			        elseif (is_numeric($d)) 
			        { 
			        	// double
			            //$this->out->writeByte(0); // write the number code
			            //$ob .= pack('c', 0);
			            $ob .= "\0";
	        			//$this->out->writeDouble($d); // write the number as a double
				        $b = pack('d', $d); // pack the bytes
				        if ($be) { // if we are a big-endian processor
				            $r = strrev($b);
				        } else { // add the bytes to the output
				            $r = $b;
				        } 
				        $ob .= $r;
			        } 
		            elseif (is_bool($d)) 
		            { 
		            	// boolean
			            //$this->out->writeByte(1); // write the boolean flag
			            //$ob .= pack("c", 1);
			            $ob .= "\1";
	        			//$this->out->writeByte($d); // write the boolean byte
	        			$ob .= pack('c', $d);
			        } 
			        elseif (is_null($d)) 
			        { // null
			            //$this->out->writeByte(5);
			            //$ob .= pack("c", 5);
			            $ob .= "\5";
			        } 
		        } 
	        }
        }
        else
        {
	        $this->out->outBuffer .= $rs->serializedData;
	    }

        //Write cursor
        $this->out->writeUTF("cursor");
        $this->writeNumber(1);
        
        //Write service name
        $this->out->writeUTF("serviceName");
        $this->writeString("PageAbleResult");
        
        //Write column names
        $this->out->writeUTF("columnNames");
        $this->writePlainArray($colnames);
        
        //Write version number
        $this->out->writeUTF("version");
        $this->writeNumber(1);
        
        //Write id
        $this->out->writeUTF("id");
        $this->writeString($rs->getID());
        
        //End inner serverInfo object
        $this->out->writeInt(0); // write the end object flag 0x00, 0x00, 0x09
        $this->out->writeByte(9);
        
    	//End outer recordset object
        $this->out->writeInt(0); // write the end object flag 0x00, 0x00, 0x09
        $this->out->writeByte(9);
        
        //echo(Logger::microtime_float() . "|End write recorset\n");
    }
    
    
    /**
     * writeRecordSetPage writes the page in the correct format to be sent to the Flash client.
     *
     * @rambling Some idiot had the brilliant idea of reverting Cursor to cursor in this. Grr.
     * @param array $d Contains the cursor position and the page data
     */
    function writeRecordSetPage($d) {
        $RecordSetPage = array();
        $RecordSetPage["Cursor"] = $d['Cursor'];
        $RecordSetPage["Page"] = $d['Page'];
        $this->writeObject($RecordSetPage);
    } 

    /**
     * writeCustomClass promotes the writing of the class name and data for CustomClasses
     * 
     * @param string $name The class name
     * @param object $d The class instance object
     */
    function writeCustomClass ($name, $d) {
        $this->out->writeByte(16); // write the custom class code
        $this->out->writeUTF($name); // write the class name
        $this->writeObject($d); // write the classes data
    } 

    /**
     * throwWrongDataTypeError sends the message back to the user that the
     * manual data type passed doesn't match the actual data type returned by
     * the service method.
     * 
     * @param string $dt The data type that was expected but not encountered
     */
    function throwWrongDataTypeError($dt) {
        //trigger_error("The returned data was not of the type " . $dt);
    } 

    /**
     * manualType allows the developer to explicitly set the type of
     * the returned data.  The returned data is validated for most of the
     * cases when possible.  Some datatypes like xml and date have to
     * be returned this way in order for the Flash client to correctly serialize them
     * 
     * recordsets appears top on the list because that will probably be the most
     * common hit in this method.  Followed by the
     * datatypes that have to be manually set.  Then the auto negotiatable types last.
     * The order may be changed for optimization.
     * 
     * @param mixed $d The data
     * @param string $type The type of $d
     */
    function manualType($d, $type) {
        $type = strtolower($type);
        $dbtype = $type; // save this here incase we need it in a few lines
        if(strpos($type, ' ') !== false)
        {
        	$str = explode(' ', $type);
        	//statement is for oci8
        	if(in_array($str[1], array("result", "recordset", "statement")))
        	{
	        	$resultType = $str[0];
	            $type = "__RECORDSET__";
	        }
        }
        switch ($type) {
            case "__recordsetpage__" :
                $this->writeRecordSetPage($d);
                break;
            case "__RECORDSET__" :
                $classname = $resultType . "Adapter"; // full class name
                $includeFile = @include_once(AMFPHP_BASE . "sql/" . $classname . ".php"); // try to load the recordset library from the sql folder
                if (!$includeFile) {
                    if (!@include_once($classname . ".php")) { // try from the same folder as the service
                        trigger_error("The recordset filter class " . $classname . " was not found");
                        exit();
                    } 
                } 
                $recordSet = new $classname($d); // returns formatted recordset
                $this->writeRecordSet($recordSet); // writes the recordset formatted for Flash
                break;
            case "xml" :
                if (is_string($d)) {
                    $this->writeXML($d);
                } else {
                    $this->throwWrongDataTypeError("xml");
                } 
                break;
            case "date" :
                if (is_float($d)) {
                    $this->writeDate($d);
                } else {
                    $this->throwWrongDataTypeError("date");
                } 
                break;
            case "boolean" :
                if (is_bool($d)) {
                    $this->writeBoolean($d);
                } else {
                    $this->throwWrongDataTypeError("boolean");
                } 
                break;
            case "string" :
                if (is_string($d)) {
                    $this->writeString($d);
                } else {
                    $this->throwWrongDataTypeError("string");
                } 
                break;
            case "double" :
                if (is_double($d)) {
                    $this->writeNumber($d);
                } else {
                    $this->throwWrongDataTypeError("double");
                } 
                break;
            case "integer" :
                if (is_int($d)) {
                    $this->writeNumber($d);
                } else {
                    $this->throwWrongDataTypeError("integer");
                } 
                break;
            case "object" :
            	//Note that if you get a Client.Data.UnderFlow
            	//you need to include the custom class before attempting to send to Flash
                if (is_object($d)) {
                    $this->writePHPObject($d);
                } else {
                    $this->throwWrongDataTypeError("object");
                } 
                break;
            case "array" :
                if (is_array($d)) {
                    $this->writeArray($d);
                } else {
                    $this->throwWrongDataTypeError("array");
                } 
                break;
            case "null" :
                if (is_null($d)) {
                    $this->writeNull();
                } else {
                    $this->throwWrongDataError("null");
                } 
                break;
            case "resultset" :
            case "recordset" :
            case "resource" :
                if (is_resource($d)) {
                    $this->manualType($d, get_resource_type($d));
                } else {
                    $this->throwWrongDataTypeError("resource");
                } 
                break;
            default: 
                // non of the above so lets assume its a Custom Class thats defined in the client
                $this->writeCustomClass($type, $d);
                // trigger_error("Unsupported Datatype");
                break;
        } 
    } 

    /**
     * writeData checks to see if the type was declared and then either
     * auto negotiates the type or relies on the user defined type to
     * serialize the data into amf
     *
     * Note that autoNegotiateType was eliminated in order to tame the 
     * call stack which was getting huge and was causing leaks
     * 
     * @param mixed $d The data
     * @param string $type The optional type
     */
    function writeData($d, $type = -1) {
        if ($type == -1) 
        {
	        if (is_numeric($d)) 
	        { // double
	            $this->writeNumber($d);
	        } 
	        elseif (is_string($d)) 
	        { // string
	            $this->writeString($d);
	        } 
	        elseif (is_bool($d)) 
	        { // boolean
	            $this->writeBoolean($d);
	        } 
	        elseif (is_array($d)) 
	        { // array
	            $this->writeArray($d);
	        } 
	        elseif (is_object($d)) 
	        { // object
	            $this->writePHPObject($d);
	        } 
	        elseif (is_null($d)) 
	        { // null
	            $this->writeNull();
	        } 
	        elseif (is_resource($d)) 
	        { // resource
	            $type = get_resource_type($d);
	            $this->manualType($d, $type);
	        } 
	        else
	        {
	        	$this->manualType($d, gettype($d));
	        }
        }
        else
        {
        	$this->manualType($d, $type); // try the manual route if we didn't return already
        }
    } 
}

?>
