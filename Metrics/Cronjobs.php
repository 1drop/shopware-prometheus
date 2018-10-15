<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Hans Hoechtl <hhoechtl@1drop.de>
 *  All rights reserved
 ***************************************************************/

namespace OdPrometheus\Metrics;


use Enlight_Components_Cron_Adapter;
use Prometheus\CollectorRegistry;

class Cronjobs implements PrometheusMetricInterface
{
    /**
     * @var CollectorRegistry
     */
    private $registry = null;
    /**
     * @var Enlight_Components_Cron_Adapter
     */
    protected $cronAdapter;

    /**
     * Cronjobs constructor.
     *
     * @param CollectorRegistry               $registry
     * @param Enlight_Components_Cron_Adapter $cronAdapter
     */
    public function __construct(
        CollectorRegistry $registry,
        Enlight_Components_Cron_Adapter $cronAdapter
    ) {
        $this->registry = $registry;
        $this->cronAdapter = $cronAdapter;
    }

    /**
     * @return void
     * @throws \Prometheus\Exception\MetricsRegistrationException
     */
    public function collectMetric()
    {
        $this->registerActiveCronjobsCounter();
        // TODO: add more metrics
    }

    /**
     * @throws \Prometheus\Exception\MetricsRegistrationException
     */
    private function registerActiveCronjobsCounter()
    {
        $cronjobCounter = $this->registry->registerCounter(
            'cronjobs',
            'noOfActiveCrons',
            'Get the number of cronjobs marked as active in the database'
        );
        $allCronJobs = $this->cronAdapter->getAllJobs();
        $activeCrons = 0;
        foreach ($allCronJobs as $cronJob) {
            if ($cronJob->isActive()) {
                $activeCrons++;
            }
        }
        $cronjobCounter->incBy($activeCrons);
    }
}
