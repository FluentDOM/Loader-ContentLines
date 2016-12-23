<?php

namespace FluentDOM\ContentLines\Loader {

  use FluentDOM\Exceptions\InvalidFragmentLoader;
  use FluentDOM\Iterators\MapIterator;
  use FluentDOM\Loadable;
  use FluentDOM\Appendable;
  use FluentDOM\Document;
  use FluentDOM\Element;
  use FluentDOM\Loader\Options;
  use FluentDOM\Loader\Supports;

  abstract class ContentLines implements Loadable, \IteratorAggregate, Appendable {

    use Supports;

    protected $_namespace = 'urn:content-lines';

    protected $_nodeNames = [
      'root' => 'data',
      'default-component' => 'component',
      'parameters' => 'parameters',
      'properties' => FALSE,
      'components' => FALSE,
      'default-type' => 'unknown'
    ];

    protected $_properties = [];

    protected $_parameters = [];

    protected $_components = [];

    /**
     * @var \Traversable
     */
    protected $_lines;

    protected $_addPropertiesAsAttributes = TRUE;

    // 20060102T120000
    const PATTERN_DATETIME = '(^
      (?P<year>\d{4})
      (?P<month>\d{2})
      (?P<day>\d{2})
      T
      (?P<hour>\d{2})
      (?P<minute>\d{2})
      (?P<second>\d{2})
      (?P<offset>(?:Z|[+-]\d{2}:?\d{2}))?
    $)x';

    // 20081006
    const PATTERN_DATE = '(^
      (?P<year>\d{4})
      (?P<month>\d{2})
      (?P<day>\d{2})
    $)x';

    // 20081006
    const PATTERN_OFFSET = '(^
      (?P<prefix>[+-])
      (?P<hours>\d{2})
      (?P<minutes>\d{2})
    $)x';

    /**
     * @see Loadable::load
     * @param mixed $source
     * @param string $contentType
     * @param array|\Traversable|Options $options
     * @return Document|NULL
     */
    public function load($source, $contentType, $options = []) {
      if ($this->supports($contentType) && ($this->_lines = $this->getLines($source))) {
        $dom = new Document('1.0', 'UTF-8');
        $dom->registerNamespace('', $this->_namespace);
        $dom
          ->appendElement($this->_nodeNames['root'])
          ->append($this);
        return $dom;
      }
      return NULL;
    }

    public function loadFragment($source, $contentType, $options = []) {
      throw new InvalidFragmentLoader(static::class);
    }

    public function getIterator() {
      return new ContentLines\Iterator(
        new \IteratorIterator($this->_lines)
      );
    }

    public function appendTo(Element $parent) {
      $groupNode = $componentsNode = $parent;
      foreach ($this as $token) {
        switch ($token->name) {
        case 'BEGIN' :
          list($groupNode, $propertiesNode, $componentsNode) = $this->startGroup(
            $componentsNode, strtolower($token->value)
          );
          break;
        case 'END' :
          $componentsNode = $this->endGroup($componentsNode, strtolower($token->value));
          break;
        case 'XML' :
        default :
          if (!isset($propertiesNode)) {
            list($groupNode, $propertiesNode, $componentsNode) = $this->startGroup(
              $componentsNode, $this->_nodeNames['default-component']
            );
          }
          if ($token->name === 'XML') {
            $propertiesNode->appendXml((string)$token->value);
          } elseif (array_key_exists($token->name, $this->_properties)) {
            if ($this->_addPropertiesAsAttributes) {
              $groupNode->setAttribute(strtolower($token->name), (string)$token->value);
              continue;
            }
            $this->appendValueTo($propertiesNode, $token);
          } else {
            $this->appendValueTo($propertiesNode, $token);
          }
        }
      }
      if ($this->_nodeNames['components']) {
        foreach ($parent('//*[local-name() = "'.$this->_nodeNames['components'].'" and count(*) = 0]') as $node) {
          $node->remove();
        }
      }
    }

    private function startGroup(Element $parent, $groupName) {
      $group = $parent->appendElement($groupName);
      return [
        $group,
        $this->getWrapperNode($group, $this->_nodeNames['properties']),
        $this->getWrapperNode($group, $this->_nodeNames['components'])
      ];
    }

    private function endGroup(Element $group, $groupName) {
      while ($group->parentNode instanceof \DOMNode) {
        if ($groupName == $group->localName) {
          return $group->parentNode;
        } else {
          $group = $group->parentNode;
        }
      }
      return $group;
    }

    /**
     * Append a wrapper node if a name was provided. Return the $parent otherwise.
     */
    private function getWrapperNode(Element $parent, $name) {
      if ($name) {
        return $parent->appendElement($name);
      } else {
        return $parent;
      }
    }

    private function appendValueTo(Element $parent, $token) {
      $itemNode = $parent->appendElement(strtolower($token->name));
      if (!empty($token->parameters)) {
        $parametersNode = $this->getWrapperNode($itemNode, $this->_nodeNames['parameters']);
        foreach ($token->parameters as $name => $parameter) {
          $parameterNode = $parametersNode->appendElement(strtolower($name));
          $this->appendValueNode(
            $parameterNode,
            strtolower(
              isset($this->_parameters[$name])
                ? $this->_parameters[$name] : $this->_nodeNames['default-type']
            ),
            $parameter
          );
        }
      }
      if (!empty($token->value)) {
        $tokenType = $token->type
          ?: (isset($this->_components[$token->name])
            ? $this->_components[$token->name]
            : 'unknown');
        if (is_array($tokenType)) {
          $tokenValues = $this->getValuesAsList($token->value, $tokenType);
          foreach ($tokenValues as $tokenName => $tokenValue) {
            $this->appendValueNode($itemNode, $tokenName, $tokenValue);
          }
        } else {
          $this->appendValueNode($itemNode, strtolower($tokenType), $token->value);
        }
      }
    }

    protected function getValuesAsList($value, $keys = FALSE) {
      $result = [];
      $elements = explode(';', (string)$value);
      foreach ($elements as $index => $element) {
        if (is_array($keys)) {
          $elementName = isset($keys[$index]) ? $keys[$index] : end($keys);
          $elementValue = $element;
        } else {
          list($elementName, $elementValue) = explode('=', $element, 2);
        }
        if (!empty($elementValue)) {
          $result[strtolower($elementName)] = $elementValue;
        }
      }
      return $result;
    }

    protected function appendValueNode(Element $parent, $type, $values) {
      switch ($type) {
      case ':ignore' :
        if ($parent->parentNode instanceof \DOMNode) {
          $parent->parentNode->removeChild($parent);
        }
        return;
      case ':value' :
        $parent->append((string)$values);
        return;
      case ':assoc' :
        $tokenValues = $this->getValuesAsList($values);
        foreach ($tokenValues as $tokenName => $tokenValue) {
          $this->appendValueNode($parent, $tokenName, $tokenValue);
        }
        return;
      case 'utc-offset' :
        if (preg_match(self::PATTERN_OFFSET, (string)$values, $match)) {
          $values = sprintf(
            '%s%s:%s',
            $match['prefix'],
            $match['hours'],
            $match['minutes']
          );
        }
        break;
      case 'date-time-or-date' :
        if (preg_match(self::PATTERN_DATETIME, (string)$values, $match)) {
          $type = 'date-time';
          $values = sprintf(
            '%s-%s-%sT%s:%s:%s%s',
            $match['year'],
            $match['month'],
            $match['day'],
            $match['hour'],
            $match['minute'],
            $match['second'],
            isset($match['offset']) ? $match['offset'] : ''
          );
        } elseif (preg_match(self::PATTERN_DATE, (string)$values, $match)) {
          $type = 'date';
          $values = sprintf(
            '%s-%s-%s',
            $match['year'],
            $match['month'],
            $match['day']
          );
        } else {
          return;
        }
        break;
      }
      if (is_array($values) || $values instanceof \Traversable) {
        foreach ($values as $value) {
          $parent->appendElement($type, $value);
        }
      } elseif (!empty($values)) {
        $parent->appendElement($type, (string)$values);
      }
    }

    /**
     * @param $source
     * @return \Traversable
     */
    protected function getLines($source) {
      $result = null;
      if ($this->isFile($source)) {
        $file = new \SplFileObject($source);
        $file->setFlags(\SplFileObject::DROP_NEW_LINE);
        return $file;
      } elseif (is_string($source)) {
        $result = new \ArrayIterator(explode("\n", $source));
      } elseif (is_array($source)) {
        $result = new \ArrayIterator($source);
      } elseif ($source instanceof \Traversable) {
        $result = $source;
      }
      if (empty($result)) {
        return null;
      } else {
        return new MapIterator($result, function($line) { return rtrim($line, "\r\n"); } );
      }
    }

    private function isFile($source) {
      return (is_string($source) && (FALSE === strpos($source, "\n")));
    }
  }
}