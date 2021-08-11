<?php

/**
 * InlineImage.php
 *
 * This file is a part of tccl/email.
 */

namespace TCCL\Email;

class InlineImage extends Attachment {
    /**
     * Map of image file names to content IDs.
     *
     * @var array
     */
    static private $imageMap = [];

    /**
     * Update all references to the inline images in the specified
     * HTMLGenerator. All inline images will be candidates.
     *
     * @param \TCCL\Email\HTMLGenerator $generator
     *  The generator to modify.
     */
    static function linkTo(HTMLGenerator $generator) {
        $modif['callback'] = function(&$tag,&$attr) {
            if (preg_match('/src="([^ "]+)"/',$attr,$matches,PREG_OFFSET_CAPTURE)) {
                if (isset(self::$imageMap[$matches[1][0]])) {
                    // Change file name in "src" attribute.
                    $cid = self::$imageMap[$matches[1][0]];
                    $attr = substr($attr,0,$matches[1][1]) . "cid:$cid"
                        . substr($attr,$matches[1][1] + strlen($matches[1][0]));
                }
            }
        };

        $generator->addModifier('img',$modif);
    }

    /**
     * Resets the image map.
     */
    public static function reset() {
        self::$imageMap = [];
    }

    /**
     * The unique content ID created for the image.
     *
     * @var string
     */
    private $contentId;

    /**
     * Wraps the base class constructor, Attachement::__construct().
     */
    public function __construct($url,$desiredName = null) {
        parent::__construct($url,$desiredName);

        // Generate a content ID for the inline image.
        $guid = self::generateGuid();
        $fileName = $this->getFileName();
        $this->contentId = "@{$guid}_$fileName";

        self::$imageMap[$fileName] = $this->contentId;
    }

    /**
     * Overrides Attachment::getHeaders().
     */
    public function getHeaders() {
        $headers = parent::getHeaders();
        $headers['Content-ID'] = "<{$this->contentId}>";
        $headers['Content-Disposition'] = 'inline';

        return $headers;
    }

    static private function generateGuid() {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x40);
        $data[8] = chr(ord($data[8]) & 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s',str_split(bin2hex($data),4));
    }
}
