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

namespace Core\Contracts;


interface CollectionContract
{

    /**
     * Collection Object Constructor
     *
     * @param array $fields
     * @param \MongoDB|null $db
     * @throws \ErrorException
     */
    public function __construct(array $fields = null, \MongoDB $db = null);

    /**
     * Returns all collection object that match the given query(condition, etc.)
     *
     * @param array $condition
     * @param null $orderBy
     * @param null $limit
     * @return array
     * @throws \ErrorException
     */
    public static function findAll(array $condition = [], $orderBy = null, $limit = null);

    /**
     * Returns an object matching given parameters(/condition)
     *
     * @param array|null $parameters
     * @return array
     * @throws \ErrorException
     */
    public static function find(array $parameters = null);

    /**
     * Finds only one collection item
     *
     * @param array $condition
     * @return array
     * @throws \ErrorException
     */
    public static function findOne(array $condition = []);

    /**
     * Gets a count of Collection result of a query
     *
     * @param array|null $condition
     * @return int
     * @throws \ErrorException
     */
    public static function getCount(array $condition = null);

    /**
     * Used to perform Mongo aggregate actions on database
     *
     * @param array $pipeline
     * @return array
     * @throws \ErrorException
     */
    public static function aggregate(array $pipeline);

    /**
     * Gets a distinct result matching the given condition
     *
     * @param array $condition
     * @return array|bool
     * @throws \ErrorException
     */
    public static function distinct(array $condition);

    /**
     * Save (write) to mongo database
     *
     * @throws \ErrorException
     */
    public function save();
}