<?php

namespace ESD\Plugins\Postgresql;

class PostgresqlConfig
{
    /**
     * @var PostgresqlOneConfig[]
     */
    protected $postgresqlConfigs;

    /**
     * @return PostgresqlOneConfig[]
     */
    public function getPostgresqlConfigs(): array
    {
        return $this->postgresqlConfigs;
    }

    /**
     * @param PostgresqlOneConfig[] $postgresqlConfigs
     */
    public function setPostgresqlConfigs(array $mysqlConfigs): void
    {
        $this->postgresqlConfigs = $mysqlConfigs;
    }

    public function addPostgresqlOneConfig(PostgresqlOneConfig $buildFromConfig)
    {
        $this->postgresqlConfigs[$buildFromConfig->getName()] = $buildFromConfig;
    }
}