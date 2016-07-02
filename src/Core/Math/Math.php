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

namespace Core\Math;

class Math {

    /**
     * The base.
     *
     * @var string
     */
    private static $base = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Convert a from a given base to base 10.
     *
     * @param  string  $value
     * @param  int     $b
     * @return int
     */
    public static function to_base_10($value, $b = 62)
    {
        $limit = strlen($value);
        $result = strpos(static::$base, $value[0]);

        for($i = 1; $i < $limit; $i++)
        {
            $result = $b * $result + strpos(static::$base, $value[$i]);
        }

        return $result;
    }

    /**
     * Convert from base 10 to another base.
     *
     * @param  int     $value
     * @param  int     $b
     * @return string
     */
    public static function to_base($value, $b = 62)
    {
        $r = $value  % $b;
        $result = static::$base[$r];
        $q = floor($value / $b);

        while ($q)
        {
            $r = $q % $b;
            $q = floor($q / $b);
            $result = static::$base[$r].$result;
        }

        return $result;
    }

}