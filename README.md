# OdPrometheus plugin for Shopware

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


## Extendability

To add your custom metric you can write or own plugin and register a new metric e.g. like that:

Build a new service that registers the tag `od_prometheus.metric` and use whatever other service you 
need to collect your data (e.g. database):

```xml
<service id="my_plugin.metrics.counter" class="MyPlugin\Metrics\Counter">
    <argument type="service" id="od_prometheus.registry"/>
    <argument type="service" id="db"/>
    <tag name="od_prometheus.metric"/>
</service>
```

Write your metric service that implements `OdPrometheus\Metrics\PrometheusMetricInterface` that registers
counters, histograms, etc. (see https://github.com/Jimdo/prometheus_client_php for documentation and examples):

```php
<?php
namespace MyPlugin\Metrics;

use OdPrometheus\Metrics\PrometheusMetricInterface;
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
     * service od_prometheus.registry
     *
     * @return void
     * @throws \Prometheus\Exception\MetricsRegistrationException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function collectMetric()
    {
        $noOfUsers = $this->db->executeQuery('SELECT COUNT(*) FROM s_user')->fetchColumn();
        $counter = $this->registry->registerCounter('user', 'noOfUsers', 'Number of users in database');
        $counter->incBy($noOfUsers);
    }
}
```

## Installation

### Git Version
* Checkout plugin in `/custom/plugins/OdPrometheus`
* Install the plugin with the "Plugin Manager"
* Configure the plugin

### Shopware plugin store

This plugin will be available shortly in the Shopware plugin store.

## Authors

* Hans Höchtl <hhoechtl[at]1drop.de>

## TODO

* Implement optional push gateway
* Make storage configurable
