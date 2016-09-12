<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This file is part of the Core Framework package.
 *
 * (c) Shalom Sam <shalom.s@coreframework.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('core_serialize')) {

    /**
     * Method to serialize array (comma separated values as single string)
     *
     * @param array $arr
     * @param string $delimiter
     * @return string
     */
    function core_serialize(array $arr, $delimiter = ", ")
    {
        $serialized = "";
        if (!key($arr)) {
            foreach ($arr as $item) {
                $serialized .= $item . $delimiter;
            }
            $serialized = rtrim($serialized, $delimiter);
            return $serialized;
        } elseif (sizeof(key($arr)) > 0) {
            foreach ($arr as $key => $val) {
                $serialized .= $val . ", ";
            }
            $serialized = rtrim($serialized, $delimiter);
            return $serialized;
        }
    }
}


if (!function_exists('core_unserialize')) {

    /**
     * @param $serialized
     * @param string $delimiter
     * @return array
     */
    function core_unserialize($serialized, $delimiter = ", ")
    {
        return explode($delimiter, $serialized);
    }
}

if (!function_exists('copyr')) {

    /**
     * Method to copy files and directories recursively
     *
     * @param $source
     * @param $dest
     * @param bool $override
     * @throws \Exception
     */
    function copyr($source, $dest, $override = false)
    {
        $dir = opendir($source);
        if (!is_dir($dest)) {
            mkdir($dest);
        } else {
            chmod($dest, 0755);
        }
        if (is_resource($dir)) {
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($source . DS . $file)) {
                        copyr($source . DS . $file, $dest . DS . $file);
                    } elseif (is_readable($dest . DS . $file) && $override === true) {
                        copy($source . DS . $file, $dest . DS . $file);
                    } elseif (!is_readable($dest . DS . $file)) {
                        copy($source . DS . $file, $dest . DS . $file);
                    }
                }
            }
        } else {
            throw new \Exception("readdir() expects parameter 1 to be resource", 10);
        }
        closedir($dir);
    }
}


if (!function_exists('chmodDirFiles')) {

    /**
     * Method to change the permission for files recursively
     *
     * @param $dir
     * @param null $mod
     * @param bool $recursive
     */
    function chmodDirFiles($dir, $mod = null, $recursive = true)
    {
        chmod($dir, 0755);
        if ($recursive && $objs = glob($dir . DS . "*")) {
            foreach ($objs as $file) {
                if (is_dir($file)) {
                    chmodDirFiles($file, $mod, $recursive);
                } else {
                    change_perms($file, $mod);
                }
            }
        }
    }
}


if (!function_exists('change_perms')) {

    /**
     * Method to change the permission of a single file
     *
     * @param $obj
     * @param null $mod
     */
    function change_perms($obj, $mod = null)
    {
        chmod($obj, empty($mod) ? 0755 : $mod);
    }
}


if (!function_exists('searchArrayByKey')) {

    /**
     * Searches given array for given key and return the value of that key. Returns false if nothing was found
     *
     * @param array $array
     * @param string $search
     * @param mixed $default
     * @return bool|mixed
     */
    function searchArrayByKey(array $array, $search, $default = false)
    {
        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $key => $value) {
            if ($search === $key) {
                return $value;
            }
        }
        return $default;
    }
}

if (!function_exists('strContains')) {

    /**
     * Returns true if string contains searched string, else false.
     *
     * @param $search
     * @param $string
     * @return bool
     */
    function strContains($search, $string)
    {
        $string = strtolower($string);
        $search = strtolower($search);
        return strpos($string, $search) !== false ? true : false;
    }
}

if (!function_exists('strStartsWith')) {

    /**
     * search backwards starting from haystack length characters from the end
     *
     * @param $string
     * @param $search
     * @return bool
     */
    function strStartsWith($string, $search)
    {
        if (empty($string) || empty($search)) {
            throw new InvalidArgumentException("String and Search values cannot be empty.");
        }

        return $search === "" || strrpos($string, $search, -strlen($string)) !== false;
    }

}

if (!function_exists('strEndsWith')) {

    /**
     * search forward starting from end minus needle length characters
     *
     * @param $string
     * @param $search
     * @return bool
     */
    function strEndsWith($string, $search)
    {
        if (empty($string) || empty($search)) {
            throw new InvalidArgumentException("String and Search values cannot be empty.");
        }

        return $search === "" || (($temp = strlen($string) - strlen($search)) >= 0 && strpos(
                $string,
                $search,
                $temp
            ) !== false);
    }

}

if (!function_exists('dotGet')) {

    /**
     * Dot notation access to given array.
     *
     * @param $key
     * @param array $data
     * @param null $default
     * @return array|null
     */
    function dotGet($key, array $data, $default = null)
    {
        if (!is_string($key) || empty($key) || !count($data)) {
            return $default;
        }

        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);

            foreach ($keys as $innerKey) {
                if (!array_key_exists($innerKey, $data)) {
                    return $default;
                }

                $data = $data[$innerKey];
            }

            return $data;
        }

        return array_key_exists($key, $data) ? $data[$key] : $default;
    }
}

if (!function_exists('dotSet')) {

    /**
     * Dot notation set array value.
     *
     * @param $path
     * @param array $array
     * @param $value
     * @return array|bool
     */
    function dotSet($path, array &$array, $value)
    {
        if (!is_string($path) || empty($path)) {
            return false;
        }

        if (strpos($path, '.') !== false) {
            $loc = &$array;
            foreach (explode('.', $path) as $step) {
                $loc = &$loc[$step];
            }
            $loc = $value;
            return $array;
        }

        return false;
    }
}

if (!function_exists('getOne')) {

    function getOne($original, $default)
    {
        try {
            if (is_callable($original)) {
                $originalValue = $original();
            } else {
                $originalValue = $original;
            }

            if (!isset($originalValue) || empty($originalValue)) {
                $returnVal = $default;
            } else {
                $returnVal = $originalValue;
            }

        } catch (Exception $e) {
            $returnVal = $default;
        }

        return $returnVal;
    }

}
