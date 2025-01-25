<?php

namespace RobinTheHood\PhpFramework\FileCreators;

class Dependency
{
    private $repositoriesPath = '';

    public function __construct(array $options)
    {
        $this->repositoriesPath = $options['repositoriesPath'];
    }

    public function getDependencies($objName)
    {
        $dependencies = [];
        $baseRepoFileNames = $this->getBaseRepoFileNames();
        foreach ($baseRepoFileNames as $baseRepoFileName) {
            $foreignObjName = $this->getObjNameFromFileName($baseRepoFileName);
            $structure = $this->getStructureFromBaseRepoFile($baseRepoFileName);
            $depndenciesFromStructure = $this->getDependenciesFromStructure($foreignObjName, $objName, $structure);
            $dependencies = array_merge($dependencies, $depndenciesFromStructure);
        }
        return $dependencies;
    }

    private function getDependenciesFromStructure($foreignObjName, $objName, $structure)
    {
        $result = [];
        foreach ($structure as $keyName => $definition) {
            if ($definition[1] == $objName) {
                $result[]  = [$foreignObjName, $keyName, $definition[0], $definition[1]];
            }
        }
        return $result;
    }

    private function getObjNameFromFileName($fileName)
    {
        $name = str_replace('BaseRepository.php', '', $fileName);
        return $name;
    }

    private function getStructureFromBaseRepoFile($fileName)
    {
        $classBaseRepositoryName = 'App\\Repositories\\Base\\' . str_replace('.php', '', $fileName);
        $objRepository = new $classBaseRepositoryName();
        return $objRepository->getStructure();
    }

    private function getBaseRepoFileNames($sort = 'ASC')
    {
        $path = $this->repositoriesPath . '/Base/';
        $files = scandir($path);
        if ($sort == 'DESC') {
            rsort($files);
        } else {
            sort($files);
        }
        $fileNames = array();
        foreach ($files as $file) {
            if (substr($file, 0, 1) != '.') {
                $fileNames[] = $file;
            }
        }
        return $fileNames;
    }
}
