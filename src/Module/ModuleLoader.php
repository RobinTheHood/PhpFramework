<?php

namespace RobinTheHood\PhpFramework\Module;

use RobinTheHood\PhpFramework\App;
use RobinTheHood\PhpFramework\ArrayHelper;

class ModuleLoader
{
    private $rootPath;
    private $eventDispatcher;

    public function __construct($config)
    {
        $this->rootPath = $config['rootPath'];
    }

    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getConfig()
    {
        $config = App::getConfig();
        return ArrayHelper::getIfSet($config, 'modules', []);
    }

    public static function getModuleDescriptor($name)
    {
        $moduleDescriptors = self::getConfig();
        return ArrayHelper::getIfSet($moduleDescriptors, $name);
    }

    public function load()
    {
        $moduleDescriptors = self::getConfig();

        foreach ($moduleDescriptors as $moduleDescriptor) {
            $this->addSubcribers($moduleDescriptor);
        }
    }

    protected function addListeners($moduleDescriptor)
    {
        $classNames = $this->getListenerClassNames($moduleDescriptor);
        foreach ($classNames as $className) {
            $classNameWithNamespace = $this->getListenerClassNameWithNamespace($moduleDescriptor, $className);
        }
    }

    protected function addSubcribers($moduleDescriptor)
    {
        $classNames = $this->getSubcribersClassNames($moduleDescriptor);
        foreach ($classNames as $className) {
            $classNameWithNamespace = $this->getSubcriberClassNameWithNamespace($moduleDescriptor, $className);
            $subscriber = new $classNameWithNamespace();
            $this->eventDispatcher->addSubscriber($subscriber);
        }
    }

    protected function addListener($listener, $eventName, $method)
    {
        $listener = new $listener();
        $this->eventDispatcher->addListener($eventName, [$listener, $method]);
    }

    protected function getListenerClassNames($moduleDescriptor)
    {
        $fileNames = $this->getListenerFileNames($moduleDescriptor);
        return $this->filterClassNames($fileNames);
    }

    protected function getSubcribersClassNames($moduleDescriptor)
    {
        $fileNames = $this->getSubcribersFileNames($moduleDescriptor);
        return $this->filterClassNames($fileNames);
    }

    protected function filterClassNames($fileNames)
    {
        $classNames = [];
        foreach ($fileNames as $fileName) {
            $pathParts = pathinfo($fileName);
            $classNames[] = $pathParts['filename'];
        }
        return $classNames;
    }

    protected function getListenerFileNames($moduleDescriptor)
    {
        $path = $this->getListenerPath($moduleDescriptor);
        if (!is_dir($path)) {
            return [];
        }

        $fileNames = scandir($path);
        $filteredFileNames = $this->filterListenerFiles($fileNames);

        return $filteredFileNames;
    }

    protected function getSubcribersFileNames($moduleDescriptor)
    {
        $path = $this->getSubcriberPath($moduleDescriptor);

        if (!is_dir($path)) {
            return [];
        }

        $fileNames = scandir($path);
        $filteredFileNames = $this->filterSubcriberFiles($fileNames);

        return $filteredFileNames;
    }


    protected function filterPhpFiles($fileNames)
    {
        $filteredFileNames = [];
        foreach ($fileNames as $fileName) {
            if ($fileName == '.' || $fileName == '..') {
                continue;
            }

            $pathParts = pathinfo($fileName);
            if ($pathParts['extension'] != 'php') {
                continue;
            }

            $filteredFileNames[] = $fileName;
        }

        return $filteredFileNames;
    }

    protected function filterListenerFiles($fileNames)
    {
        return $this->filterPhpFiles($fileNames);
    }

    protected function filterSubcriberFiles($fileNames)
    {
        return $this->filterPhpFiles($fileNames);
    }

    protected function getListenerPath($moduleDescriptor)
    {
        return $this->rootPath . '/' . $moduleDescriptor['path'] . '/Listeners';
    }

    protected function getSubcriberPath($moduleDescriptor)
    {
        return $this->rootPath . '/' . $moduleDescriptor['path'] . '/Subcribers';
    }

    protected function getListenerClassNameWithNamespace($moduleDescriptor, $className)
    {
        return $moduleDescriptor['namespace'] . '\Listeners\\' . $className;
    }

    protected function getSubcriberClassNameWithNamespace($moduleDescriptor, $className)
    {
        return $moduleDescriptor['namespace'] . '\Subcribers\\' . $className;
    }
}
