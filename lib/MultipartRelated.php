<?php

/**
 * MultipartRelated.php
 *
 * This file is a part of tccl/email.
 */

namespace TCCL\Email;

class MultipartRelated extends Multipart {
    public function __construct() {
        parent::__construct();

    }

    /**
     * Implements Multipart::getSubtype().
     */
    protected function getSubtype() {
        return 'related';
    }
}
