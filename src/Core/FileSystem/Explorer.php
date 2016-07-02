<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 18/04/16
 * Time: 9:58 AM
 */

namespace Core\FileSystem;


use Core\Iterators\Filters\DirectoryExcludeFilter;
use Core\Iterators\Filters\DirectoryRegexFilter;
use Core\Iterators\Filters\FilenameRegexFilter;

class Explorer implements \IteratorAggregate, \Countable
{

    const SEARCH_FILES = 'FileFilter';
    const SEARCH_DIR = 'DirectoryFilter';

    protected $baseDir;
    protected $searchFor;
    protected $pattern;
    protected $ignoreVcsFiles = true;
    protected $ignore = [];
    
    protected $flags = [
        \FilesystemIterator::CURRENT_AS_FILEINFO,
        \FilesystemIterator::KEY_AS_FILENAME,
        \FilesystemIterator::SKIP_DOTS
    ];

    private static $vcsFiles = ['.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg'];

    /**
     * @var \RecursiveIteratorIterator
     */
    protected $iterator;

    /**
     * Explorer constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return static
     */
    public static function find()
    {
        return new static();
    }

    /**
     * Get files with pattern
     *
     * @param $pattern
     * @return $this
     */
    public function files($pattern)
    {
        $this->searchFor = static::SEARCH_FILES;
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Search Directories with pattern
     *
     * @param $pattern
     * @return $this
     */
    public function directories($pattern)
    {
        $this->searchFor = static::SEARCH_DIR;
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Files/Directories to ignore
     *
     * @param string|array $pattern
     * @return $this
     */
    public function ignore($pattern)
    {
        if (!is_array($pattern)) {
            $pattern = [$pattern];
        }
        array_merge($this->ignore, $pattern);

        return $this;
    }

    /**
     * Base Directory to search in
     *
     * @param $dir
     * @return $this
     */
    public function in($dir)
    {
        $this->baseDir = $dir;

        return $this;
    }

    /**
     * Callback to map against files in directory
     *
     * @var string $key
     * @var \SplFileInfo $fileInfo
     * @param \Closure $callback
     */
    public function map(\Closure $callback)
    {
        foreach ($this as $key => $fileInfo) {
            $callback($key, $fileInfo);
        }
    }

    /**
     * Get full path with pattern
     *
     * @return string
     */
    protected function getRealPath()
    {
        return $this->baseDir . DIRECTORY_SEPARATOR . $this->pattern;
    }

    /**
     * Add Iterator flags
     *
     * @param $flag
     */
    public function addFlag($flag)
    {
        $this->flags[] = $flag;
    }

    /**
     * Set Iterator flags
     *
     * @param $flags
     */
    public function setFlag($flags)
    {
        $this->flags = [$flags];
    }

    /**
     * Build combined flag integer value
     *
     * @return int|mixed
     */
    protected function buildFlag()
    {
        $final = 0;
        if (!empty($this->flags)) {
            foreach ($this->flags as $flag) {
                if ($final == 0) {
                    $final = $flag;
                    continue;
                }

                $final |= $flag;
            }
        }

        return $final;
    }

    /**
     * @return \RecursiveIteratorIterator
     */
    public function getIterator()
    {
        //$iterator = new FileSearchIterator($this->baseDir, $this->pattern, $this->buildFlag());
        if (!is_null($this->iterator)) {
            $this->iterator->rewind();
            return $this->iterator;
        }
        
        $iterator = new \RecursiveDirectoryIterator($this->baseDir, $this->buildFlag());
        
        if ($this->ignoreVcsFiles) {
            $this->ignore = array_merge($this->ignore, static::$vcsFiles);
        }

        if (!empty($this->ignore)) {
            $iterator = new DirectoryExcludeFilter($iterator, $this->ignore);
        }

        if ($this->searchFor === static::SEARCH_FILES) {
            $iterator = new FilenameRegexFilter($iterator, $this->pattern);
        } else {
            $iterator = new DirectoryRegexFilter($iterator, $this->pattern);
        }
        
        $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);

        return $this->iterator = $iterator;
    }

    /**
     * @return int
     */
    public function count()
    {
        return iterator_count($this->getIterator());
    }
}