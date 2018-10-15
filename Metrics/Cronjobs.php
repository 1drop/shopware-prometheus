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
     * @throws \Prometheus\Exception\MetricsRegistrationException
     * @return void
     */
    public function collectMetric()
    {
        $this->registerActiveCronjobsCounter();
        $this->registerCronTimes();
    }

    /**
     * @throws \Prometheus\Exception\MetricsRegistrationException
     */
    private function registerActiveCronjobsCounter()
    {
        $cronJobGauge = $this->registry->registerGauge(
            'shopware',
            'cronjobs_noOfActiveCrons',
            'Get the number of cronjobs marked as active in the database'
        );
        $allCronJobs = $this->cronAdapter->getAllJobs();
        $activeCrons = 0;
        foreach ($allCronJobs as $cronJob) {
            if ($cronJob->isActive()) {
                ++$activeCrons;
            }
        }
        $cronJobGauge->set($activeCrons);
    }

    /**
     * @throws \Prometheus\Exception\MetricsRegistrationException
     */
    private function registerCronTimes()
    {
        $allCronJobs = $this->cronAdapter->getAllJobs();
        foreach ($allCronJobs as $cronJob) {
            $startRunCounter = $this->registry->getOrRegisterCounter(
                'shopware',
                'cronjob_start',
                'Unix timestamp of the crons start value',
                ['cron']
            );
            $startRunCounter->incBy(
                $cronJob->getStart() instanceof \Zend_Date ? $cronJob->getStart()->getTimestamp() : 0,
                [$cronJob->getName()]
            );
            $nextRunCounter = $this->registry->getOrRegisterCounter(
                'shopware',
                'cronjob_next',
                'Unix timestamp of the crons next value',
                ['cron']
            );
            $nextRunCounter->incBy(
                $cronJob->getNext() instanceof \Zend_Date ? $cronJob->getNext()->getTimestamp() : 0,
                [$cronJob->getName()]
            );
            $endRunCounter = $this->registry->getOrRegisterCounter(
                'shopware',
                'cronjob_end',
                'Unix timestamp of the crons end value',
                ['cron']
            );
            $endRunCounter->incBy(
                $cronJob->getEnd() instanceof \Zend_Date ? $cronJob->getEnd()->getTimestamp() : 0,
                [$cronJob->getName()]
            );
        }
    }
}
