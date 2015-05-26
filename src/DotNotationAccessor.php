<?php

namespace Gorka\DotNotationAccess;

/**
 * Interface DotNotationReadAccessorInterface
 *
 * Dot notation (adsense.ads.count) read-only accessor
 *
 * Keys must be lowercase letters, numbers, hyphen and underscore beginning with a letter: [a-z]+[a-z0-9_-]*
 * Values must be scalar (integer, float, string, boolean), scalar[] or sub-config
 */
interface DotNotationAccessor
{
    /**
     * Set value at given path
     *
     * If path or leaf don't exist it will create path and set leaf value
     * If path exists it will overwrite its value (either scalar, scalar[] or array)
     *
     * # EXAMPLE1 - Add new leaf on existing path
     *      pre: data = array('foo'=>3)
     *      set('data.bar', 7)
     *      pos: data = array('foo'=>3, 'bar'=>7)
     *
     * # EXAMPLE2 - Overwrite leaf
     *      pre: data = array('foo'=>3)
     *      set('data.foo', 7)
     *      pos: data = array('foo'=>7)
     *
     * # EXAMPLE3 - Overwrite branch
     *      pre: data.foo = 3
     *      set('data.foo.bar', 5)
     *      pos: data.foo = array('bar'=>5)
     *
     * @param string $path
     * @param mixed $value
     * @return DotNotationAccessor
     */
    public function set($path, $value);

    /**
     * Removes path or leaf at given path
     *
     * @param string $path
     * @return DotNotationAccessor
     */
    public function remove($path);

    /**
     * Get value at given path
     *
     * If path or leaf don't exist it will return null or $default value if given
     * If path is a leaf it will return its value (scalar|scalar[])
     * If path is not a leaf it will return the subtree (array)
     *
     * @param string $path
     * @param mixed $default
     * @return mixed
     */
    public function get($path, $default = null);

    /**
     * Get root value
     *
     * @return mixed
     */
    public function getAll();

    /**
     * Whether given path exists
     *
     * @param string $path
     * @return bool
     */
    public function has($path);
}
