<?php

/**
 * EmailGenerator.php
 *
 * This file is a part of tccl/email.
 */

namespace TCCL\Email;

/**
 * EmailGenerator
 *
 */
interface EmailGenerator {
    /**
     * Obtains the email message content body.
     *
     * @return string
     *  The email message content body.
     */
    public function getContent();

    /**
     * Obtains the headers for the email message.
     *
     * @return array
     *  An associative array representing the message headers.
     */
    public function getHeaders();
}
