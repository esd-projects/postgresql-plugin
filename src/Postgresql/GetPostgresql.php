<?php
namespace ESD\Plugins\Postgresql;

use ESD\BaseServer\Server\Server;

trait GetPostgresql
{
    /**
     * @param string $name
     * @return \ESD\Plugins\Postgresql\PostgresDb
     * @throws \ESD\BaseServer\Exception
     */
    public function postgresql($name = "default")
    {
        $db = getContextValue("PostgresDb:$name");
        if ($db == null) {
            $postgresqlPool = getDeepContextValueByClassName(PostgresqlManyPool::class);
            if ($postgresqlPool instanceof PostgresqlManyPool) {
                $db = $postgresqlPool->getPool($name)->db();
                return $db;
            } else {
                throw new PostgresqlException("没有找到名为{$name}的postgresql连接池");
            }
        } else {
            return $db;
        }
    }
}