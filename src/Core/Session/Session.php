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


namespace Core\Session;


use Core\Reactor\DataCollection;

class Session extends DataCollection
{
    private $started;

    protected $sessionHandler;

    /**
     * @inheritDoc
     */
    public function __construct(\SessionHandlerInterface $sessionHandler = null)
    {
        parent::__construct();
        $this->setSessionHandler($sessionHandler);
        $this->start();
        $this->collection = &$_SESSION;
    }

    protected function setSessionHandler(\SessionHandlerInterface $sessionHandler = null)
    {
        if (!empty($sessionHandler)) {
            $this->sessionHandler = $sessionHandler;
            session_set_save_handler($sessionHandler);
        }
    }

    public function start()
    {
        if (ini_get('session.auto_start')) {
            $this->started = true;
        }
        $this->started ?: session_start();
        $this->started = true;
        $this->setMeta();
    }

    protected function setMeta()
    {
        $time = time();
        $_SESSION['__meta'] = array(
            'ip'       => $_SERVER['REMOTE_ADDR'],
            'name'     => session_name(),
            'created'  => $time,
            'lastModified' => $time,
        );
    }

    public function status()
    {
        return $this->started;
    }

    public function destroy()
    {
        $_SESSION = [];
        session_unset();
        session_destroy();
    }

    public function close()
    {
        session_write_close();
    }

    public function set($key, $value)
    {
        parent::set($key, $value);
        parent::set('__meta.lastModified', time());
    }
}