<?php

declare(strict_types=1);

namespace EnjoysCMS\Module\RevSlider5;

use DI\DependencyException;
use DI\FactoryInterface;
use DI\NotFoundException;
use EnjoysCMS\Core\Components\Modules\ModuleConfig;

final class Config
{
    private ModuleConfig $config;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->config = $factory->make(ModuleConfig::class, ['moduleName' => 'enjoyscms/revslider5']);
        $this->config->strict(false);
    }

    public function all(): ModuleConfig
    {
        return $this->config;
    }

    public function get(string $key, $default = null): mixed
    {
        return $this->config->get($key, $default);
    }
}
