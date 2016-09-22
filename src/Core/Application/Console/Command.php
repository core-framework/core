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

namespace Core\Application\Console;


use Core\Contracts\Application\CliApplication;
use Core\Contracts\Application\Console\Command as CommandInterface;
use Core\Contracts\Application\Console\IOStream;
use Core\Contracts\Validators\OptionsValidator;

abstract class Command implements CommandInterface
{
    /**
     * @var CliApplication $application
     */
    private $application;
    /**
     * @var string $name
     */
    private $name;
    /**
     * @var array|Argument[] $arguments
     */
    private $arguments = [];
    /**
     * @var array|Option[]
     */
    private $options = [];
    /**
     * @var string $description
     */
    private $description;

    public function __construct(CliApplication $application)
    {
        $this->application = $application;
        $this->init();
    }

    /**
     * @inheritdoc
     */
    abstract function init();

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addArgument($name, $description = null, $default = null, $required = false)
    {
        return $this->arguments[$name] = new Argument($name, $description, $default, $required);
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @inheritdoc
     */
    public function hasArgument($name)
    {
        return isset($this->arguments[$name]);
    }

    /**
     * @inheritdoc
     */
    public function getArgument($name = null)
    {
        if (empty($name)) {
            return $this->arguments;
        }
        return $this->arguments[$name];
    }

    /**
     * @inheritdoc
     */
    public function addOption($name, $shortName, $description, $type = Option::OPTION_OPTIONAL)
    {
        $this->options[$name] = new Option($name, $shortName, $description, $type);
    }

    /**
     * @inheritdoc
     */
    public function getOptions($name = null)
    {
        if (is_null($name)) {
            return $this->options;
        }
        return $this->options[$name];
    }

    /**
     * @inheritdoc
     */
    public function input($name = null, $default = [])
    {
        return $this->application()->input($name, $default);
    }

    /**
     * @inheritdoc
     */
    public function application()
    {
        return $this->application;
    }

    /**
     * @inheritdoc
     */
    public function options($name = null, $default = false)
    {
        return $this->application()->inputOptions($name, $default);
    }

    /**
     * @inheritdoc
     */
    abstract function execute(IOStream $io);

    /**
     * @inheritdoc
     */
    public function showHelp(IOStream $io)
    {
        $io->writeln('Usage:', 'yellow');

        // Generate syntax
        $io->write("{$this->name} ", 'green');
        /** @var Argument $argument */
        foreach($this->arguments as $name => $argument) {
            if ($argument->isRequired()) {
                $io->write("{$name} ");
            } else {
                $io->write("[{$name}] ", 'yellow');
            }
        }
        /** @var Option $option */
        foreach($this->options as $name => $option) {
            $color = "";
            $value = "";
            if (!$option->isRequired()) {
                $color = "yellow";
                $io->write("[", $color);
            } else {
                $value = "=(value)";
            }

            if ($option->hasShortName()) {
                $io->write("-{$option->getShortName()}{$value} | ", $color);
            }
            $io->write("--{$option->getName()}", $color);

            if (!$option->isRequired()) {
                $io->write("]", $color);
            }
        }
        $io->writeln();
        $io->writeln();
        $io->writeln("Description:", 'yellow');
        $io->writeln("{$this->getDescription()}");
        $io->writeln();

        if (empty($this->arguments) && empty($this->options)) {
            $io->writeln("This command has no arguments or options!");
            return;
        }

        // Argument(s) description
        if (!empty($this->arguments)) {
            $io->writeln("Arguments:", 'yellow');
            foreach ($this->arguments as $i => $argument) {
                $optionalTxt = "";
                if (!$argument->isRequired()) {
                    $optionalTxt = "(optional)";
                }
                //$io->writeln("{$argument->getName()} {$argument->getDescription()} {$optionalTxt}");
                $io->write("{$argument->getName()} {$optionalTxt}", 'green', null, "%-40s ");
                $io->write($argument->getDescription(), 'white', null, "%s" . PHP_EOL);
            }
            $io->writeln();
            foreach ($this->arguments as $i => $argument) {
                if ($argument->hasValidation()) {
                    $io->writeln();
                    $io->writeln("Valid {$argument->getName()} Options:", 'yellow');
                    foreach($argument->getValidations() as $index => $validator) {
                        if ($validator instanceof OptionsValidator) {
                            foreach ($validator->getOptions() as $value => $desc) {
                                $io->write(" {$value}", 'green', null, "%-40s ");
                                $io->write($desc, 'white', null, "%s" . PHP_EOL);
                            }
                        }
                    }
                }
            }
            $io->writeln();
        }


        // Option(s) description
        if (!empty($this->options)) {
            $io->writeln("Options:", 'yellow');
            foreach ($this->options as $name => $option) {
                $requiredTxt = "";
                if ($option->isRequired()) {
                    $requiredTxt = "=(value)";
                }
                //$io->writeln("{$option->getName()} {$option->getDescription()} {$optionalTxt}");
                $io->write(
                    "--{$option->getName()}{$requiredTxt} | -{$option->getShortName()}{$requiredTxt} ",
                    "green",
                    null,
                    "%-40s "
                );
                $io->write($option->getDescription(), "white", null, "%s" . PHP_EOL);
            }
            $io->writeln();
        }


    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}