<?php

namespace johnitvn\jsonquery\schema;

use \johnitvn\jsonquery\JsonUtils;

/**
 * @author John Martin <john.itvn@gmail.com>
 * @since 1.0.0
 */
class Model {

    public $data;

    /**
     * @var array
     */
    protected $references = array();

    public function __construct($input) {
        $this->data = JsonUtils::dataCopy((object) $input, array($this, 'initCallback'));
        $this->resolveReferences();
    }

    public function find($schema, array $keys) {
        while ($keys && $schema) {
            $type = gettype($schema);
            $key = array_shift($keys);

            if ('array' === $type) {
                $key = (int) $key;
            }
            $schema = JsonUtils::get($schema, $key);
        }

        return $schema;
    }

    public function initCallback($data) {
        if ($ref = JsonUtils::get($data, '$ref')) {

            if (is_string($ref) && 0 === strpos($ref, '#')) {
                $this->references[$ref] = null;
            } else {
                throw new \RuntimeException('Invalid reference');
            }
        }

        return $data;
    }

    public function resolveCallback($data) {
        if ($ref = JsonUtils::get($data, '$ref')) {
            $data = JsonUtils::get($this->references, $ref);
        }

        return $data;
    }

    private function resolveReferences() {
        if ($this->references) {

            foreach (array_keys($this->references) as $ref) {
                $keys = JsonUtils::pathDecode($ref);

                if ($schema = $this->find($this->data, $keys)) {
                    $this->references[$ref] = $schema;
                } else {
                    throw new \RuntimeException('Unable to find ref ' . $ref);
                }
            }

            foreach ($this->references as $ref => $schema) {
                $this->references[$ref] = $this->resolve($schema);
            }

            $this->data = JsonUtils::dataCopy($this->data, array($this, 'resolveCallback'));
            $this->references = array();
        }
    }

    private function resolve($schema, $parents = array()) {
        $result = $schema;

        if ($ref = JsonUtils::get($schema, '$ref')) {
            $refSchema = JsonUtils::get($this->references, $ref);

            if (in_array($ref, $parents)) {
                throw new \RuntimeException('Circular reference to ref ' . $ref);
            } elseif (JsonUtils::get($refSchema, '$ref')) {
                $parents[] = $ref;
                $result = $this->resolve($refSchema, $parents);
            } else {
                $result = $refSchema;
            }
        }

        return $result;
    }

}
