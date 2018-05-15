<?php
/**
 * PHP document tools
 *
 * This document is licensed as free software under the terms of the
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 *
 * @license     MIT License
 * @version     0.1
 * @copyright   Copyright 2011 by Yohei Yoshikawa (http://yoo-s.com)
 * 
 */

require_once 'DynamicDocument.php';

class DynamicPHPDocument extends DynamicDocument {

    var $columns;
    var $document_columns;

    function DynamicPHPDocument() {
        parent::DynamicDocument('php');
    }

}