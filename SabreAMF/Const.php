<?php

    /**
     * SabreAMF_Const 
     * 
     * @package SabreAMF 
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */
    abstract class SabreAMF_Const {

        const AC_Flash    = 0;
        const AC_FlashCom = 1;

        const R_RESULT = 1;
        const R_STATUS = 2;
        const R_DEBUG  = 3;

        const AT_AMF0_NUMBER      = 0x00;
        const AT_AMF0_BOOL        = 0x01;
        const AT_AMF0_STRING      = 0x02;
        const AT_AMF0_OBJECT      = 0x03;
        const AT_AMF0_MOVIECLIP   = 0x04;
        const AT_AMF0_NULL        = 0x05;
        const AT_AMF0_UNDEFINED   = 0x06;
        const AT_AMF0_REFERENCE   = 0x07;
        const AT_AMF0_MIXEDARRAY  = 0x08;
        const AT_AMF0_OBJECTTERM  = 0x09;
        const AT_AMF0_ARRAY       = 0x0a;
        const AT_AMF0_DATE        = 0x0b;
        const AT_AMF0_LONGSTRING  = 0x0c;
        const AT_AMF0_UNSUPPORTED = 0x0e;
        const AT_AMF0_XML         = 0x0f;
        const AT_AMF0_TYPEDOBJECT = 0x10;

        const AT_AMF3_NULL        = 0x01;
        const AT_AMF3_BOOL_FALSE  = 0x02;
        const AT_AMF3_BOOL_TRUE   = 0x03;
        const AT_AMF3_INTEGER     = 0x04;
        const AT_AMF3_NUMBER      = 0x05;
        const AT_AMF3_STRING      = 0x06;
        const AT_AMF3_DATE        = 0x08;
        const AT_AMF3_ARRAY       = 0x09;
        const AT_AMF3_OBJECT      = 0x0a;

   }


?>
