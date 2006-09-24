<?php

    /**
     * SabreAMF_ClassNotFoundException
     * 
     * @package SabreAMF
     * @version $Id$
     * @copyright 2006 Renaun Erickson
     * @author Renaun Erickson (http://renaun.com/blog)
     * @author Evert Pot (http://www.rooftopsolutions.nl) 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */

    /**
     * Detailed exception 
     */
    require_once 'SabreAMF/DetailException.php';

    /**
     * This is the receipt for ClassException and default values reflective of ColdFusion RPC faults
     *
     * @uses SabreAMF_DetailException
     */
    class SabreAMF_ClassNotFoundException extends Exception implements SabreAMF_DetailException {

		/**
		 *	Constructor
		 */
		public function __construct( $classname ) {
			// Specific message to ClassException
			$this->message = "Could not locate class " . $classname;
			$this->code = "Server.Processing";

			// Call parent class constructor
			parent::__construct( $this->message );
		}

        public function getDetail() {

            return "Please check that the given servicename is correct and that the class exists.";

        }

    }

?>
