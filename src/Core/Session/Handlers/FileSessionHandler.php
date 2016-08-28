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


namespace Core\Session\Handlers;


use Core\Contracts\FileSystem\FileSystem;
use Core\FileSystem\Explorer;

class FileSessionHandler implements \SessionHandlerInterface
{

    protected $fileSystem;

    protected $dir;

    /**
     * FileSessionHandler constructor.
     * @param $fileSystem
     * @param $dir
     */
    public function __construct(FileSystem $fileSystem, $dir = '/tmp')
    {
        $this->fileSystem = $fileSystem;
        $this->dir = $dir;
    }

    /**
     * @inheritDoc
     */
    public function open($save_path, $name)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function read($sessionId)
    {
        $path = $this->dir . '/' . $sessionId;
        if ($this->fileSystem->exists($path)) {
            return $this->fileSystem->read($path);
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function write($sessionId, $data)
    {
        $this->fileSystem->write($this->dir.'/'.$sessionId, $data, true);
    }


    /**
     * @inheritDoc
     */
    public function destroy($sessionId)
    {
        $this->fileSystem->delete($this->dir.'/'.$sessionId);
    }

    /**
     * @inheritDoc
     */
    public function gc($maxLife)
    {
        $fileSystem = $this->fileSystem;
        Explorer::find()->in($this->dir)->date('now - '.$maxLife.' seconds', '<=')->map(function ($key, $fileInfo) use ($fileSystem) {
            /** @var \SplFileInfo $fileInfo */
            $fileSystem->delete($fileInfo->getRealPath());
        });
    }

}