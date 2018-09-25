<?php

namespace Engelsystem\Config;

use Engelsystem\Application;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Models\EventConfig;

class ConfigServiceProvider extends ServiceProvider
{
    /** @var EventConfig */
    protected $eventConfig;

    /**
     * @param Application $app
     * @param EventConfig $eventConfig
     */
    public function __construct(Application $app, EventConfig $eventConfig = null)
    {
        parent::__construct($app);

        $this->eventConfig = $eventConfig;
    }

    public function register()
    {
        $defaultConfigFile = $this->getConfigPath('config.default.php');
        $configFile = $this->getConfigPath('config.php');

        $config = $this->app->make(Config::class);
        $this->app->instance(Config::class, $config);
        $this->app->instance('config', $config);

        $config->set(require $defaultConfigFile);

        if (file_exists($configFile)) {
            $config->set(array_replace_recursive(
                $config->get(null),
                require $configFile
            ));
        }
    }

    public function boot()
    {
        if (
            !$this->eventConfig
            || !$this->eventConfig
                ->getConnection()
                ->getSchemaBuilder()
                ->hasTable($this->eventConfig->getTable())
        ) {
            return;
        }

        /** @var Config $config */
        $config = $this->app->get('config');
        /** @var EventConfig[] $values */
        $values = $this->eventConfig->newQuery()->get(['name', 'value']);

        foreach ($values as $option) {
            $data = $option->value;

            if (is_array($data) && $config->has($option->name)) {
                $data = array_replace_recursive(
                    $config->get($option->name),
                    $data
                );
            }

            $config->set($option->name, $data);
        }
    }

    /**
     * Get the config path
     *
     * @param string $path
     * @return string
     */
    protected function getConfigPath($path = ''): string
    {
        return config_path($path);
    }
}
