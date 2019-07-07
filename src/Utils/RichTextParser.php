<?php

namespace Freyo\WeChatMiniProgram\Utils;

/**
 * Class RichTextParser
 * @package Freyo\WeChatMiniProgram\Utils
 */
class RichTextParser
{
    /**
     * @var \DOMDocument
     */
    protected $DOMDocument;

    /**
     * @var callable
     */
    protected $elementNodeHook;

    /**
     * @var callable
     */
    protected $textNodeHook;

    /**
     * RichTextParser constructor.
     *
     * @param string $HTML
     * @param null $encoding
     */
    public function __construct($HTML, $encoding = null)
    {
        $this->DOMDocument = new \DOMDocument();
        $this->DOMDocument->preserveWhiteSpace = false;
        $this->DOMDocument->strictErrorChecking = false;
        $this->DOMDocument->loadHTML($this->getXMLEncoding($encoding) . $HTML);
    }

    /**
     * @param string $encoding
     *
     * @return string
     */
    protected function getXMLEncoding($encoding = 'utf-8')
    {
        return '<?xml encoding="' . $encoding . '" ?>';
    }

    /**
     * @param string $HTML
     *
     * @return RichTextParser
     */
    public static function loadHTML($HTML)
    {
        return new self($HTML);
    }

    /**
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $body = $this->DOMDocument->getElementsByTagName('body')[0];

        return $body ? $this->getNodes($body->childNodes) : [];
    }

    /**
     * @param $childNodes
     *
     * @return array
     */
    protected function getNodes($childNodes)
    {
        $nodes = [];

        foreach ($childNodes as $childNode) {
            $nodes[] = $this->isTextNode($childNode)
                ? $this->textNode($childNode)
                : $this->elementNode($childNode);
        }

        return array_filter(isset($nodes[0][0]) ? $nodes[0] : $nodes);
    }

    /**
     * @param $childNode
     *
     * @return bool
     */
    protected function isTextNode($childNode)
    {
        return $childNode->nodeName === '#text';
    }

    /**
     * @param $childNode
     *
     * @return array
     */
    protected function textNode($childNode)
    {
        $node = [
            'text' => $childNode->textContent,
            'type' => 'text',
        ];

        if (is_callable($this->textNodeHook)) {
            $node = call_user_func_array(
                $this->textNodeHook, [$node, $childNode]
            );
        }

        return $node;
    }

    /**
     * @param $childNode
     *
     * @return array
     */
    protected function elementNode($childNode)
    {
        $node = [
            'name' => $childNode->nodeName,
            'attrs' => $childNode->attributes
                ? $this->getAttrs($childNode->attributes)
                : [],
            'children' => $childNode->childNodes
                ? $this->getNodes($childNode->childNodes)
                : [],
        ];

        if (is_callable($this->elementNodeHook)) {
            $node = call_user_func_array(
                $this->elementNodeHook, [$node, $childNode]
            );
        }

        return $node;
    }

    /**
     * @param $attributes
     *
     * @return array
     */
    protected function getAttrs($attributes)
    {
        $attrs = [];

        foreach ($attributes as $attribute) {
            $attrs[$attribute->name] = $attribute->value;
        }

        return $attrs;
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function setElementNodeHook(callable $callback)
    {
        $this->elementNodeHook = $callback;

        return $this;
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function setTextNodeHook(callable $callback)
    {
        $this->textNodeHook = $callback;

        return $this;
    }
}
