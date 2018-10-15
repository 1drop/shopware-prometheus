<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Hans Hoechtl <hhoechtl@1drop.de>
 *  All rights reserved
 ***************************************************************/

namespace OdPrometheus\Subscriber;


use Enlight\Event\SubscriberInterface;

class Library implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginPath;

    /**
     * Library constructor.
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
            'Enlight_Controller_Front_StartDispatch' => 'onStartDispatch',
            'Shopware_Console_Add_Command' => 'onStartDispatch',
        ];
    }

    /**
     * Use composer library
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onStartDispatch(\Enlight_Event_EventArgs $args)
    {
        if (file_exists($this->pluginPath . '/vendor/autoload.php')) {
            require_once $this->pluginPath . '/vendor/autoload.php';
        }
    }
}
