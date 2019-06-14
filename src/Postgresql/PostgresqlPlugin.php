<?php

namespace ESD\Plugins\Postgresql;

use ESD\BaseServer\Plugins\Logger\GetLogger;
use ESD\BaseServer\Server\Context;
use ESD\BaseServer\Server\Plugin\AbstractPlugin;
use ESD\BaseServer\Server\PlugIn\PluginInterfaceManager;
use ESD\BaseServer\Server\Server;
use ESD\Plugins\Aop\AopConfig;
use ESD\Plugins\Aop\AopPlugin;
use ESD\Plugins\Postgresql\Aspect\PostgresqlAspect;

class PostgresqlPlugin extends AbstractPlugin
{
    use GetLogger;
    /**
     * @var PostgresqlConfig[]
     */
    protected $configList = [];

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Postrgresql";
    }

    public function __construct()
    {
        parent::__construct();
        $this->atAfter(AopPlugin::class);
    }

    public function init(Context $context)
    {
        parent::init($context);
        $aopConfig = Server::$instance->getContainer()->get(AopConfig::class);
        $aopConfig->addAspect(new PostgresqlAspect());
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \ESD\BaseServer\Exception
     * @throws \ReflectionException
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlug(new AopPlugin());
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @return mixed
     * @throws \ESD\BaseServer\Server\Exception\ConfigException
     */
    public function beforeServerStart(Context $context)
    {
        //所有配置合併
        foreach ($this->configList as $config) {
            $config->merge();
        }
        $postgresDbProxy = new PostgresqliDbProxy();
        $this->setToDIContainer(PostgresDb::class, $postgresDbProxy);
        $this->setToDIContainer(PostgresDb::class, $postgresDbProxy);
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @return mixed
     * @throws \ESD\BaseServer\Exception
     * @throws \ReflectionException
     */
    public function beforeProcessStart(Context $context)
    {
        $postgresqlManyPool = new PostgresqlManyPool();
        //重新获取配置
        $this->configList = [];
        $configs = Server::$instance->getConfigContext()->get(PostgresqlConfig::key, []);
        if (empty($configs)) {
            $this->warn("没有postgresql配置");
            return false;
        }
        foreach ($configs as $key => $value) {
            $postgresqlConfig = new PostgresqlConfig("", "", "", "");
            $postgresqlConfig->setName($key);
            $this->configList[$key] = $postgresqlConfig->buildFromConfig($value);
            $postgresqlPool = new PostgresqlPool($postgresqlConfig);
            $postgresqlManyPool->addPool($postgresqlPool);
            $this->debug("已添加名为 {$postgresqlConfig->getName()} 的postgresql连接池");
        }
        $context->add("postgresqlPool", $postgresqlManyPool);
        $this->setToDIContainer(postgresqlManyPool::class, $postgresqlManyPool);
        $this->setToDIContainer(postgresqlPool::class, $postgresqlManyPool->getPool());
        $this->ready();
    }

    /**
     * @return postgresqlConfig[]
     */
    public function getConfigList(): array
    {
        return $this->configList;
    }

    /**
     * @param postgresqlConfig[] $configList
     */
    public function setConfigList(array $configList): void
    {
        $this->configList = $configList;
    }

    /**
     * @param postgresqlConfig $mysqlConfig
     */
    public function addConfigList(postgresqlConfig $mysqlConfig): void
    {
        $this->configList[$mysqlConfig->getName()] = $mysqlConfig;
    }
}