<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;
use RocketTheme\Toolbox\File\File;
use Symfony\Component\Yaml\Yaml;

/**
 * Class TestpluginPlugin
 * @package Grav\Plugin
 */
class TestpluginPlugin extends Plugin
{
    protected $myVar;
    protected $myVarCacheId;

    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin for an authenciated user
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        if ( ! $this->grav['user']->get('authenticated') ) return; // if not authenticated, nothing happens

        // Enable the events we are interested in
        $this->enable([
            'onTwigExtensions' => ['onTwigExtensions',0],
            'onFormProcessed' => ['onFormProcessed',0]
        ]);

        # set up cache_enable
        $cache = $this->grav['cache'];
        //init cache id
        $this->myVarCacheId = md5($this->config->get(plugins.testplugin.varname) . $cache->getKey() );

        $this->grav['debugger']->addMessage('init data dir' . DATA_DIR);
    }

    public function onFormProcessed(Event $event) {
       $action = $event['action'];
       switch ( $action ) {
          case 'myplugin':
                $form = $event['form'];
                $this->myVar = $form->value()->toArray();
                // Save
                $path = $this->getStoragePath();
$this->grav['debugger']->addMessage('formProc: path='.$path);
                $datafh = File::instance($path);
                $datafh->save(Yaml::dump($this->myVar));
                //clear cache
                $this->grav['cache']->delete($this->myVarCacheId);
                // make available immediately
                $this->grav['debugger']->addMessage('varname=');$this->grav['debugger']->addMessage($this->config->get(plugins.testplugin.varname));
                $this->grav['twig']->twig_vars[$this->config->get(plugins.testplugin.varname)] = $this->myVar;
                break;
        }
    }

    public function onTwigExtensions() {
        $cache = $this->grav['cache'];
        //search in cache, returns false if not in cache
        $this->myVar = $cache->fetch($this->myVarCacheId);
        $this->grav['debugger']->addMessage('twigex start, myVar:');
        $this->grav['debugger']->addMessage($this->myVar);
        if (! $this->myVar ) {
$this->grav['debugger']->addMessage('twigex: no cache');
            // if not in cache, then look in persistent storage
            $path = $this->getStoragePath();
            $datafh = File::instance($path);
            $datafh->lock();
$this->grav['debugger']->addMessage('twigex: filename = '.$path);
            if ( file_exists($path) ) {
$this->grav['debugger']->addMessage('twigex: file exists');
$this->grav['debugger']->addMessage('twigex: content is: ' . $datafh->content() );
                $this->myVar = Yaml::parse($datafh->content());
                if ( $this->myVar === null ) {
    $this->grav['debugger']->addMessage('no Yaml');
                    $this->myVar = array();
                }
            } else {
$this->grav['debugger']->addMessage('no file');
                $this->myVar = array();
                // Save
                $datafh->save(Yaml::dump($this->myVar));
            }
            $datafh->free();
            $cache->save($this->myVarCacheId, $this->myVar);
        } else {
$this->grav['debugger']->addMessage('twigex: cache exists');
        }
$this->grav['debugger']->addMessage('twigex: '.$this->myVar);
        $this->grav['twig']->twig_vars[$this->config->get(plugins.testplugin.varname)] = $this->myVar;
    }

    private function getStoragePath() {
        $locator = $this->grav['locator'];
        $path = $locator->findResource('user://data/testplugin', true);
        $path .= DS . $this->grav['user']->username; // must be true because authenciated
        return $path;
    }
}
