<?php

namespace ESD\Plugins\Postgresql;


class PostgresqliDbProxy
{
    use GetPostgresql;
    public function __get($name)
    {
        return $this->postgresql()->$name;
    }

    public function __set($name, $value)
    {
        $this->postgresql()->$name = $value;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->postgresql(), $name], $arguments);
    }
}