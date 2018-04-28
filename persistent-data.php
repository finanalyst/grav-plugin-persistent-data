<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;
use RocketTheme\Toolbox\File\File;
use Symfony\Component\Yaml\Yaml;

/**
 * Class PersistentDataPlugin
 * @package Grav\Plugin
 */
class PersistentDataPlugin extends Plugin
{
    protected $userinfo;
    protected $userinfoCacheId;

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
        // Don't proceed unless user is authenticated
        if ( ! $this->grav['user']->get('authenticated') ) {
            return;
        }
        // Enable the events we are interested in
        $this->enable([
            'onTwigExtensions' => ['onTwigExtensions',0],
            'onFormProcessed' => ['onFormProcessed',0],
            'onUserLogout' => ['onUserLogout',0]
        ]);

        # set up cache
        $cache = $this->grav['cache'];
        //init cache id
        $this->userinfoCacheId = md5('persistent-data-userinfo' . $cache->getKey() );

        # verify data directory exists
        if (!file_exists(DATA_DIR . 'persistent' )) {
            mkdir(DATA_DIR . 'persistent' , 0775, true);
        }
    }

    public function onFormProcessed(Event $event) {
       $action = $event['action'];
       switch ( $action ) {
          case 'userinfo':
                $form = $event['form'];
                $params = $event['params'];
                $data = $form->value()->toArray();
                if (isset($params['update']) and $params['update']) {
                    $cache = $this->grav['cache'];
                    //search in cache, returns false if not in cache
                    $this->userinfo = $cache->fetch($this->userinfoCacheId);
                    if (! $this->userinfo ) {
                        // if not in cache, then look in persistent storage
                        $path = DATA_DIR . 'persistent' . DS . $this->grav['user']->username;
                        $datafh = File::instance($path);
                        if ( file_exists($path) ) {
                            $this->userinfo = Yaml::parse($datafh->content());
                            if ( $this->userinfo === null ) {
                                $this->userinfo = array();
                            }
                        } else {
                            $this->userinfo = array();
                            $datafh->save(Yaml::dump($this->userinfo));
                            chmod($path, 0666);
                        }
                    }
                    // only update fields set by the form
                    foreach ($data as $key => $val ) {
                        $this->userinfo[$key] = $val;
                    }
                } else { // overwrite existing data
                    $this->userinfo = $data;
                }
                // For onFormProcessed to be called, a user has to be authenticated,
                //  so username is set
                $path = DATA_DIR . 'persistent' . DS . $this->grav['user']->username;
                $datafh = File::instance($path);
                $datafh->save(Yaml::dump($this->userinfo));
                //clear cache
                $this->grav['cache']->delete($this->userinfoCacheId);
                // make available to Twig immediately
                $this->grav['twig']->twig_vars['userinfo'] = $this->userinfo;
                break;
        }
    }

    public function onTwigExtensions() {
        $cache = $this->grav['cache'];
        //search in cache, returns false if not in cache
        $this->userinfo = $cache->fetch($this->userinfoCacheId);
        if (! $this->userinfo ) {
            // if not in cache, then look in persistent storage
            $path = DATA_DIR . 'persistent' . DS . $this->grav['user']->username;
            $datafh = File::instance($path);
            if ( file_exists($path) ) {
                $this->userinfo = Yaml::parse($datafh->content());
                if ( $this->userinfo === null ) {
                    $this->userinfo = array();
                }
            } else {
                $this->userinfo = array();
                $datafh->save(Yaml::dump($this->userinfo));
                chmod($path, 0666);
            }
            $cache->save($this->userinfoCacheId, $this->userinfo);
        }
        $this->grav['twig']->twig_vars['userinfo'] = $this->userinfo;
    }

    public function onUserLogout() {
        if ($this->config->get('plugins.persistent-data.forget_on_logout')) {
            $path = DATA_DIR . 'persistent' . DS . $this->grav['user']->username;
            $datafh = File::instance($path);
            if ( file_exists($path) ) {
                $datafh->delete();
            }
        }
    }
}
