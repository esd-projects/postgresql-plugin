# postgresql-plugin

## 插件安装

```
composer require esd/postgresql-plugin
```



## 插件用法

PostgreSQL插件的用法，力求与MySQL用法一样。如果你已熟用过 esd/mysql-plugin，可以忽略以下内容。



## 1. 启用插件

src/Application.php

```php
<?php
namespace app;
use ESD\Go\GoApplication;
use ESD\Plugins\Postgresql\PostgresqlPlugin;

class Application
{
    /**
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Exception
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    public static function main()
    {
        $application = new GoApplication();
        $application->addPlug(new PostgresqlPlugin());

        $application->run();
    }
}

```



## 2. 使用插件

有两种方法在对象中使用。

### 方法1

使用 use trait，然后通过 $this->postgresql() 获取数据库连接。

```php
use GetPostgresql;
```

```php
public function test()
{
    $this->postgresql()->query("select * from pg_stat_activity");
    return $res;
}
```

如果需要切换 postgresql配置为test，可按照如下方法修改。

```php
public function test()
{
    $this->postgresql('test')->query("select * from pg_stat_activity");
    return $res;
}
```



### 方法2

在对象中进行注入postgresql对象

```php
use DI\Annotation\Inject;

/**
 * @Inject()
 * @var \ESD\Plugins\Postgresql\PostgresDb
 */
protected $postgresql;

public function test(){
	$res = $this->postgresql->query("select * from pg_stat_activity");
	return $res;
}
```

**通过注入的方法，目前还不支持切换 postgresql 连接配置。**



## PostgreSQL 对象的使用方法

数据表例子：

```sql
CREATE TABLE p_customer (
"user_name" varchar(200),
"contact" jsonb
)
```

| user_name | contact                                                      |
| --------- | ------------------------------------------------------------ |
| 张三      | {"QQ": 120012, "tel": 6768456, "phone": 15838381234, "Wechat": "风的季节"} |
| 李四      | {"tel": 8661235545}                                          |



### Insert Query

```php
$res = $this->postgresql()->insert("p_customer", [
            "user_name" => "奥里",
            "contact" => json_encode([
                "QQ" => "123456",
                "Wechat" => "黑暗森林"
            ], JSON_UNESCAPED_UNICODE)
        ]);
```



### Select Query 

```php
$res = $this->postgresql()
            ->where("contact->>'phone'", '15838381234')
			->get("p_customer");
```



### Update Query

```sql
$res = $this->postgresql()
            ->where("contact->>'phone'", '15838381234')
            ->update("p_customer", [
                "user_name" => "钱三强"
            ]);
```



### Delete Query

```php
$res = $this->postgresql()
            ->where("contact->>'phone'", '15838381234')
            ->delete("p_customer");
```



### Ordering method

```php
$res = $this->postgresql()
			->orderByd("contact->>'QQ'", "ASC")
            ->get("p_customer");
```



### Grouping method

```php
$res = $this->postgresql()
            ->groupBy("contact->>'QQ'")
            ->get("p_customer");
```



### JOIN method

```php
$res = $this->postgresql()
            ->join("p_customer_qq cq", "c.contact->>'QQ' = cq.qq_number", "LEFT")
            ->where("c.contact->>'phone'", '15838381234')
            ->get("p_customer c", NULL, "c.*, cq.qq_avator");
```



### Has method

A convenient function that returns TRUE if exists at least an element that satisfy the where condition specified calling the "where" method before this one.

```php
$res = $this->postgresql()
            ->where("contact->>'phone'", '15838381234')
            ->has("p_customer");
```



### Helper methods

Get last executed SQL query

```php
$sql = $this->postgresql()->getLastQuery();
```



### Check if table exists

```php
$exist = $this->postgresql()->tableExists('p_customer');
```



### Transaction helpers

```php
$db = $this->postgresql();

$db->startTransaction();
...
if (!$db->insert ('myTable', $insertData)) {
    //Error while saving, cancel new record
    $db->rollback();
} else {
    //OK
    $db->commit();
}
```



### Error helpers

```php
$error = $this->postgresql()->getLastError();
```



### Pagination

```php
$db = $this->postgresql();
$page = 1;
// set page limit to 2 results per page. 20 by default
$db->pageLimit = 2;
$res = $db->paginate("p_customer", $page);
```



**loadData、XML、loadXML、insertMulti、subQuery 等MySQL插件的功能，暂时没有实现**。



### Running raw SQL queries

```php
$res = $this->postgresql()
            ->query('SELECT * from p_customer where contact->>'phone' = ?', Array('15838381234'));
```



