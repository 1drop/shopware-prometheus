<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Hans Hoechtl <hhoechtl@1drop.de>
 *  All rights reserved
 ***************************************************************/
namespace OdsPrometheus\Subscriber;

use Enlight\Event\SubscriberInterface;

class ApiController implements SubscriberInterface
{

    /**
     * @var string
     */
    private $pluginPath;

    /**
     * ApiController constructor.
     *
     * @param string $pluginPath
     */
    public function __construct(string $pluginPath)
    {
        $this->pluginPath = $pluginPath;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Api_Metrics' => 'onGetMetricsApiController',
        ];
    }

    /**
     * @param  \Enlight_Event_EventArgs $args
     * @return string
     * @noinspection PhpUnusedParameterInspection
     */
    public function onGetMetricsApiController(\Enlight_Event_EventArgs $args)
    {
        return $this->pluginPath . '/Controllers/Api/Metrics.php';
    }
}
