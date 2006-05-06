<?php

    /**
     * SabreAMF_AMF3_Const 
     * 
     * @package SabreAMF
     * @subpackage AMF3
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */
    final class SabreAMF_AMF3_Const {

		const DT_UNDEFINED   = 0x00;
        const DT_NULL        = 0x01;
        const DT_BOOL_FALSE  = 0x02;
        const DT_BOOL_TRUE   = 0x03;
        const DT_INTEGER     = 0x04;
        const DT_NUMBER      = 0x05;
        const DT_STRING      = 0x06;
        const DT_XML         = 0x07;
        const DT_DATE        = 0x08;
        const DT_ARRAY       = 0x09;
        const DT_OBJECT      = 0x0A;
        const DT_XMLSTRING   = 0x0B;

        const ET_OBJ_INLINE   = 0x01;
        const ET_CLASS_INLINE = 0x02;
        const ET_PROPDEF      = 0x04;
        const ET_PROPSERIAL   = 0x08;

   }


?>
