<?php
namespace ESD\Plugins\Postgresql;

trait GetPostgresql
{
    /**
     * @param string $name
     * @return \ESD\Plugins\Postgresql\PostgresDb
     * @throws \ESD\Plugins\Postgresql\PostgresqlException
     */
    public function postgresql($name = "default")
    {
        $db = getContextValue("PostgresDb:$name");
        if ($db == null) {
            /** @var PostgresqlManyPool $postgresqlPool */
            $postgresqlPool = getDeepContextValueByClassName(PostgresqlManyPool::class);
            $pool = $postgresqlPool->getPool($name);
            if ($pool == null) {
                throw new PostgresqlException("No Postgresql connection pool named {$name} was found");
            }
            return $pool->db();
        } else {
            return $db;
        }
    }
}