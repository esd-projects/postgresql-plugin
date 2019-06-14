<?php

namespace ESD\Plugins\Postgresql;


use ESD\Core\Channel\Channel;

class PostgresqlPool
{
    /**
     * @var Channel
     */
    protected $pool;
    /**
     * @var PostgresqlOneConfig
     */
    protected $postgresqlConfig;

    /**
     * PostgresqlPool constructor.
     * @param PostgresqlOneConfig $postgresqlConfig
     * @throws PostgresqlException
     */
    public function __construct(PostgresqlOneConfig $postgresqlConfig)
    {
        $this->postgresqlConfig = $postgresqlConfig;
        $config = $postgresqlConfig->buildConfig();
        $this->pool = DIGet(Channel::class, [$postgresqlConfig->getPoolMaxNumber()]);
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
     * @return PostgresqlOneConfig
     */
    public function getPostgresqlConfig(): PostgresqlOneConfig
    {
        return $this->postgresqlConfig;
    }

    /**
     * @param PostgresqlOneConfig $postgresqlConfig
     */
    public function setPostgresqlConfig(PostgresqlOneConfig $postgresqlConfig): void
    {
        $this->postgresqlConfig = $postgresqlConfig;
    }

}