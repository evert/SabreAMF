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
 * AMFInputStream class is a wrapper to extend the functionality of of the raw input stream.
 * 
 * The input stream object takes the raw data and reads it in linearly by n number of bytes where
 * n is the number of bytes dictated by the datatype the corresponding method requires.  For example
 * the readByte accessor grabs a single byte from the stream and advances the current cursor where the
 * read double method gets the next 8 bytes from the stream and advances the cursor.
 * 
 * @package flashservices
 * @subpackage io
 * @version $Id: AMFInputStream.php,v 1.14 2005/03/26 20:50:58 pmineault Exp $
 */

class AMFInputStream extends AMFStream{
    /**
     * The raw data input
     * 
     * @access private 
     * @var string 
     */
    var $raw_data;

    /**
     * The current seek cursor of the stream
     * 
     * @access private 
     * @var int 
     */
    var $current_byte;

    /**
     * The length of the stream.  Since this class is not actually using a stream
     * the entire content of the stream is passed in as the initial argument so the
     * length can be determined.
     * 
     * @access private 
     * @var int 
     */
    var $content_length;
    
    /**
     * AMFInputStream constructor
     * 
     * @param string $rd The raw data stream from remoting
     */
    function AMFInputStream(&$rd) {
    	parent::AMFStream();
        $this->current_byte = 0;
        $this->raw_data = &$rd; // store the stream in this object
        $this->content_length = strlen($this->raw_data); // grab the total length of this stream
        //$this->charsetHandler = new CharsetHandler('flashtophp');
    } 
    
    /**
     * readByte grabs the next byte from the data stream and returns it.
     * 
     * @return int The next byte converted into an integer
     */
    function readByte() {
        return ord($this->raw_data[$this->current_byte++]); // return the next byte
    }

    /**
     * readInt grabs the next 2 bytes and returns the next two bytes, shifted and combined
     * to produce the resulting integer
     * 
     * @return int The resulting integer from the next 2 bytes
     */
    function readInt() {
        return ((ord($this->raw_data[$this->current_byte++]) << 8) |
            ord($this->raw_data[$this->current_byte++])); // read the next 2 bytes, shift and add
    } 

    /**
     * readLong grabs the next 4 bytes shifts and combines them to produce an integer
     * 
     * @return int The resulting integer from the next 4 bytes
     */
    function readLong() {
        return ((ord($this->raw_data[$this->current_byte++]) << 24) |
            (ord($this->raw_data[$this->current_byte++]) << 16) |
            (ord($this->raw_data[$this->current_byte++]) << 8) |
            ord($this->raw_data[$this->current_byte++])); // read the next 4 bytes, shift and add
    } 

    /**
     * readDouble reads the floating point value from the bytes stream and properly orders
     * the bytes depending on the system architecture.
     * 
     * @return float The floating point value of the next 8 bytes
     */
    function readDouble() {
        if ($this->isBigEndian) {
            $invertedBytes = ""; // container to store the reversed bytes
            for($i = 7 ; $i >= 0 ; $i--) { // create a loop with a backwards index
                $invertedBytes .= $this->raw_data[$this->current_byte + $i]; // grab the bytes in reverse order from the backwards index
            } 
            $this->current_byte += 8; // move the seek head forward 8 bytes
        } else {
            $invertedBytes = ""; // container to store the bytes
            for($i = 0 ; $i < 8 ; $i++) { // create a loop with a forwards index
                $invertedBytes .= $this->raw_data[$this->current_byte + $i]; // grab the bytes in forward order
            }
            $this->current_byte += 8; // move the seek head forward
        } 
        //echo($invertedBytes);
        $zz = unpack("dflt", $invertedBytes); // unpack the bytes
        return $zz['flt']; // return the number from the associative array
    } 

    /**
     * readUTF first grabs the next 2 bytes which represent the string length.
     * Then it grabs the next (len) bytes of the resulting string.
     * 
     * @return string The utf8 decoded string
     */
    function readUTF() {
        $length = $this->readInt(); // get the length of the string (1st 2 bytes)
        $val = substr($this->raw_data, $this->current_byte, $length); // grab the string
        $this->current_byte += $length; // move the seek head to the end of the string
        return ($val); // return the string
    } 

    /**
     * readLongUTF first grabs the next 4 bytes which represent the string length.
     * Then it grabs the next (len) bytes of the resulting in the string
     * 
     * @return string The utf8 decoded string
     */
    function readLongUTF() {
        $length = $this->readLong(); // get the length of the string (1st 4 bytes)
        $val = substr($this->raw_data, $this->current_byte, $length); // grab the string
        $this->current_byte += $length; // move the seek head to the end of the string
        return $this->charsetHandler->transliterate($val); // return the string
    } 
} 
?>
