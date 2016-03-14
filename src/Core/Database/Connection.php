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

namespace Core\Database;

use PDO;

/**
 * This class is the base database class which is an extension of PDO
 *
 * @package Core\Connection
 * @version $Revision$
 * @license http://creativecommons.org/licenses/by-sa/4.0/
 * @link http://coreframework.in
 * @author Shalom Sam <shalom.s@coreframework.in>
 */
class Connection extends PDO
{
    /**
     * @var array Internal Caching
     */
    private $cache = [];

    protected static $config;

    /**
     * Creates an instance of the pdo class
     *
     * @param array $config
     * @throws \ErrorException
     * @throws \Exception
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
        $this->init($config);
    }

    /**
     * @param array $config
     * @return PDO
     */
    public function init(array $config = [])
    {
        $options = [];
        $defaultOptions = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_EMULATE_PREPARES => false);

        if (empty($config)) {
            $config = $this->getConfig();
        }

        $db = isset($config['db']) ? $config['db'] : 'test';
        $type = isset($config['type']) ? $config['type'] : 'mysql';
        $host = isset($config['host']) ? $config['host'] : 'localhost';
        $user = isset($config['user']) ? $config['user'] : null;
        $pass = isset($config['pass']) ? $config['pass'] : null;
        $port = isset($config['port']) ? $config['port'] : '';

        $dsnString = $type . ':' . 'dbname=' .$db . ';' . 'host=' . $host . ';' . $port;


        try {

            if (isset($config['options'])) {
               $options = array_merge($defaultOptions, $config['options']);
            }

            parent::__construct($dsnString, $user, $pass, $options);


        } catch (\Exception $e) {
            throw new \PDOException("There was a problem connecting to the database: {$e->getMessage()}");
        }
    }

    public function setDefaultOptions($options = [])
    {
        if (!empty($options)) {
            foreach ($options as $key => $value) {
                $this->setAttribute($key, $value);
            }
        }
    }

    /**
     * Gets the prepared statement
     *
     * @param $query
     * @return mixed
     */
    public function getPrepared($query)
    {
        $hash = md5($query);
        if (!isset($this->cache[$hash])) {
            $this->cache[$hash] = $this->prepare($query);
        }
        return $this->cache[$hash];
    }

    public static function setConfig($config)
    {
        self::$config = $config;
    }

    public static function getConfig()
    {
        return self::$config;
    }

    /**
     * Reset database cache
     */
    public function __destruct()
    {
        $this->cache = null;
    }

}