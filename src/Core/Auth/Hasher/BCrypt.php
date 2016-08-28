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


namespace Core\Auth\Hasher;

use Core\Contracts\Password;

class BCrypt implements Password
{

    /**
     * @param $value
     * @param array $options
     * @return bool|string
     */
    public function hash($value, array $options = [])
    {
        $algo = $options['algo'] || PASSWORD_BCRYPT;
        $algoTxt = $algo === PASSWORD_BCRYPT ? 'BCRYPT' : 'algo type:'.$algo;
        $options['cost'] = $options['cost'] ?: 13;

        $hash = password_hash($value, $algo, $options);

        if ($hash === false && defined("CRYPT_BLOWFISH")) {
            new \RuntimeException("Hashing failed with {$algoTxt} algorithm type.");
        } else {
            new \RuntimeException("BCRYPT hashing not supported.");
        }

        return $hash;
    }

    /**
     * @param $value
     * @param $hashedValue
     * @return bool
     */
    public function verify($value, $hashedValue)
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }

        return password_verify($value, $hashedValue);
    }

}