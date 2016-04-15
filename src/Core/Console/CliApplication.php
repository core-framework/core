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

namespace Core\Console;

use Core\Application\Application;
use Core\Application\BaseApplication;
use Core\Config\Config;
use Core\Contracts\CacheContract;
use Core\Contracts\CLIContract;

/**
 * Class CliApplication
 * @package Core\Console
 */
abstract class CliApplication extends BaseApplication implements CLIContract
{
    /**
     * @var bool Defines if verbosity is set
     */
    public static $verbose = false;
    /**
     * @var Command[] Contains all assigned commands
     */
    public $commands;
    /**
     * @var IOStream $io Contains IO stream object
     */
    public $io;
    /**
     * @inheritdoc
     */
    public $config;
    /**
     * @var string The Tool name
     */
    protected static $toolName = "Console";
    /**
     * @var string Usage string
     */
    protected $usage = "Console [globalOptions] command [arguments || [options]]";
    /**
     * @var string Version no.
     */
    protected static $version = "0.0.1";
    /**
     * @var Options[] Contains an array of (global) options
     */
    protected $options = [];
    /**
     * @var array Contains the map array for options short name -> long name pointers
     */
    protected $_map;
    /**
     * @var array
     */
    protected $_passOptions;

    protected $argv;

    protected $argc;

    private $parsedArgv;

    /**
     * @var bool Sets whether after global option parsing, the following command(if any) should be parsed as well
     */
    private $stopPropagation = false;

    /**
     * @param null $basePath
     * @param IOStream $io
     */
    public function __construct($basePath = null, IOStream $io)
    {
        $this->io = $io;
        parent::__construct($basePath);
        $this->loadCommands();
    }

    /**
     * Method to set/add all console Commands associated with this Console App
     *
     * @return mixed
     */
    protected abstract function loadCommands();

    /**
     * @inheritdoc
     */
    public function loadBaseComponents()
    {
        $this->registerAndLoad(
            'Cache',
            \Core\Cache\AppCache::class,
            [$this->getAbsolutePath("/storage/framework/cache")]
        );
        $this->registerAndLoad('Config', \Core\Config\Config::class, [$this->getConfigDir()]);
    }

    /**
     * @inheritdoc
     */
    public function getConfigPath()
    {
        return $this->getAbsolutePath('/config/cli.conf.php');
    }

    /**
     * @return string Returns Tool name
     */
    public function getToolName()
    {
        return static::$toolName;
    }

    /**
     * Sets Tool name
     *
     * @param $toolName
     */
    public function setToolName($toolName)
    {
        static::$toolName = $toolName;
    }

    /**
     * @return string returns the Usage string
     */
    public function getUsage()
    {
        return $this->usage;
    }

    /**
     * Sets the usage string
     *
     * @param $usage
     * @return $this
     */
    public function setUsage($usage)
    {
        $this->usage = $usage;

        return $this;
    }

    /**
     * @return string Returns the Tool version
     */
    public function getVersion()
    {
        return static::$version;
    }

    /**
     * Sets the tool version
     *
     * @param $version
     * @return $this
     */
    public function setVersion($version)
    {
        static::$version = $version;
    }

    /**
     * Returns Command object by command name
     *
     * @param $command
     * @return mixed
     * @throws \ErrorException
     */
    public function getCommand($command = null)
    {
        if (empty($command)) {
            $this->io->showErr("Missing Argument command name.", '\\LogicException');
        }

        if (!isset($this->commands[$command])) {
            $this->io->showErr("Command by name {$command} missing or not set", '\\LogicException');
        }

        return $this->commands[$command];
    }

    /**
     * Gets set options
     *
     * @param $name
     * @return Options[]
     * @throws \ErrorException
     */
    public function getOptions($name = null)
    {
        if (is_null($name)) {
            return $this->options;
        }
        if (!isset($this->options[$name])) {
            $this->io->showErr("Missing or not set Option by name {$name}.", '\\InvalidArgumentException');
        }
        return $this->options[$name];
    }

    /**
     * Set Options
     *
     * @param $name
     * @param null $shortName
     * @param null $description
     * @param null $definition
     * @return $this
     * @throws \ErrorException
     */
    public function setOptions($name, $shortName = null, $description = null, $definition = null)
    {
        if (!empty($shortName)) {
            $this->_map[$shortName] = $name;
        }
        if (empty($name)) {
            throw new \ErrorException("\$name cannot be null");
        }

        $this->options[$name] = new Options($name, $shortName, $description, $definition);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws \ErrorException
     * @throws \Exception
     */
    public function parse()
    {
        $argv = $this->argv = isset($GLOBALS['argv']) ? $GLOBALS['argv'] : $_SERVER['argv'];
        $argc = $this->argc = isset($GLOBALS['argc']) ? $GLOBALS['argc'] : $_SERVER['argc'];

        if (sizeof($argv) < 2) {
            $this->showHelp();
            return;
        }

        $argv = $this->parseConsoleOptions();

        if (sizeof($argv) === 0 || $this->stopPropagation === true) {
            $this->terminate();
            return;
        }

        if ($this->commandExists($argv[0])) {
            $this->parseConsoleCommands();
        } else {
            $this->io->showErr("Command $argv[0] not found");
        }
    }

    /**
     * Default show help
     */
    public function showHelp()
    {
        $this->io->writeln($this->toolName, "white");
        $this->io->writeln("version: " . $this->version, "green");
        $this->io->writeln("");
        $this->io->writeln("Usage:", "yellow");
        $this->io->writeln($this->usage);
        $this->io->writeln("");
        $this->io->writeln("Options(global):", "yellow");

        foreach ($this->options as $key => $val) {
            $this->io->write("--" . $key, 'green', null, "%-22s");
            if ($this->options[$key]->getShortName()) {
                $this->io->write(" (-" . $this->options[$key]->getShortName() . ") ", "white", null, "%-20s");
            } else {
                $this->io->write("  ");
            }
            $this->io->write($this->options[$key]->getDescription(), "white", null, "%s" . PHP_EOL);
        }

        $this->io->writeln("");
        if (sizeof($this->options) > 0) {
            $this->io->writeln("Commands:", "yellow");
            if (!empty($this->commands)) {
                foreach ($this->commands as $key => $val) {
                    $this->io->write($key, 'green', null, "%-22s");
                    if ($this->commands[$key]->hasOptions()) {
                        $optAsArr = $this->commands[$key]->getOptionsAsArray();
                        $shortOpts = "[-" . $optAsArr[0] . "]";
                        if (sizeof($optAsArr[1]) > 0) {
                            $longOpts = "[--(" . core_serialize($optAsArr[1]) . ")]";
                        } else {
                            $longOpts = "     ";
                        }
                        $this->io->write($shortOpts . " " . $longOpts . "\t", "white", null, "%-30s");
                    } else {
                        $this->io->write("\t", "white", null, "%-32s");
                    }
                    $this->io->write($this->commands[$key]->getDescription(), "white", null, "%s" . PHP_EOL);
                }
            } else {
                $this->io->writeln("No Commands found", 'yellow');
                return;
            }
        }
        $this->io->writeln(" ");
        $this->stopPropagation = true;
    }

    /**
     * Parse command line options
     *
     * @return array
     * @throws \ErrorException
     * @throws \Exception
     */
    protected function parseConsoleOptions()
    {
        $argv = $this->argv;
        $this->parsedArgv = &$argv;

        array_shift($argv); //reset indices
        $optsArr = $this->getOptionsAsArray($this->options);
        $globalOpts = getopt($optsArr[0], $optsArr[1]);

        if (!empty($globalOpts)) {
            foreach ($argv as $index => $item) {
                $item = preg_replace('/-+/', "", $item);
                if (isset($globalOpts[$item])) {
                    $this->parseOptions($item);
                    unset($argv[$index]);
                    $argv = array_values($argv); //reset indices
                }
            }
        }

        return $argv;
    }

    /**
     * Returns options as array. Where array[0] = [string] shortOptions and array[1] = (array) longOptions
     *
     * @param array $arr
     * @return array
     * @throws \ErrorException
     */
    public function getOptionsAsArray(array $arr)
    {
        $longOpts = [];
        $shortOpts = "";

        if (!empty($arr)) {
            foreach ($arr as $key => $val) {

                if (!$val instanceof Options) {
                    throw new \ErrorException("Given parameter must contain an array of options of type Options");
                }

                $sym = $val->getIsRequired();
                $shortName = $val->getShortname();
                array_push($longOpts, $key . $sym);
                $shortOpts .= $shortName . $sym;
            }

        }

        return [$shortOpts, $longOpts];
    }

    /**
     * Parse set options
     *
     * @param $name
     * @throws \Exception
     */
    public function parseOptions($name)
    {
        $found = false;
        if (isset($this->options[$name])) {
            $def = $this->options[$name]->getDefinition();
            call_user_func($def);
            $found = true;
        } else {
            foreach ($this->options as $key => $val) {
                if ($this->options[$key]->getShortName() === $name) {
                    $def = $this->options[$key]->getDefinition();
                    call_user_func($def);
                    $found = true;
                }
            }
        }

        if (!$found) {
            throw new \Exception("Option of type $name not found");
        }
    }

    /**
     * Returns true if command exists else returns false
     *
     * @param $name
     * @return bool
     */
    public function commandExists($name)
    {
        return isset($this->commands[$name]) ? true : false;
    }

    /**
     * Parse command line Commands
     *
     * @param null $argv
     * @throws \ErrorException
     */
    protected function parseConsoleCommands($argv = null)
    {
        if (is_null($argv)) {
            $argv = $this->parsedArgv;
        }

        /** @var $command Command */
        $command = $this->commands[$argv[0]];
        $def = $command->getDefinition();
        $options = $command->getOptions();
        $arguments = $command->getArguments();

        array_shift($argv);
        $optsAsArr = $this->getOptionsAsArray($options);

        while (count($argv) > 0) {
            if (substr($argv[0], 0, 2) == '--') {

                $optionName = preg_replace('/-+/', "", $argv[0]);
                $this->parseCommandOptions($optionName, $options[$optionName], $optsAsArr[1], $argv);
                array_shift($argv);

            } elseif (substr($argv[0], 0, 1) == '-') {

                $shortName = preg_replace('/-+/', "", $argv[0]);
                $optionName = $command->getNameFromShortName($shortName);
                $this->parseCommandOptions($optionName, $options[$optionName], str_split($optsAsArr[0]), $argv);
                array_shift($argv);

            } elseif (count($arguments) !== 0) {
                $argumentName = $arguments[0]->getName();
                if ($arguments[0]->getRequired() === true && empty($argv[0])) {
                    $this->io->showErr("Argument {$arguments[0]->getRequired()} is required");
                    exit;
                }
                array_shift($arguments);
                $this->_passOptions[$argumentName] = $argv[0];
                array_shift($argv);

            }
        }

        $arr = explode('::', $def);
        $reflection = new \ReflectionClass($arr[0]);

        $method = $reflection->getMethod($arr[1]);
        $params = $method->getParameters();

        $pass = array();
        if (count($params) > 0) {
            foreach ($params as $index => $param) {
                /* @var $param \ReflectionParameter */
                if (isset($this->_passOptions[$param->getName()])) {
                    $pass[] = $this->_passOptions[$param->getName()];
                } elseif (isset($this->_passOptions[$index])) {
                    $pass[] = $this->_passOptions[$index];
                } elseif ($param->isDefaultValueAvailable()) {
                    $pass[] = $param->getDefaultValue();
                } else {
                    $pass[] = null;
                }
            }
            $method->invokeArgs($this, $pass);
        } else {
            $method->invoke($this);
        }
    }

    /**
     * Parse set command options
     *
     * @param $optionName
     * @param Options $option
     * @param $options
     * @param array $argv
     */
    private function parseCommandOptions($optionName, Options $option, $options, array $argv)
    {
        if (in_array($optionName, $options)) {
            if ($option->getIsRequired() === null) {
                $this->_passOptions[$optionName] = true;
            } elseif ($option->getIsRequired() === true) {
                $this->_passOptions[$optionName] = $argv[1];
                array_slice($argv, 2);
            } else {
                if (strpos($argv[1], '-') > -1) {
                    $this->_passOptions[$optionName] = true;
                } else {
                    $this->_passOptions[$optionName] = $argv[1];
                    array_slice($argv, 2);
                }
            }
        }
    }

    /**
     * Shows the current version of Cli app
     */
    public function showVersion()
    {
        $this->io->writeln($this->toolName, "white");
        $this->io->writeln("version: " . $this->version, "green");
        $this->stopPropagation = true;
    }

    /**
     * Allows the use of dot separated array key access
     *
     * @param $arr
     * @param $path
     * @param $value
     */
    public function assignArrayByPath(&$arr, $path, $value)
    {
        $keys = explode('.', $path);

        while ($key = array_shift($keys)) {
            $arr = &$arr[$key];
        }

        if (is_array($arr)) {
            array_push($arr, $value);
        } else {
            $arr = $value;
        }
    }

    /**
     * Returns value of given path. Where path is a dot(.) separated array path
     *
     * @param $arr
     * @param $path
     * @return mixed
     */
    public function getArrayByPath(&$arr, $path)
    {
        $keys = explode('.', $path);

        while ($key = array_shift($keys)) {
            $arr = &$arr[$key];
        }

        return $arr;
    }

    /**
     * Test helloWorld
     *
     * @param $name
     */
    public function helloWorld($name = "world") {
        echo "hello " . $name . PHP_EOL;
    }

    /**
     * Magic sleep method
     */
    public function __sleep()
    {
        return ['verbose', 'commands', 'toolName', 'usage', 'version', 'options', '_maps', 'stopPropagation'];
    }

    /**
     * Magic wakeup method
     */
    public function __wakeup()
    {
        $this->io = Application::get('IOStream');
    }

    /**
     * @inheritdoc
     */
    protected function loadConfig()
    {
        /** @var \Core\Cache\AppCache $cache */
        $cache = $this->cache;

        if (!$this->cache instanceof CacheContract) {
            throw new \ErrorException("Cache Service not found.");
        }

        if ($cache->cacheExists('cli.conf')) {
            $this->configArr = $cache->getCache('cli.conf');
        } else {
            $this->configArr = $this->getConfigArr();
            $cache->cacheContent('cli.conf', $this->configArr, 0);
        }
    }

    /**
     * @inheritdoc
     */
    protected function registerServicesFromConfig()
    {
        parent::registerServicesFromConfig();
        $this->loadConf();
        $this->setDefaultCommands();
    }

    /**
     * Load Configuration settings
     *
     * @throws \ErrorException
     */
    protected function loadConf()
    {
        $config = $this->configArr;
        if (isset($config['$commands']) && !empty($config['$commands'])) {
            $commandsArr = $config['$commands'];
            foreach ($commandsArr as $index => $arr) {
                $command = $this->addCommand($arr['name'], $arr['description'], $arr['definition']);
                if (isset($arr['arguments'])) {
                    $args = $arr['arguments'];
                    $command->addArguments($args['name'], $args['isRequired'], $args['description']);
                }
            }
        }

        if (isset($config['$options']) && !empty($config['$options'])) {
            $optionsArr = $config['$options'];
            foreach ($optionsArr as $index => $arr) {
                $this->setOptions($arr['name'], $arr['shortName'], $arr['description'], $arr['definition']);
            }
        }

    }

    /**
     * Adds command to command list
     *
     * @param $name
     * @param $description
     * @param $definition
     * @return Command
     */
    public function addCommand($name, $description, $definition)
    {
        $this->commands[$name] = new Command($name, $description, $definition);

        return $this->commands[$name];
    }

    /**
     * Sets defaults for ClI applications
     */
    private function setDefaultCommands()
    {
        $this->setOptions("help", "h", "Prints the help for this tool", get_class($this) . "::showHelp");
        $this->setOptions(
            "verbose",
            "V",
            "Increases verbosity of message output",
            function () {
                $this::$verbose = true;
            }
        );
        $this->setOptions("version", "v", "Display the version of this tool", get_class($this) . "::showVersion");
    }
}