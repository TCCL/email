<?php

/**
 * PlainTextGenerator.php
 *
 * This file is a part of tccl/email.
 */

namespace TCCL\Email;

class PlainTextGenerator implements EmailGenerator {
    private $content;

    /**
     * Implements EmailGenerator::getContent().
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * Implements EmailGenerator::getHeaders().
     */
    public function getHeaders() {
        return [
            'Content-Type' => 'text/plain; charset=utf-8',
        ];
    }

    /**
     * Set content.
     *
     * @param string $content
     */
    public function setContent($content) {
        $this->content = $content;
    }
}
