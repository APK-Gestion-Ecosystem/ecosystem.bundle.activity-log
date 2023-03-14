<?php

namespace Ecosystem\ActivityLogBundle;

use Ecosystem\ActivityLogBundle\Service\ActivityLogService;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class EcosystemActivityLogBundle extends AbstractBundle
{
    public function loadExtension(
        array $config,
        ContainerConfigurator $containerConfigurator,
        ContainerBuilder $containerBuilder
    ): void {
        $containerConfigurator->import('../config/services.yaml');

        $containerConfigurator->services()->get(ActivityLogService::class)->arg(0, $config['arn']);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }
}
