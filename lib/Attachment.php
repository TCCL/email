<?php

/**
 * Attachment.php
 *
 * This file is a part of tccl/email.
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
            // Since mime_content_type() requires a file on disk, we need to
            // copy the input stream to a temporary file if the stream wrapper
            // is not 'file'.
            $meta = stream_get_meta_data($this->inputStream);
            if ($meta['wrapper_type'] != 'file') {
                $tmp = tmpfile();
                if (stream_copy_to_stream($this->inputStream,$tmp) === false) {
                    throw new Exception('Failed to copy attachment data to temporary file');
                }

                $this->inputStream = $tmp;
                $meta = stream_get_meta_data($tmp);
                fseek($tmp,0);
            }

            $filePath = $meta['uri'];
            $this->contentType = mime_content_type($filePath);
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
        return [
            'Content-Type' => "$this->contentType; name=$this->fileName",
            'Content-Transfer-Encoding' => 'base64',
            'Content-Disposition' => "attachment; filename=$this->fileName",
        ];
    }

    /**
     * Gets the attachement file name.
     *
     * @return string
     */
    public function getFileName() {
        return $this->fileName;
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

            $map = [];
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
