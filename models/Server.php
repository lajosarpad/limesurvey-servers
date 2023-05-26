<?php

namespace app\Models;

use \yii\base\Model;
use \Exception;
use app\models\Plugin;
use app\models\Stack;

class Server extends Model
{

    public const nothing = '';
    public const CentOS = 'CentOS';
    public const RedHat = 'RedHat';
    public const Ubuntu = 'Ubuntu';
    /**
     * Supported operating systems
     * 
     * @var array
     */
    protected static $operatingSystems = [
        self::nothing,
        self::CentOS,
        self::RedHat,
        self::Ubuntu
    ];

    /**
     * Gets the serialized OS array
     * 
     * @return string
     */
    public static function getSupportedOperatingSystems()
    {
        return json_encode(self::$operatingSystems, true);
    }

    /**
     * the operating system of the server
     * 
     * @var string
     */
    protected $os;

    /**
     * gets the operating system of the server
     * 
     * @return string
     */
    public function getOS()
    {
        return $this->os;
    }

    /**
     * sets the operating system of the server
     * 
     * @param string $os
     * 
     * @return Server
     */
    public function setOS(string $os)
    {
        if (array_search($os, self::$operatingSystems) === false) throw new Exception('Unsupported operating system for server');
        $this->os = $os;
        return $this;
    }

    /**
     * the storage size of the server
     * 
     * @var int
     */
    protected $storageSize;

    /**
     * gets the storage size of the server
     * 
     * @return int
     */
    public function getStorageSize()
    {
        return $this->storageSize;
    }

    /**
     * sets the storage size of the server
     * 
     * @param int $storageSize
     * 
     * @return Server
     */
    public function setStorageSize(int $storageSize)
    {
        $this->storageSize = $storageSize;
        return $this;
    }

    /**
     * private constructor
     * 
     * @param int $storageSize
     */
    private function __construct(int $storageSize, $os)
    {
        $this->setStorageSize($storageSize);
        if ($os !== null) $this->setOS($os);
    }

    /**
     * creates a server
     * 
     * @param int $storageSize
     * 
     * @return Server
     */
    public static function createServer(int $storageSize, string $os = null) {
        return new Server($storageSize, $os);
    }

    /**
     * gets friendly array
     * 
     * @return array
     */
    public function getFriendlyArray()
    {
        return [
            'OS' => $this->getOS(),
            'storageSize' => $this->getStorageSize()
        ];
    }

    /**
     * @var int
     */
    protected $occupied;

    /**
     * gets the occupied space
     * 
     * @return int
     */
    public function getOccupied()
    {
        return $this->occupied;
    }

    /**
     * stores a certain amount of space
     * 
     * @param int
     * 
     * @return Server
     */
    public function store($size)
    {
        $this->occupied += $size;
        return $this;
    }

    /**
     * releases a certain amount of space
     * 
     * @param int
     * 
     * @return Server
     */
    public function release($size)
    {
        $this->occupied -= $size;
        return $this;
    }

    /**
     * determines whether a plugin fits into a server according to current plan
     * 
     * @param Plugin $plugin
     * 
     * @return boolean
     */
    public function fits(Plugin $plugin)
    {
        return $this->getStorageSize() - $this->getOccupied() >= $plugin->getSize();
    }

    /**
     * @var array
     */
    protected $plugins = [];

    /**
     * Adds a plugin to the server
     * 
     * @param Plugin $plugin
     * 
     * @return Server
     */
    public function add(Plugin $plugin)
    {
        $this->plugins[]=$plugin;
        $this->store($plugin->getSize());
        return $this;
    }

    /**
     * Pops a plugin
     * 
     * @return Plugin
     */
    public function popPlugin()
    {
        $plugin = array_pop($this->plugins);
        $this->release($plugin->getSize());
        return $plugin;
    }

    public static function findBestDistribution($rawServers, $rawPlugins)
    {
        $servers = [];
        $plugins = [];
        foreach($rawServers as $rawServer) $servers[]=self::createServer($rawServer['storageSize'], $rawServer['OS']);
        foreach($rawPlugins as $rawPlugin) $plugins[]=Plugin::createPlugin($rawPlugin['name'], $rawPlugin['versions'], $rawPlugin['operatingSystems'], $rawPlugin['size']);

        $serversCapacities = [];
        $maximumNeededSpace = [];
        $serversOSs = [];
        $serverIndexes = [];
        $osKeys = [self::nothing, self::CentOS, self::RedHat, self::Ubuntu];
        foreach ($osKeys as $key) {
            $serversCapacities[$key] = 0;
            $serversOSs[$key] = [];
            $maximumNeededSpace[$key] = 0;
            $serverIndexes[$key] = 0;
        }
        foreach ($servers as $server) {
            $serversCapacities[$server->getOS()] += $server->getStorageSize();
            $serversOSs[$server->getOS()][] = $server;
        }
        foreach ($plugins as $plugin) {
            foreach ($plugin->getSupportedOS() as $os) {
                if (!isset($maximumNeededSpace[$os])) $maximumNeededSpace[$os] = 0;
                $maximumNeededSpace[$os] += $plugin->getSize();
            }
        }
        usort($plugins, function($a, $b) {
            count($b->getSupportedOS()) - count($a->getSupportedOS());
        });

        for ($pluginIndex = 0; $pluginIndex < count($plugins); $pluginIndex++) {
            $plugin = $plugins[$pluginIndex];
            $operatingSystems = $plugin->getSupportedOS();
            $bestOSIndex = 0;
            for ($index = 1; $index < count($operatingSystems); $index++) {
                if ($serversCapacities[$operatingSystems[$index]] > $serversCapacities[$operatingSystems[$bestOSIndex]]) {
                    $bestOSIndex = $index;
                }
            }
            $bestOS = $operatingSystems[$bestOSIndex];
            while (($serverIndexes[$bestOS] < count($serversOSs[$bestOS])) && ((!$serversOSs[$bestOS][$serverIndexes[$bestOS]]->fits($plugin)))) $serverIndexes[$bestOS]++;
            if ($serverIndexes[$bestOS] >= count($serversOSs[$bestOS])) {
                while (($serverIndexes[self::nothing] < count($serversOSs[self::nothing])) && ((!$serversOSs[self::nothing][$serverIndexes[self::nothing]]->fits($plugin)))) $serverIndexes[self::nothing]++;
                if ($serverIndexes[self::nothing] < count($serversOSs[self::nothing])) {
                    $maxOSNeededSpaceIndex = 0;
                    for ($index = 1; $index < count($operatingSystems); $index++) {
                        if ($maximumNeededSpace[$operatingSystems[$maxOSNeededSpaceIndex]] < $maximumNeededSpace[$operatingSystems[$index]]) {
                            $maxOSNeededSpaceIndex = $index;
                        }
                    }
                    $serversOSs[self::nothing][$serverIndexes[self::nothing]]->setOS($operatingSystems[$maxOSNeededSpaceIndex]);
                    $serversCapacities[$operatingSystems[$maxOSNeededSpaceIndex]] += $serversOSs[self::nothing][$serverIndexes[self::nothing]]->getStorageSize();
                    $serversOSs[$operatingSystems[$maxOSNeededSpaceIndex]][]=$serversOSs[self::nothing][$serverIndexes[self::nothing]++];
                    $pluginIndex--;
                }
            } else {
                $serversCapacities[$bestOS] -= $plugin->getSize();
                $serversOSs[$bestOS][$serverIndexes[$bestOS]]->add($plugin);
                foreach ($operatingSystems as $os) {
                    $maximumNeededSpace[$os] -= $plugin->getSize();
                }
            }
        }

        $friendlyServers = [];
        foreach ($servers as $server) {
            $srv = $server->getFriendlyArray();
            $srv['plugins'] = [];
            foreach ($server->plugins as $plugin) {
                $srv['plugins'][]=$plugin->getFriendlyArray();
            }
            $friendlyServers[]=$srv;
        }

        return $friendlyServers;
    }
}