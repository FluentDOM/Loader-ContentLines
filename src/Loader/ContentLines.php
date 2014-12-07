<?php

namespace FluentDOM\ContentLines\Loader {

  use FluentDOM\Iterators\MapIterator;
  use FluentDOM\Loadable;
  use FluentDOM\Appendable;
  use FluentDOM\Document;
  use FluentDOM\Element;
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

    protected $_lines = [];

    protected $_addPropertiesAsAttributes = TRUE;

    /**
     * @see Loadable::load
     * @param mixed $source
     * @param string $contentType
     * @param array $options
     * @return Document|NULL
     */
    public function load($source, $contentType, array $options = []) {
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

    public function getIterator() {
      return new ContentLines\Iterator(
        new \IteratorIterator($this->_lines)
      );
    }

    public function appendTo(Element $parent) {
      $currentNode = $parent;
      foreach ($this as $token) {
        switch ($token->name) {
        case 'BEGIN' :
          $currentNode = $currentNode->appendElement(strtolower($token->value));
          $propertiesNode = $this->getWrapperNode($currentNode, $this->_nodeNames['properties']);
          $currentNode = $this->getWrapperNode($currentNode, $this->_nodeNames['components']);
          break;
        case 'END' :
          $currentNode = $currentNode->parentNode;
          break;
        case 'XML' :
          // @todo implement XML property
          break;
        default :
          if (!isset($propertiesNode)) {
            $currentNode = $currentNode->appendElement($this->_nodeNames['default-component']);
            $propertiesNode = $this->getWrapperNode($currentNode, $this->_nodeNames['properties']);
            $currentNode = $this->getWrapperNode($currentNode, $this->_nodeNames['components']);
          }
          if (array_key_exists($token->name, $this->_properties)) {
            if ($this->_addPropertiesAsAttributes) {
              $currentNode->setAttribute(strtolower($token->name), (string)$token->value);
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
        if (is_array($tokenType) || $tokenType === ':assoc') {
          $elements = explode(';', (string)$token->value);
          foreach ($elements as $index => $element) {
            if ($tokenType === ':assoc') {
              list($elementName, $elementValue) = explode('=', $element, 2);
            } else {
              $elementName = isset($tokenType[$index])
                ? $tokenType[$index] : end($tokenType[$token->name]);
              $elementValue = $element;
            }
            if (!empty($element)) {
              $itemNode->appendElement(strtolower($elementName), $elementValue);
            }
          }
        } else {
          $this->appendValueNode($itemNode, strtolower($tokenType), $token->value);
        }
      }
    }

    private function appendValueNode(Element $parent, $type, $values) {
      if (is_array($values) || $values instanceof \Traversable) {
        foreach ($values as $value) {
          $parent->appendElement($type, $value);
        }
      } elseif (!empty($values)) {
        $parent->appendElement($type, (string)$values);
      }
    }

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