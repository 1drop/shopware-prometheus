<?php

use OdPrometheus\Metrics\PrometheusMetricInterface;
use Prometheus\RenderTextFormat;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Hans Hoechtl <hhoechtl@1drop.de>
 *  All rights reserved
 ***************************************************************/

class Shopware_Controllers_Api_Metrics extends Shopware_Controllers_Api_Rest
{

    /**
     * @var \Prometheus\CollectorRegistry
     */
    protected $registry = null;

    /**
     * Initialize registry through service container
     */
    public function init()
    {
        $this->registry = Shopware()->Container()->get('od_prometheus.registry');
    }

    /**
     * Execute all registered metrics to fill the registry
     */
    public function indexAction()
    {
        /** @var array $registeredMetricServices */
        $registeredMetricServices = Shopware()->Container()->getParameter('od_prometheus.metrics');
        foreach ($registeredMetricServices as $registeredMetricService) {
            /** @var PrometheusMetricInterface $metric */
            $metric = Shopware()->Container()->get($registeredMetricService);
            $metric->collectMetric();
        }
    }

    /**
     * Render the registry as Prometheus text and output it
     */
    public function postDispatch()
    {
        $renderer = new RenderTextFormat();
        $data = $renderer->render($this->registry->getMetricFamilySamples());
        $this->Response()->setHeader('Content-type', RenderTextFormat::MIME_TYPE, true);
        $this->Response()->setBody($data);
    }

}
