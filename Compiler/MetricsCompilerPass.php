<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Hans Hoechtl <hhoechtl@1drop.de>
 *  All rights reserved
 ***************************************************************/
namespace OdPrometheus\Compiler;

use OdPrometheus\Metrics\PrometheusMetricInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MetricsCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param  ContainerBuilder     $container
     * @throws \ReflectionException
     */
    public function process(ContainerBuilder $container)
    {
        $metricServiceNames = [];
        $metricServices = $container->findTaggedServiceIds('od_prometheus.metric');
        foreach ($metricServices as $id => $metricService) {
            $def = $container->getDefinition($id);
            if (!$def->isPublic()) {
                throw new \InvalidArgumentException(sprintf('The service "%s" must be public as event subscribers are lazy-loaded.', $id));
            }
            if ($def->isAbstract()) {
                throw new \InvalidArgumentException(sprintf('The service "%s" must not be abstract as event subscribers are lazy-loaded.', $id));
            }
            $class = $container->getParameterBag()->resolveValue($def->getClass());
            $refClass = new \ReflectionClass($class);
            $interface = PrometheusMetricInterface::class;
            if (!$refClass->implementsInterface($interface)) {
                throw new \InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, $interface));
            }
            $metricServiceNames[] = $id;
        }
        $container->setParameter('od_prometheus.metrics', $metricServiceNames);
    }
}
