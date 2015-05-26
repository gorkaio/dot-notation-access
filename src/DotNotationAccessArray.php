<?php

namespace Gorka\DotNotationAccess;

/**
 * Class DotNotationAccessArray
 * @todo: needs optimization
 */
class DotNotationAccessArray implements DotNotationAccessor
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param array|string $data Configuration values, either as array or JSON string
     * @throws \InvalidArgumentException
     */
    public function __construct($data = null)
    {
        if (null === $data) {
            $data = [];
        }

        if (!is_array($data)) {
            $data = $this->unserialize($data);
        }

        if (!$this->isValidData($data)) {
            throw new \InvalidArgumentException();
        }

        $this->data = $data;
    }

    /**
     * @param string $path
     * @param null $default
     * @return array|null
     */
    public function get($path, $default = null)
    {
        if (!$this->isValidPath($path)) {
            throw new \InvalidArgumentException();
        }

        $parts = explode('.', $path);
        if (is_array($this->data) && is_array($parts)) {
            $nodes = &$this->data;
            foreach ($parts as $nodeName) {
                if (!isset($nodes[$nodeName])) {
                    return $default;
                } else {
                    $nodes = &$nodes[$nodeName];
                }
            }
            return $nodes;
        } else {
            return $default;
        }
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->data;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function has($path)
    {
        if (!$this->isValidPath($path)) {
            throw new \InvalidArgumentException();
        }

        $parts = explode('.', $path);
        if (is_array($this->data) && is_array($parts)) {
            $nodes = &$this->data;
            foreach ($parts as $nodeName) {
                if (!isset($nodes[$nodeName])) {
                    return false;
                } else {
                    $nodes = &$nodes[$nodeName];
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Whether the given path is valid
     *
     * @param string $path
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function isValidPath($path)
    {
        return preg_match('/^([a-z]+[a-z0-9_-]*\.)*([a-z]+[a-z0-9_-]*)$/i', $path);
    }

    /**
     * Validates values
     *
     * @param $value
     * @return bool
     */
    protected function isValidValue($value)
    {
        $valid = true;
        if (is_array($value)) {
            $numericKeys = 0;
            foreach ($value as $k => $v) {
                $numericKeys += is_integer($k);
                if (!$this->isValidValue($v)) {
                    $valid = false;
                    break;
                }
            }
            if (!($numericKeys == 0 || $numericKeys == count($value))) {
                $valid = false;
            }
        } else {
            if (null !== $value && !is_scalar($value)) {
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * Validate single key
     *
     * @param $key
     * @return int
     */
    protected function isValidKey($key)
    {
        return preg_match('/^([a-z]+[a-z0-9_-]*)$/i', $key);
    }

    /**
     * Validate config
     *
     * @param $config
     * @return string
     */
    protected function isValidData($config)
    {
        $valid = true;
        foreach ($config as $k => $v) {
            if (!($this->isValidKey($k) && $this->isValidValue($v))) {
                $valid = false;
                break;
            }
        }
        return $valid;
    }

    /**
     * @return string JSON serialized configuration
     */
    public function serialize()
    {
        if (null == $this->data) {
            $serialized = json_encode(array());
        } else {
            $serialized = json_encode($this->data);
        }
        return $serialized;
    }

    /**
     * @param string $serialized JSON serialized configuration
     * @return array
     */
    private function unserialize($serialized)
    {
        if ($serialized === null) {
            $serialized = '';
        }

        $config = json_decode($serialized, true);
        if (empty($serialized) || null == $config) {
            throw new \InvalidArgumentException("Invalid config JSON");
        }

        if (!$this->isValidData($config)) {
            throw new \InvalidArgumentException("Invalid config");
        }

        return $config;
    }

    /**
     * @param string $path
     * @param mixed $value
     * @return DotNotationAccessArray
     */
    public function set($path, $value)
    {
        if (!$this->isValidPath($path) || !$this->isValidValue($value)) {
            throw new \InvalidArgumentException();
        }

        $newConfig = clone $this;
        $parts = explode('.', $path);
        if (is_array($parts)) {
            $nodes = &$newConfig->data;
            foreach ($parts as $nodeName) {

                if (!isset($nodes[$nodeName]) || !is_array($nodes[$nodeName])) {
                    $nodes[$nodeName] = array();
                }

                $nodes = &$nodes[$nodeName];
            }
        }
        $nodes = $value;
        return $newConfig;
    }

    /**
     * @param string $path
     * @return DotNotationAccessArray
     */
    public function remove($path)
    {
        if (!$this->isValidPath($path)) {
            throw new \InvalidArgumentException();
        }

        $newData = clone $this;
        $parts = explode('.', $path);
        if (is_array($parts)) {
            $parent = null;
            $nodes = &$newData->data;
            $nodeName = $parts[0];
            foreach ($parts as $nodeName) {
                if (!isset($nodes[$nodeName])) {
                    return false;
                }
                $parent = &$nodes;
                $nodes = &$nodes[$nodeName];
            }
            unset($parent[$nodeName]);
        }
        return $newData;
    }

    /**
     * @param DotNotationAccessArray|array $config
     * @return DotNotationAccessArray
     */
    public function merge($config)
    {
        if ($config instanceof DotNotationAccessArray) {
            $configValues = $config->getAll();
        } else {
            if (is_array($config)) {
                $configValues = $config;
            } else {
                throw new \InvalidArgumentException("DotNotationAccessArray to be merged should be a DotNotationAccessArray object or an array");
            }
        }
        $mergedConfig = array_replace_recursive($this->data, $configValues);
        return new self($mergedConfig);
    }

    /**
     * @param DotNotationAccessArray $other
     * @return bool
     */
    public function equals(DotNotationAccessArray $other)
    {
        return $this->serialize() == $other->serialize();
    }
}
