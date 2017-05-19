<?php

/**
 * HTMLGenerator.php
 *
 * This file is a part of tccl/email.
 */

namespace TCCL\Email;

class HTMLGenerator implements EmailGenerator {
    private $modifs;
    private $content;

    static private $headers = array(
        'Content-Type' => 'text/html; charset=utf-8',
    );

    /**
     * Implements EmailGenerator::getContent().
     */
    public function getContent() {
        if (isset($this->modifs)) {
            return self::modifyMarkup($this->content,$this->modifs);
        }

        return $this->content;
    }

    /**
     * Implements EmailGenerator::getHeaders().
     */
    public function getHeaders() {
        return self::$headers;
    }

    /**
     * Sets the content for the HTML generator.
     *
     * @param string $content
     *  The HTML content for the generator.
     */
    public function setContent($content) {
        $this->content = $content;
    }

    /**
     * Appends content to the HTML generator.
     *
     * @param string $content
     *  The content to append.
     */
    public function appendContent($content) {
        $this->content .= $content;
    }

    /**
     * Sets the set of modifiers that are to be employed by the generator. This
     * will overwrite any existing modifiers.
     *
     * @param array $modifiers
     *  An associative array representing the modifiers.
     */
    public function setModifiers(array $modifiers) {
        $this->modifs = $modifiers;
    }

    /**
     * Adds a new entry to the generator's set of modifiers.
     *
     * @param string $forTag
     *  The XML tag for which this modifier applies. The special tag *
     *  represents all tags.
     * @param array $entry
     *  The modifier information for the entry.
     */
    public function addModifier($forTag,array $entry) {
        $this->modifs[$forTag] = $entry;
    }

    /**
     * Modifies markup.
     *
     * @param string $html
     *  The HTML markup to process.
     * @param array $modifiers
     *  An associative array of modifiers. Each bucket is keyed by the tag name
     *  found in the markup and the value is an associative array with the
     *  different modifier elements:
     *   - 'tag': a replacement tag name
     *   - 'styles': inline styles to apply to the element
     *   - 'attrs': extra attributes to apply to the element
     *   - 'exclude': excludes the modifier from applying to the specified tag
             names; the tags must be the keys mapping to an arbitrary value
     *   - 'callback': a PHP callable to generate a modification; this is called
     *       after the other elements have been processed
     *
     * @return string
     *  The markup with styles applied.
     */
    static public function modifyMarkup($html,array $modifiers) {
        $callback = function(array $matches) use($modifiers) {
            list($_,$tag,$attrs) = $matches;

            $entry = isset($modifiers[$tag]) ? $modifiers[$tag]
                : (isset($modifiers['*']) ? $modifiers['*'] : null);
            if (isset($entry) && !isset($entry['exclude'][$tag])) {
                if (isset($entry['tag'])) {
                    $tag = $entry['tag'];
                }
                if (isset($entry['styles'])) {
                    $attrs = " style=\"{$entry['styles']}\"$attrs";
                }
                if (isset($entry['attrs'])) {
                    $attrs = " {$entry['attrs']}$attrs";
                }
                if (isset($entry['callback'])) {
                    $entry['callback']($tag,$attrs);
                }
            }

            return "<$tag$attrs>";
        };

        $regex = '/<([^!\/ >]+)([^>]*)>/';
        return preg_replace_callback($regex,$callback,$html);
    }
}
