<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Hans Hoechtl <hhoechtl@1drop.de>
 *  All rights reserved
 ***************************************************************/
namespace OdsPrometheus;

use OdsPrometheus\Compiler\MetricsCompilerPass;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OdsPrometheus extends Plugin
{
    /**
     * Cache must be cleared to recompile symfony services
     *
     * @param ActivateContext $context
     */
    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    /**
     * Collect all registered services by tag ods_prometheus.metric
     * and write them as container parameter array for later iteration.
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new MetricsCompilerPass());
    }
}
