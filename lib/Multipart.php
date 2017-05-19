<?php

/**
 * Multipart.php
 *
 * This file is a part of tccl/email.
 */

namespace TCCL\Email;

abstract class Multipart implements EmailGenerator {
    static private $boundaryCount = 0;

    /**
     * The boundary string for the multipart message.
     *
     * @var string
     */
    private $boundary;

    /**
     * The child parts included in the message.
     *
     * @var array
     */
    private $children = [];

    /**
     * Creates a new Multipart instance.
     */
    public function __construct() {
        $n = self::$boundaryCount++;
        $xs = openssl_random_pseudo_bytes(40);

        $this->boundary = "----=_Item_{$n}_" . bin2hex($xs);
    }

    /**
     * Implements EmailGenerator::getContent().
     */
    public function getContent() {
        $body = '';
        foreach ($this->children as $part) {
            if (!empty($body)) {
                $body .= "\r\n";
            }
            $body .= "--$this->boundary\r\n";

            $headers = $part->getHeaders();
            $hstring = '';
            foreach ($headers as $key => $value) {
                $hstring .= "$key: $value\r\n";
            }

            $body .= "$hstring\r\n" . $part->getContent();
        }
        if (!empty($body)) {
            $body .= "\r\n";
        }
        $body .= "--$this->boundary\r\n";
        return $body;
    }

    /**
     * Implements EmailGenerator::getHeaders().
     */
    public function getHeaders() {
        $subtype = $this->getSubtype();

        return array(
            'Content-Type' => "multipart/$subtype; boundary=\"$this->boundary\"",
        );
    }

    /**
     * Adds a content part to the multipart content.
     *
     * @param EmailGenerator $generator
     *  The generator which provides the part content.
     */
    public function addPart(EmailGenerator $generator) {
        $this->children[] = $generator;
    }

    /**
     * Gets the subtype associated with the multipart instance.
     */
    abstract protected function getSubtype();
}
