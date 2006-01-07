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
 * AMFStream class is the base class for AMFInputStream and AMFOutputStream.
 * 
 * @package flashservices
 * @subpackage io
 * @version $Id: AMFStream.php,v 1.3 2005/03/26 03:28:03 pmineault Exp $
 */

class AMFStream{
	/**
     * The byte order variable
     * 
     * @access protected 
     * @var string 
     */
    var $isBigEndian;
	
	function AMFStream(){
		$tmp = pack("d", 1); // determine the multi-byte ordering of this machine temporarily pack 1
        $this->isBigEndian = ($tmp == "\0\0\0\0\0\0\360\77");
	}
}
?>
