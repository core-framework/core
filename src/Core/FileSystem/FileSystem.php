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


namespace Core\FileSystem;

use Core\Contracts\FileSystem\FileSystem as FileSystemInterface;

class FileSystem implements FileSystemInterface
{

    /**
     * Reads and returns the contents of the given File(path).
     *
     * @param $path
     * @param int $lockFlag
     * @return string
     */
    public function read($path, $lockFlag = LOCK_SH)
    {
        if (!is_readable($path)) {
            throw new \InvalidArgumentException("Provided file(path) ({$path}) is not readable.");
        }

        $content = '';
        $handle = fopen($path, 'r');
        
        if ($handle) {
            try {
                if (flock($handle, $lockFlag)) {
                    while (! feof($handle)) {
                        $content .= fread($handle, filesize($path));
                    }
                }
            } finally {
                fclose($handle);
            }
        }

        return $content;
    }

    /**
     * Returns the contents of the given File(path).
     *
     * @param $path
     * @param bool $lock
     * @param null $flags
     * @return string
     */
    public function getContents($path, $lock = false, $flags = null)
    {
        if ($this->exists($path)) {
            return $lock ? $this->read($path) : file_get_contents($path, $flags);
        }

        throw new \InvalidArgumentException("File: {$path} not found.");
    }

    /**
     * Writes content to File
     * 
     * @param $path
     * @param $content
     * @param bool $lock
     * @return bool
     */
    public function write($path, $content, $lock = false)
    {
        if (!is_writable($path)) {
            //throw new \InvalidArgumentException("Provided path: {$path}, is not writable.");
            touch($path);
        }

        return file_put_contents($path, $content, $lock ? LOCK_EX : 0) ? true : false;
    }

    /**
     * Prepends given content before the original content in the file
     * 
     * @param $path
     * @param $content
     * @param bool $lock
     * @return bool
     */
    public function prepend($path, $content, $lock = false)
    {
        $data = $this->getContents($path, $lock);
        return $this->write($path, $content.$data, $lock);
    }

    /**
     * Appends given content after the original content in the file
     *
     * @param $path
     * @param $content
     * @param bool $lock
     * @return bool
     */
    public function append($path, $content, $lock = false)
    {
        $data = $this->getContents($path, $lock);
        return $this->write($path, $data.$content, $lock);
    }

    /**
     * Return true if file exists, else false
     *
     * @param $path
     * @return mixed
     */
    public function exists($path)
    {
        return file_exists($path);
    }

    /**
     * Deletes given file
     *
     * @param $path
     * @return mixed
     */
    public function delete($path)
    {
        return unlink($path);
    }

    /**
     * Moves files from current location to new provided path location
     *
     * @param $path
     * @param $newPath
     * @return mixed
     */
    public function move($path, $newPath)
    {
        return rename($path, $newPath);
    }

    /**
     * Copies file(s) to given destination
     *
     * @param $path
     * @param $destination
     * @param bool $recursive
     * @return mixed
     * @throws \Exception
     */
    public function copy($path, $destination, $recursive = false)
    {
        if ($recursive) {
            try {
                copyr($path, $destination);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return copy($path, $destination);
    }

    /**
     * Makes a Directory at given path
     *
     * @param $path
     * @param int $mode
     * @param bool $recursive
     * @param bool $force
     * @return mixed
     */
    public function mkdir($path, $mode = 0755, $recursive = false, $force = false)
    {
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }

        return mkdir($path, $mode, $recursive);
    }

    /**
     * Returns the pathinfo (Array) of the given File(path)
     *
     * @param $path
     * @param null $options
     * @return array
     */
    public function pathInfo($path, $options = null)
    {
        if (!is_null($options)) {
            return pathinfo($path, $options);
        }

        return pathinfo($path);
    }

    /**
     * Returns the file name with extension of the given File(path)
     * 
     * @param $path
     * @return mixed
     */
    public function basename($path)
    {
        return $this->pathInfo($path, PATHINFO_BASENAME);
    }

    /**
     * Returns the Directory name from the given File Path
     * 
     * @param $path
     * @return mixed
     */
    public function dirname($path)
    {
        return $this->pathInfo($path, PATHINFO_DIRNAME);
    }

    /**
     * Returns the file name from the given File Path
     * 
     * @param $path
     * @return mixed
     */
    public function filename($path)
    {
        return $this->pathInfo($path, PATHINFO_FILENAME);
    }

    /**
     * Returns the extension of the given file(path)
     * 
     * @param $path
     * @return mixed
     */
    public function extension($path)
    {
        return $this->pathInfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Returns the file size of the given file(path)
     * 
     * @param $path
     * @return int
     */
    public function size($path)
    {
        return filesize($path);
    }

    /**
     * Returns the last modified timestamp of the File(path)
     *
     * @param $path
     * @return int
     */
    public function lastModified($path)
    {
        return filemtime($path);
    }

    /**
     * Returns true if given path is a directory, else false.
     *
     * @param $path
     * @return boolean
     */
    public function isDir($path)
    {
        return is_dir($path);
    }

    /**
     * Returns true if given path is a file, else false.
     *
     * @param $path
     * @return boolean
     */
    public function isFile($path)
    {
        return is_file($path);
    }

    /**
     * Returns the file type of the given File(path).
     *
     * @param $path
     * @return mixed
     */
    public function type($path)
    {
        return filetype($path);
    }

    /**
     * Returns the MIME file type of given File(path)
     *
     * @param $path
     * @return mixed
     */
    public function mime($path)
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }

    /**
     * Returns true if file(path) is writable, else false.
     *
     * @param $path
     * @return boolean
     */
    public function isWritable($path)
    {
        return is_writable($path);
    }

    /**
     * Returns files matching a (glob) pattern in a given Directory.
     *
     * @param $pattern
     * @param $dir
     * @param int $flags
     * @return mixed
     */
    public function find($pattern, $dir = '', $flags = 0)
    {
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);
        $pathWithPattern = $dir . DIRECTORY_SEPARATOR . $pattern;
        return glob($pathWithPattern, $flags);
    }
}