<?php

namespace ESD\Plugins\Postgresql;


use SeinopSys\PostgresDb;

class PostgresqlManyPool
{
    protected $poolList = [];

    /**
     * 获取连接池
     * @param $name
     * @return PostgresqlPool|null
     */
    public function getPool($name = "default")
    {
        return $this->poolList[$name] ?? null;
    }

    /**
     * 添加连接池
     * @param PostgresqlPool $postgresqlPool
     */
    public function addPool(PostgresqlPool $postgresqlPool)
    {
        $this->poolList[$postgresqlPool->getPostgresqlConfig()->getName()] = $postgresqlPool;
    }

    /**
     * @return PostgresDb
     * @throws PostgresqlException
     * @throws \ESD\BaseServer\Exception
     */
    public function db(): PostgresDb
    {
        $default = $this->getPool();
        if ($default == null) {
            throw new PostgresqlException("没有设置默认的postgresql");
        }
        return $this->getPool()->db();
    }
}