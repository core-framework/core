<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 20/12/15
 * Time: 2:34 PM
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
    public function __construct(array $fields = [], \MongoDB $db = null);

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