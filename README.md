# wechat-html2json
Convert HTML to WeChat Mini Program Rich Text Nodes

# Install

```
composer require freyo/wechat-html2json
```

# Usage

```php
use Freyo\WeChatMiniProgram\Utils\RichTextParser;

$parsed = RichTextParser::loadHTML($HTML)
    ->setElementNodeHook(function (array $node, DOMElement $childNode) {
        // remove span node
        if ($childNode->nodeName === 'span') {
            return $node['children'];
        }
        // add width to img node
        if ($childNode->nodeName === 'img') {
            $node['attrs']['width'] = '100%';
        }
        return $node;
    })
    ->setTextNodeHook(function (array $node, DOMElement $childNode) {
        // remove text node
        if (strpos($childNode->textContent, 'KeyWord') !== false) {
            return null;
        }
        // replace keywords
        $node['text'] = str_replace(
            'keyword', 'KEYWORD', $childNode->textContent
        );
        return $node;
    })
    ->toJSON(); // or toArray()
    
var_dump($parsed);
```
