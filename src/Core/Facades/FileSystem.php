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


namespace Core\Facades;


use Core\Reactor\Facade;

/**
 * Class FileSystem
 * 
 * @method static string read($path, $lockFlag = LOCK_SH)
 * @method static string getContents($path, $lock = false, $flags = null)
 * @method static bool write($path, $content, $lock = false)
 * @method static bool prepend($path, $content, $flags = false)
 * @method static bool append($path, $content, $flags = false)
 * @method static bool exists($path)
 * @method static bool delete($path)
 * @method static bool move($path, $newPath)
 * @method static bool copy($path, $destination, $recursive = false)
 * @method static bool mkdir($path, $mode = 0755, $recursive = false, $force = false)
 * @method static mixed pathInfo($path, $flag = null)
 * @method static mixed basename($path)
 * @method static mixed dirname($path)
 * @method static mixed filename($path)
 * @method static mixed extension($path)
 * @method static int size($path)
 * @method static int lastModified($path)
 * @method static bool isDir($path)
 * @method static bool isFile($path)
 * @method static mixed type($path)
 * @method static mixed mime($path)
 * @method static bool isWritable($path)
 * @method static mixed find($pattern, $dir = '', $flags = 0)
 * @package Core\FileSystem
 */
class FileSystem extends Facade
{
    /**
     * @inheritDoc
     */
    protected static function getName()
    {
        return 'FileSystem';
    }

}