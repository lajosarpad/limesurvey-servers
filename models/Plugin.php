<?php

namespace app\Models;

use \yii\base\BaseObject;

class Plugin extends BaseObject
{

    public const v_1_2 = 'v.1.2';
    public const v_1_3 = 'v.1.3';
    public const v_2 = 'v.2.0';
    public const latest = 'latest';
    
    /**
     * supported versions
     * 
     * @var array
     */
    protected static $versions = [
        self::v_1_2,
        self::v_1_3,
        self::v_2,
        self::latest
    ];

    /**
     * Gets the serialized version array
     * 
     * @return string
     */
    public static function getSupportedVersionsJSON()
    {
        return json_encode(self::$versions, true);
    }

    /**
     * @var string
     */
    protected $name;

    /**
     * gets the name of the plugin
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * sets the name of the plugin
     * 
     * @param string $name
     * 
     * @return Plugin
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @var array
     */
    protected $supportedVersions = [];

    /**
     * returns the supported versions
     * 
     * @return array
     */
    public function getSupportedVersions()
    {
        return $this->supportedVersions;
    }

    /**
     * sets the supported versions
     * 
     * @param array $supportedVersions
     * 
     * @return Plugin
     */
    public function setSupportedVersions(array $supportedVersions)
    {
        $this->supportedVersions = $supportedVersions;
        return $this;
    }

    /**
     * @var array
     */
    protected $supportedOS;

    /**
     * returns the supported operating systems
     * 
     * @return array
     */
    public function getSupportedOS()
    {
        return $this->supportedOS;
    }

    /**
     * sets the supported operating systems
     * 
     * @param array $supportedOS
     * 
     * @return Plugin
     */
    public function setSupportedOS(array $supportedOS)
    {
        $this->supportedOS = $supportedOS;
        return $this;
    }

    /**
     * @var int
     */
    protected $size;

    /**
     * returns the size of the plugin
     * 
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * returns the size of the plugin
     * 
     * @param int $size
     * 
     * @return Plugin
     */
    public function setSize(int $size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * private constructor
     * 
     * @param string $name
     * @param array $supportedVersions
     * @param array $supportedOS
     * @param int $size
     */
    private function __construct(string $name, array $supportedVersions, array $supportedOS, int $size)
    {
        $this->setName($name)
             ->setSupportedVersions($supportedVersions)
             ->setSupportedOS($supportedOS)
             ->setSize($size)
        ;
    }

    /**
     * creates a plugin
     * 
     * @param string $name
     * @param array $supportedVersions
     * @param array $supportedOS
     * @param int $size
     * 
     * @return Plugin
     */
    public static function createPlugin(string $name, array $supportedVersions, array $supportedOS, int $size) {
        return new Plugin($name, $supportedVersions, $supportedOS, $size);
    }

    /**
     * gets friendly array
     * 
     * @return array
     */
    public function getFriendlyArray()
    {
        return [
            'name' => $this->getName(),
            'versions' => $this->getSupportedVersions(),
            'operatingSystems' => $this->getSupportedOS(),
            'size' => $this->getSize()
        ];
    }

}