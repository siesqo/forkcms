<?php

namespace Backend\Modules\Policies\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PoliciesExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->getLoader($container)->load('services.yml');
    }

    private function getLoader(ContainerBuilder $container): YamlFileLoader
    {
        return new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
    }
}
