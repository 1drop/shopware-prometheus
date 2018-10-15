<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Hans Hoechtl <hhoechtl@1drop.de>
 *  All rights reserved
 ***************************************************************/

namespace OdPrometheus\Metrics;


interface PrometheusMetricInterface
{

    /**
     * Collect the metric data and add them to the registry
     * service od_prometheus.registry
     *
     * @return void
     */
    public function collectMetric();

}
