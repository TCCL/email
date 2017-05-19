<?php

/**
 * Attachment.php
 */

namespace TCCL\Email;

use Exception;

class Attachment implements EmailGenerator {
    const BASE64_LINE_LEN = 76;
    const TYPES_FILE = '/etc/mime.types';

    private $content;
    private $fileName;
    private $inputStream;
    private $contentType;

    /**
     * Creates a new attachment object.
     *
     * @param mixed $url
     *  A file or remote resource that PHP can fopen() OR an existing fopen'd
     *  resource. To pass in arbitrary file data, use php://memory or
     *  php://temp.
     * @param string $desiredName
     *  The desired file name for the attachment.
     */
    public function __construct($url,$desiredName = null) {
        // Open input stream to attachment resource.
        if (!is_resource($url)) {
            $this->inputStream = fopen($url,'r');
            if (!is_resource($this->inputStream)) {
                throw new Exception(__METHOD__.': cannot open attachment URL');
            }
        }
        else {
            $this->inputStream = $url;
            if (!isset($desiredName)) {
                throw new Exception(__METHOD__.': attachment file name must be specified');
            }
        }

        // Set file name for attachment.
        if (!isset($desiredName)) {
            $pinfo = pathinfo($url);
            $this->fileName = $pinfo['basename'];
        }
        else {
            $this->fileName = $desiredName;
            $pinfo = pathinfo($desiredName);
        }

        // Figure out the content type from the extension. If the types file
        // doesn't exist, use PHP's mime_content_type() function.
        $this->contentType = self::ex2mime($pinfo['extension']);
        if ($this->contentType === false) {
            if (is_resource($url)) {
                $this->contentType = 'application/octet-stream';
            }
            else {
                $this->contentType = mime_content_type($url);
            }
        }
    }

    /**
     * Implements EmailGenerator::getContent().
     */
    public function getContent() {
        if (!isset($this->content)) {
            $encoded = base64_encode(stream_get_contents($this->inputStream));
            $output = '';

            $i = 0;
            while ($i < strlen($encoded)) {
                $output .= substr($encoded,$i,self::BASE64_LINE_LEN) . "\r\n";
                $i += self::BASE64_LINE_LEN;
            }
            $this->content = $output;
            fclose($this->inputStream);
        }

        return $this->content;
    }

    /**
     * Implements EmailGenerator::getHeaders().
     */
    public function getHeaders() {
        return array(
            'Content-Type' => "$this->contentType; name=$this->fileName",
            'Content-Transfer-Encoding' => 'base64',
            'Content-Disposition' => "attachment; filename=$this->fileName",
        );
    }

    /**
     * Convert the specified extension to its corresponding MIME-type.
     *
     * @param string $ex
     *  The file extension (without a leading punctuator).
     */
    static private function ex2mime($ex) {
        static $map;

        if (is_null($map)) {
            if (!is_file(self::TYPES_FILE)) {
                return false;
            }

            $map = array();
            $info = file_get_contents(self::TYPES_FILE);
            foreach (explode("\n",$info) as $line) {
                $line = trim($line);
                if (strlen($line) > 0 && $line[0] != '#') {
                    $mediaType = strtok($line," \t");

                    while (($tok = strtok(" \t")) !== false) {
                        $map[$tok] = strtolower($mediaType);
                    }
                }
            }
        }
        $ex = strtolower($ex);

        if (isset($map[$ex])) {
            return $map[$ex];
        }

        return 'application/octet-stream';
    }
}
