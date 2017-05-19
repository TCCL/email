<?php

/**
 * Mailer.php
 *
 * This file is a part of tccl/email.
 */

namespace TCCL\Email;

class Mailer {
    private $generator;

    /**
     * Creates a new Mailer instance.
     *
     * @param EmailGenerator $generator
     *  The generator that will create the email message.
     */
    public function __construct(EmailGenerator $generator) {
        $this->generator = $generator;
    }

    /**
     * Calls mail() to send the email message.
     *
     * @param string $emailFrom
     *  The sender email address.
     * @param string $emailTo
     *  The recipient email address.
     * @param string $subject
     *  The email address subject.
     *
     * @return bool
     *  The return value is that from the underlying call to mail().
     */
    public function send($emailFrom,$emailTo,$subject) {
        $content = $this->generator->getContent();
        $headers = $this->generator->getHeaders();

        $headers['From'] = $emailFrom;
        $headers['MIME-Version'] = 1.0;
        $h = '';
        foreach ($headers as $key => $value) {
            $h .= "$key: $value\r\n";
        }

        return mail($emailTo,$subject,$content,$h);
    }
}
