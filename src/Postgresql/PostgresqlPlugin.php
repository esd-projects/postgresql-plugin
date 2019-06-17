<?php

namespace ESD\Plugins\Postgresql;

use Doctrine\Common\Annotations\AnnotationReader;
use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;
use ESD\Plugins\Aop\AopConfig;
use ESD\Plugins\Aop\AopPlugin;
use ESD\Plugins\Postgresql\Aspect\PostgresqlAspect;

class PostgresqlPlugin extends AbstractPlugin
{
    use GetLogger;
    /**
     * @var postgresqlConfig
     */
    protected $postgresqlConfig;

    public function __construct()
    {
        parent::__construct();
        $this->postgresqlConfig = new PostgresqlConfig();
        $this->postgresqlConfig->setPostgresqlConfigs([]);
        AnnotationReader::addGlobalIgnoredName('params');
        $this->atAfter(AopPlugin::class);
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Postrgresql";
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
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        //所有配置合併
        foreach ($this->postgresqlConfig as $config) {
            $config->merge();
        }
        $configs = Server::$instance->getConfigContext()->get(PostgresqlOneConfig::key, []);
        foreach ($configs as $key => $value) {
            $postgresqlOneConfig = new PostgresqlOneConfig("", "", "", "");
            $postgresqlOneConfig->setName($key);
            $this->postgresqlConfig->addPostgresqlOneConfig($postgresqlOneConfig->buildFromConfig($value));
        }

        $postgresDbProxy = new PostgresDbProxy();
        $this->setToDIContainer(PostgresDb::class, $postgresDbProxy);
        $this->setToDIContainer(PostgresDb::class, $postgresDbProxy);
        $this->setToDIContainer(PostgresqlConfig::class, $this->postgresqlConfig);
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
        $configs = Server::$instance->getConfigContext()->get(PostgresqlOneConfig::key, []);
        if (empty($configs)) {
            $this->warn("没有postgresql配置");
            return false;
        }
        foreach ($configs as $key => $value) {
            $postgresqlConfig = new PostgresqlOneConfig("", "", "", "");
            $postgresqlConfig->setName($key);
            $this->configList[$key] = $postgresqlConfig->buildFromConfig($value);
            $postgresqlPool = new PostgresqlPool($postgresqlConfig);
            $postgresqlManyPool->addPool($postgresqlPool);
            $this->debug("已添加名为 {$postgresqlConfig->getName()} 的postgresql连接池");
        }
        $context->add("postgresqlPool", $postgresqlManyPool);
        $this->setToDIContainer(PostgresqlManyPool::class, $postgresqlManyPool);
        $this->setToDIContainer(PostgresqlPool::class, $postgresqlManyPool->getPool());
        $this->ready();
    }

    /**
     * @return PostgresqlOneConfig[]
     */
    public function getConfigList(): array
    {
        return $this->postgresqlConfig->getPostgresqlConfigs();
    }

    /**
     * @param PostgresqlOneConfig[] $configList
     */
    public function setConfigList(array $configList): void
    {
        $this->postgresqlConfig->setPostgresqlConfigs($configList);
    }

    /**
     * @param PostgresqlOneConfig $postgresqlOneConfig
     */
    public function addConfigList(PostgresqlOneConfig $postgresqlOneConfig): void
    {
        $this->postgresqlConfig->addPostgresqlOneConfig($postgresqlOneConfig);
    }
}