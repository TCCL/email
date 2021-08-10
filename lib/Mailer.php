<?php

/**
 * Mailer.php
 *
 * This file is a part of tccl/email.
 */

namespace TCCL\Email;

class Mailer {
    /**
     * Encodes an email address and (optionally) recipient name for use in an
     * email header.
     *
     * @param string $emailAddress
     * @param string $name
     *
     * @return string
     */
    public static function encodeEmail($emailAddress,$name = null) {
        mb_internal_encoding("UTF-8");

        if (is_null($name)) {
            if (preg_match('/^(.*)<(.*)>$/',$emailAddress,$matches)) {
                list($name,$emailAddress) = array_slice($matches,1);
            }
        }

        if (!empty($name)) {
            $eUserName = mb_encode_mimeheader($name,'UTF-8','Q');
            $eUserName = str_replace('"','\"',$eUserName);
            $eUserName = "\"$eUserName\"";
            return "$eUserName <$emailAddress>";
        }

        return $emailAddress;
    }

    private $generator;

    /**
     * Creates a new Mailer instance.
     *
     * @param \TCCL\Email\EmailGenerator $generator
     *  The generator that will create the email message.
     */
    public function __construct(EmailGenerator $generator) {
        $this->generator = $generator;
    }

    /**
     * Calls mail() to send the email.
     *
     * @param string $subject
     * @param string $from
     * @param string $toAddress
     * @param string $toUser
     *
     * @return bool
     */
    public function sendMail($subject,$from,$toAddress,$toUser = null) {
        mb_internal_encoding('UTF-8');

        $content = $this->generator->getContent();
        $headers = $this->generator->getHeaders();

        $headers['From'] = mb_encode_mimeheader($from,'UTF-8','Q');
        $headers['MIME-Version'] = 1.0;

        $h = '';
        foreach ($headers as $key => $value) {
            $h .= "$key: $value\r\n";
        }

        $to = self::encodeEmail($toAddress,$toUser);
        $subjectEncoded = mb_encode_mimeheader($subject,'UTF-8','Q');

        return mail($to,$subjectEncoded,$content,$h);
    }

    /**
     * Calls mail() to send the email message.
     *
     * @param string $from
     *  The sender email address.
     * @param string $to
     *  The recipient email address.
     * @param string $subject
     *  The email address subject.
     *
     * @return bool
     *  The return value is that from the underlying call to mail().
     */
    public function send($from,$to,$subject) {
        mb_internal_encoding('UTF-8');

        $content = $this->generator->getContent();
        $headers = $this->generator->getHeaders();

        $headers['From'] = mb_encode_mimeheader($from,'UTF-8','Q');
        $headers['MIME-Version'] = 1.0;

        $h = '';
        foreach ($headers as $key => $value) {
            $h .= "$key: $value\r\n";
        }

        $toEncoded = self::encodeEmail($to);
        $subjectEncoded = mb_encode_mimeheader($subject,'UTF-8','Q');

        return mail($toEncoded,$subjectEncoded,$content,$h);
    }
}
