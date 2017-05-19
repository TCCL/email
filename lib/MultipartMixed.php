<?php

/**
 * MultipartMixed.php
 *
 * This file is a part of tccl/email.
 */

namespace TCCL\Email;

class MultipartMixed extends Multipart {
    public function __construct() {
        parent::__construct();

    }

    /**
     * Implements Multipart::getSubtype().
     */
    protected function getSubtype() {
        return 'mixed';
    }
}
