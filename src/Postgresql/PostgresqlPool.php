<?php

namespace ESD\Plugins\Postgresql;


use ESD\BaseServer\Coroutine\Channel;

class PostgresqlPool
{
    /**
     * @var Channel
     */
    protected $pool;
    /**
     * @var PostgresqlConfig
     */
    protected $postgresqlConfig;

    /**
     * PostgresqlPool constructor.
     * @param PostgresqlConfig $postgresqlConfig
     * @throws PostgresqlException
     */
    public function __construct(PostgresqlConfig $postgresqlConfig)
    {
        $this->postgresqlConfig = $postgresqlConfig;
        $config = $postgresqlConfig->buildConfig();
        $this->pool = new Channel($postgresqlConfig->getPoolMaxNumber());
        for ($i = 0; $i < $postgresqlConfig->getPoolMaxNumber(); $i++) {
            $db = new PostgresDb($config['db'], $config['host'], $config['username'], $config['password']);
            $this->pool->push($db);
        }
    }

    /**
     * @return \ESD\Plugins\Postgresql\PostgresDb;
     * @throws \ESD\BaseServer\Exception
     */
    public function db(): PostgresDb
    {
        $db = getContextValue("PostgresDb:{$this->getPostgresqlConfig()->getName()}");
        if ($db == null) {
            $db = $this->pool->pop();
            defer(function () use ($db) {
                $this->pool->push($db);
            });
            setContextValue("PostgresDb:{$this->getPostgresqlConfig()->getName()}", $db);
        }
        return $db;
    }

    /**
     * @return PostgresqlConfig
     */
    public function getPostgresqlConfig(): PostgresqlConfig
    {
        return $this->postgresqlConfig;
    }

    /**
     * @param PostgresqlConfig $postgresqlConfig
     */
    public function setPostgresqlConfig(PostgresqlConfig $postgresqlConfig): void
    {
        $this->postgresqlConfig = $postgresqlConfig;
    }

}