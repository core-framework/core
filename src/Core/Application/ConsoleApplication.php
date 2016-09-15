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


namespace Core\Application;


use Core\Application\Console\Argument;
use Core\Application\Console\IOStream;
use Core\Application\Console\Option;
use Core\Contracts\Application\CliApplication;
use Core\Contracts\Config;
use Core\Contracts\Events\Dispatcher;
use Core\Contracts\Application\Console\Command;
use Core\Contracts\Reactor\Runnable;
use Core\Reactor\DataCollection;

class ConsoleApplication extends BaseApplication implements CliApplication
{

    protected $validOptionTypes = ['-', '--'];
    protected $applicationName = "Core PHP Framework Console";
    protected $version = "v1.0.0";

    /**
     * @var string Usage string
     */
    protected $usage = "reactor [globalOptions] command [arguments] [options]";
    /**
     * @var IOStream $io Contains IO stream object
     */
    protected $io;

    protected $argv;

    protected $argc;

    protected $commands = [
        'help' => \Core\Application\Console\Commands\HelpCommand::class,
        'status' => \Core\Application\Console\Commands\StatusCommand::class,
        'setup' => \Core\Application\Console\Commands\SetupCommand::class,
        'clearcache' => \Core\Application\Console\Commands\ClearCacheCommand::class,
        'server' => \Core\Application\Console\Commands\ServerCommand::class,
        'optimize' => \Core\Application\Console\Commands\OptimizeCommand::class,
        'create' => \Core\Application\Console\Commands\CreateCommand::class,
        'router' => \Core\Application\Console\Commands\RouterCommand::class,
        'migrate:install' => \Core\Application\Console\Commands\MigrateInstallCommand::class,
        'migrate:run' => \Core\Application\Console\Commands\MigrateRunCommand::class,
        'seeder:run' => \Core\Application\Console\Commands\SeederCommand::class,
    ];

    /**
     * @var array|Option[]
     */
    protected $options = [
        'help' => \Core\Application\Console\Options\GlobalOptions::class
    ];

    protected $pipeline;

    /**
     * @var array $components
     */
    protected $components = [
        'Router' => ['\Core\Router\Router', ['App']],
        'io' => \Core\Application\Console\IOStream::class
    ];

    private $isVerbose = false;

    /**
     * @var Command $currentCommand
     */
    private $currentCommand;
    /**
     * @var array $inputArguments
     */
    private $inputArguments;

    /**
     * @inheritDoc
     */
    public function __construct($basePath)
    {
        $this->inputArguments = new DataCollection();
        parent::__construct($basePath);
    }

    /**
     * @return IOStream|object
     */
    public function io()
    {
        if (!$this->io) {
            $this->io = $this->get('io');
        }
        return $this->io;
    }

    /**
     * @inheritdoc
     */
    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->on('core.app.config.booted', [$this, 'loadOptions'], 3);
        $dispatcher->on('core.app.config.booted', [$this, 'loadCommands'], 4);
        parent::subscribe($dispatcher);
    }

    /**
     * @param Config $config
     */
    public function loadCommands(Config $config)
    {
        $commands = array_merge($this->commands, $config->get('commands', []));
        $this->loader($commands, 'commands');
    }

    /**
     * @param Config $config
     */
    public function loadOptions(Config $config)
    {
        $options = array_merge($this->options, $config->get('options', []));
        $this->loader($options, 'options');
    }

    /**
     * @param array $config
     * @param $type
     */
    private function loader(array $config, $type)
    {
        foreach ($config as $name => $class) {
            $implements = class_implements($class);
            if ($implements && in_array(Runnable::class, $implements)) {
                /** @var Runnable $class */
                $class = new $class($this);
                $class->run();
                continue;
            } elseif ($type === 'options') {
                $this->addGlobalOptions($class['name'], $class['shortName'], $class['description'], $class['isRequired']);
                continue;
            }
            $this->{$type}[$name] = new $class($this);
        }
    }

    /**
     * @inheritdoc
     */
    public function addGlobalOptions($name, $shortName, $description, $isRequired = Option::OPTION_OPTIONAL)
    {
        $this->options[$name] = new Option($name, $shortName, $description, $isRequired);
    }

    /**
     * @inheritdoc
     */
    public function getGlobalOptions(array $pipeline = [])
    {
        while (strContains('-', null !== $argument = array_shift($pipeline))) {
            if (strContains('--', $argument)) {
                $this->parseOption($argument, Option::OPTION_LONG);
            } elseif (strContains('-', $argument)) {
                $this->parseOption($argument, Option::OPTION_SHORT);
            }
        }

        return $this->inputArguments->get('options');
    }

    /**
     * @inheritdoc
     */
    public function addCommand($name, $class)
    {
        $this->commands[$name] = $class;
    }

    /**
     * @inheritdoc
     */
    public function hasCommand($name)
    {
        return isset($this->commands[$name]);
    }

    /**
     * @inheritdoc
     */
    public function getCommand($name = null)
    {
        if (is_null($name)) {
            return $this->commands;
        }
        return isset($this->commands[$name]) ? $this->commands[$name] : false;
    }

    /**
     * @inheritdoc
     */
    public function input($argumentName = null, $default = [])
    {
        return $this->inputArguments->get($argumentName, $default);
    }

    /**
     * @inheritdoc
     */
    public function inputOptions($optionName = null, $default = false)
    {
        $optionName = is_null($optionName) ? '' : ".{$optionName}";
        if ($this->inputArguments->has('options'.$optionName)) {
            return $this->inputArguments->get('options'.$optionName);
        } else {
            if (isset($this->options[$optionName])) {
                $shortName = "." .$this->options[$optionName]->getShortName();
                return $this->inputArguments->get('options'.$shortName, $default);
            }
        }
        return $default;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->dispatch('core.app.run.pre', $this);

        $this->argv = isset($GLOBALS['argv']) ? $GLOBALS['argv'] : $_SERVER['argv'];
        $this->argc = isset($GLOBALS['argc']) ? $GLOBALS['argc'] : $_SERVER['argc'];

        $argv = &$this->argv;
        array_shift($argv);

        try {
            $this->parse($argv);
        } catch (\Exception $e) {
            $this->handleError($e);
        }

    }

    /**
     * @param array $argv
     * @throws \ErrorException
     * @throws \Exception
     */
    protected function parse($argv)
    {
        $this->dispatch('core.app.parse.pre', $this);

        $this->pipeline = $argv;
        
        while (null !== $argument = array_shift($this->pipeline)) {
            if (!strContains('--', $argument) && !strContains('-', $argument)) {
                $this->parseConsoleArgument($argument);
            } elseif (strContains('--', $argument)) {
                $this->parseOption($argument, Option::OPTION_LONG);
            } elseif (strContains('-', $argument)) {
                $this->parseOption($argument, Option::OPTION_SHORT);
            }
        }

        $this->parseGlobalOptions();
        $this->execute();
        $this->terminate();
    }

    /**
     * @param $argument
     */
    public function parseConsoleArgument($argument)
    {
        if (isset($this->commands[$argument]) && !isset($this->currentCommand)) {
            $this->currentCommand = new $this->commands[$argument]($this);
        } elseif (isset($this->currentCommand)) {
            $this->inputArguments[] = $argument;
        }
    }

    /**
     * Parse Console Options
     *
     * @param string $argument The Option string as argument
     * @param string $type The Option type (Long Option || short Option)
     */
    public function parseOption($argument, $type = Option::OPTION_LONG)
    {
        if (!in_array($type, $this->validOptionTypes)) {
            throw new \InvalidArgumentException("Invalid Option type {$type}");
        }

        $value = null;

        if (!strContains('=', $argument)) {
            $optionName = str_replace($type, '', $argument);
        } else {
            $argument = str_replace($type, '', $argument);
            list($optionName, $value) = explode("=", $argument);
        }

        if ($type === Option::OPTION_SHORT) {
            if ($currentCommand = $this->currentCommand) {
                $this->parseShortOption($optionName, $currentCommand->getOptions(), $value);
            } else {
                $this->parseShortOption($optionName, $this->options, $value);
            }
        } elseif ($type === Option::OPTION_LONG) {
            if ($currentCommand = $this->currentCommand) {
                $this->parseLongOption($optionName, $currentCommand->getOptions(), $value);
            } else {
                $this->parseLongOption($optionName, $this->options, $value);
            }
        }

    }

    /**
     * Parse short option and assign value (if required)
     *
     * @param $optionName
     * @param array $options
     * @param null $value
     */
    private function parseShortOption($optionName, array $options, $value = null)
    {
        foreach($options as $name => $option) {
            /** @param Option $option */
            if ($option->getShortName() === $optionName) {
                $value = $this->parseOptionValue($option, $value);
            }
        }
    }

    /**
     * Parse long option and assign value (if required)
     *
     * @param $optionName
     * @param array $options
     * @param null $value
     */
    private function parseLongOption($optionName, array $options, $value = null)
    {
        foreach($options as $name => $option) {
            /** @param Option $option */
            if ($option->getName() === $optionName) {
                $value = $this->parseOptionValue($option, $value);
            }
        }
    }

    /**
     * Parse option to determine correct value
     *
     * @param Option $option
     * @param null $value
     */
    private function parseOptionValue(Option $option, $value = null)
    {
        $optionName = $option->getName();
        if ($option->isFlag()) {
            $value = true;
        } elseif ($option->isOptional()) {
            $value = !is_null($value) ? $value : $option->getDefault();
        } elseif ($option->isRequired()) {
            $value = !is_null($value) ? $value : array_shift($this->pipeline);
        }
        $this->inputArguments->set('options.'.$optionName, $value);
    }

    /**
     * Execute command
     */
    protected function execute()
    {
        $this->dispatch('core.app.execute.pre', $this);
        //$io = $this->io();
        $argv = &$this->argv;

        if (!isset($this->currentCommand) && $this->inputArguments->count() <= 1 && sizeof($argv) > 0) {

            $closest = $this->closestCommand($argv);
            if ($closest) {
                throw new \InvalidArgumentException("Command not found.\nDid you mean '{$closest}'?");
            } else {
                throw new \InvalidArgumentException("Command not found: {$argv[0]}");
            }

        } elseif (!isset($this->currentCommand)) {
            $this->currentCommand = new $this->commands['help']($this);
        }

        if (!$this->currentCommand instanceof Command) {
            throw new \RuntimeException("Command must implement Core\\Contracts\\Application\\Console\\Command");
        }

        $this->bindArguments();
        $this->currentCommand->execute($this->io());

        $this->dispatch('core.app.execute.post', $this);
    }

    /**
     * Finds closest command matching input (typo) string
     *
     * @param array $argv
     * @return bool
     */
    public function closestCommand(array $argv)
    {
        $keys = array_keys($this->commands);
        $found = false;
        foreach ($keys as $i => $key) {
            if ((strContains($argv[0], $key) || strContains($key, $argv[0])) && $found === false) {
                $found = $key;
            }
        }

        return $found;
    }

    /**
     * Bind console arguments to defined arguments
     */
    protected function bindArguments()
    {
        /** @var Argument[] $arguments */
        $arguments = array_values($this->currentCommand->getArgument());

        if (sizeof($arguments) > sizeof($this->inputArguments) && $arguments[0]->isRequired()) {
            throw new \RuntimeException("Too many arguments");
        }

        foreach ($arguments as $i => $argument) {
            /** @var Argument $argument */
            if (isset($this->inputArguments[$i])) {
                $this->inputArguments[$argument->getName()] = $argument->validate($this->inputArguments[$i]);
            } elseif ($argument->isOptional()) {
                $this->inputArguments[$argument->getName()] = $argument->getDefault();
            } else {
                throw new \RuntimeException("Too few arguments. Argument {$argument->getName()} requires a value.");
            }
        }
    }

    /**
     * Parse global (application) Options
     */
    protected function parseGlobalOptions()
    {
        if ($this->inputArguments->has('options.verbose')) {
            $this->isVerbose = true;
        }
        if ($this->inputArguments->has('options.help')) {
            $this->currentCommand = new $this->commands['help']($this);
        }
    }

    /**
     * Displays error on cli
     *
     * @param \Exception $exception
     */
    protected function handleError(\Exception $exception)
    {
        $this->io()->showErr($exception->getMessage(), $exception);
    }

    /**
     * Default show help
     */
    public function showHelp()
    {
        $this->io->writeln($this->applicationName, "white");
        $this->io->writeln("Version: " . $this->version, "green");
        $this->io->writeln();
        $this->io->writeln("Usage:", "yellow");
        $this->io->writeln($this->usage);
        $this->io->writeln();
        $this->io->writeln("Option(global):", "yellow");

        foreach ($this->options as $option) {
            /** @var Option $option */
            $this->io->write("--{$option->getName()} | -{$option->getShortName()} ", "green", null, "%-30s ");
            $this->io->write($option->getDescription(), "white", null, "%s" . PHP_EOL);
        }

        $this->io->writeln();
        $this->io->writeln("Commands:", "yellow");

        foreach($this->commands as $command) {
            /** @var Command $command */
            $this->io->write($command->getName(), "green", null, "%-30s ");
            $this->io->write($command->getDescription(), "white", null, "%s" . PHP_EOL);
        }

        $this->io->writeln();
        $this->io->writeln("For detailed help type - help [commandName]", 'yellow');
    }

}