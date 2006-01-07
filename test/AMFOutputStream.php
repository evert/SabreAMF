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
 * AMFOutputStream is the class that handles writing the data types as their associated binary representations
 * to a buffer which can be later flushed.
 * 
 * @package flashservices
 * @subpackage io
 * @version $Id: AMFOutputStream.php,v 1.12 2005/03/26 03:28:03 pmineault Exp $
 */
require_once(AMFPHP_BASE . "io/AMFStream.php");

class AMFOutputStream extends AMFStream{
    /**
     * The output buffer holder
     * 
     * @access private 
     * @var string 
     */
    var $outBuffer;

    /**
     * The constructor for the output stream class.
     * 
     * Here we initialize the output buffer and determine the byte order
     * of the system.  The byte order will be promoted in future releases.
     */
    function AMFOutputStream() {
    	parent::AMFStream();
        $this->outBuffer = ""; // the buffer
        $this->charsetHandler = new CharsetHandler('phptoflash');
    } 

    /**
     * writeByte writes a singe byte to the output stream
     * 0-255 range
     * 
     * @param int $b An int that can be converted to a byte
     */
    function writeByte($b) {
        $this->outBuffer .= pack("c", $b); // use pack with the c flag
    } 

    /**
     * writeInt takes an int and writes it as 2 bytes to the output stream
     * 0-65535 range
     * 
     * @param int $n An integer to convert to a 2 byte binary string
     */
    function writeInt($n) {
        $this->outBuffer .= pack("n", $n); // use pack with the n flag
    } 

    /**
     * writeLong takes an int, float or double and converts it to a 4 byte binary string and
     * adds it to the output buffer
     * 
     * @param long $l A long to convert to a 4 byte binary string
     */
    function writeLong($l) {
        $this->outBuffer .= pack("N", $l); // use pack with the N flag
    } 

    /**
     * writeUTF takes and input string, writes the length as an int and then
     * appends the string to the output buffer
     * 
     * @param string $s The string less than 65535 characters to add to the stream
     */
    function writeUTF($s) {
    	$os = $this->charsetHandler->transliterate($s);
        $this->writeInt(strlen($os)); // write the string length - max 65535
        $this->outBuffer .= $os; // write the string chars
    } 

    /**
     * writeLongUTF will write a string longer than 65535 characters.
     * It works exactly as writeUTF does except uses a long for the length
     * flag.
     * 
     * @param string $s A string to add to the byte stream
     */
    function writeLongUTF($s) {
    	$os = $this->charsetHandler->transliterate($s);
        $this->writeLong(strlen($os));
        $this->outBuffer .= $os; // write the string chars
    } 

    /**
     * writeDouble takes a float as the input and writes it to the output stream.
     * Then if the system is big-endian, it reverses the bytes order because all
     * doubles passed via remoting are passed little-endian.
     * 
     * @param double $d The double to add to the output buffer
     */
    function writeDouble($d) {
        $b = pack("d", $d); // pack the bytes
        if ($this->isBigEndian) { // if we are a big-endian processor
            $r = strrev($b);
            /*
            $r = "";
            for($byte = 7 ; $byte >= 0 ; $byte--) { // reverse the bytes
                $r .= $b[$byte];
            } 
            */
        } else { // add the bytes to the output
            $r = $b;
        } 
        $this->outBuffer .= $r;
    } 

    /**
     * flush returns the contents of the output buffer.
     * 
     * @return string The output buffer contents.
     */
    function flush() {
        return $this->outBuffer; // return the output buffer contents
    } 
} 

?>