# OdsPrometheus plugin for Shopware

## What is Prometheus
[Prometheus](https://prometheus.io) is a modern monitoring platform. You can collect metrics through the HTTP protocol using
a simple pull mechanism (combinable with authentication, etc.).

![Detail](https://prometheus.io/assets/architecture.png)

Prometheus can:

* collect metrics
* store metrics as time series (internal db or Influx)
* expose metrics to Grafana
* do alerting

## What this plugin does

This plugin adds an API endpoint `/api/metrics` which exposes Prometheus metrics containing:

* List of cronjobs with last execution date and expected execution interval
* Time since last order
* Time since last user registered

## Exposed metrics example

```text
# HELP shopware_cronjob_end Unix timestamp of the crons end value
# TYPE shopware_cronjob_end counter
shopware_cronjob_end{cron="Artikelbewertung per eMail"} 1536748562
shopware_cronjob_end{cron="Aufräumen"} 1536748501
shopware_cronjob_end{cron="Basket Signature cleanup"} 1536820501
shopware_cronjob_end{cron="Customer Stream refresh"} 1536825620
shopware_cronjob_end{cron="Geburtstagsgruß"} 1536788582
shopware_cronjob_end{cron="HTTP Cache löschen"} 1536800401
# HELP shopware_cronjob_next Unix timestamp of the crons next value
# TYPE shopware_cronjob_next counter
shopware_cronjob_next{cron="Artikelbewertung per eMail"} 1536834918
shopware_cronjob_next{cron="Aufräumen"} 1536834878
shopware_cronjob_next{cron="Basket Signature cleanup"} 1536906853
shopware_cronjob_next{cron="Customer Stream refresh"} 1536832800
shopware_cronjob_next{cron="Geburtstagsgruß"} 1536874978
shopware_cronjob_next{cron="HTTP Cache löschen"} 1536886800
# HELP shopware_cronjob_start Unix timestamp of the crons start value
# TYPE shopware_cronjob_start counter
shopware_cronjob_start{cron="Artikelbewertung per eMail"} 1536748562
shopware_cronjob_start{cron="Aufräumen"} 1536748501
shopware_cronjob_start{cron="Basket Signature cleanup"} 1536820501
shopware_cronjob_start{cron="Customer Stream refresh"} 1536825602
shopware_cronjob_start{cron="Geburtstagsgruß"} 1536788582
shopware_cronjob_start{cron="HTTP Cache löschen"} 1536800401
# HELP shopware_cronjobs_noOfActiveCrons Get the number of cronjobs marked as active in the database
# TYPE shopware_cronjobs_noOfActiveCrons gauge
shopware_cronjobs_noOfActiveCrons 31
```

## Example Prometheus Scrape config

```yaml
job_name: Shopware
scrape_interval: 60s
scrape_timeout: 5s
metrics_path: /api/metrics
scheme: https
basic_auth:
    username: {SHOPWARE_API_USER}
    password: {SHOPWARE_API_KEY}
static_configs:
    - target: ['my.shop.de']
```


## Extensibility

To add your custom metric you can write or own plugin and register a new metric e.g. like that:

Build a new service that registers the tag `ods_prometheus.metric` and use whatever other service you
need to collect your data (e.g. database):

```xml
<service id="my_plugin.metrics.counter" class="MyPlugin\Metrics\Counter">
    <argument type="service" id="ods_prometheus.registry"/>
    <argument type="service" id="db"/>
    <tag name="ods_prometheus.metric"/>
</service>
```

Write your metric service that implements `OdsPrometheus\Metrics\PrometheusMetricInterface` that registers
counters, histograms, etc. (see https://github.com/Jimdo/prometheus_client_php for documentation and examples):

```php
<?php
namespace MyPlugin\Metrics;

use OdsPrometheus\Metrics\PrometheusMetricInterface;
use Prometheus\CollectorRegistry;

class Counter implements PrometheusMetricInterface
{
    /**
     * @var CollectorRegistry
     */
    protected $registry;
    /**
     * @var \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected $db;

    /**
     * Counter constructor.
     *
     * @param CollectorRegistry                        $registry
     * @param \Enlight_Components_Db_Adapter_Pdo_Mysql $db
     */
    public function __construct(CollectorRegistry $registry, \Enlight_Components_Db_Adapter_Pdo_Mysql $db)
    {
        $this->registry = $registry;
        $this->db = $db;
    }

    /**
     * Collect the metric data and add them to the registry
     * service ods_prometheus.registry
     *
     * @return void
     * @throws \Prometheus\Exception\MetricsRegistrationException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function collectMetric()
    {
        $noOfUsers = $this->db->executeQuery('SELECT COUNT(*) FROM s_user')->fetchColumn();
        // Note: always use shopware as namespace, so ops can distinguish the namespace properly
        $counter = $this->registry->registerCounter('shopware', 'noOfUsers', 'Number of users in database');
        $counter->incBy($noOfUsers);
    }
}
```

## Installation

### Git Version
* Checkout plugin in `/custom/plugins/OdsPrometheus`
* Install the plugin with the "Plugin Manager"
* Configure the plugin

### Shopware plugin store

This plugin will be available shortly in the Shopware plugin store.

## Authors

* Hans Höchtl <hhoechtl[at]1drop.de>

## TODO

* Implement optional push gateway
* Make storage configurable
