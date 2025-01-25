<?php

namespace RobinTheHood\PhpFramework\Scripts;

use RobinTheHood\Database\DatabaseType;
use RobinTheHood\Debug\Debug;
use RobinTheHood\NamingConvention\NamingConvention;
use RobinTheHood\PhpFramework\FileCreators\ModelFileCreator;
use RobinTheHood\PhpFramework\FileCreators\MigrationFileCreator;
use RobinTheHood\PhpFramework\FileCreators\ControllerFileCreator;
use RobinTheHood\PhpFramework\FileCreators\RepositoryFileCreator;

class CreateModelScript //extends Script
{
    private $repositoriesPath = '';
    private $modelsPath = '';
    private $migrationsPath = '';

    public function __construct(array $options)
    {
        $this->repositoriesPath = $options['repositoriesPath'];
        $this->modelsPath = $options['modelsPath'];
        $this->migrationsPath = $options['migrationsPath'];
    }

    public function action($argv)
    {
        $command = $argv[2];
        if ($command == 'create' || $command == '-c') {
            $this->createModel($argv);
        } elseif ($command == 'add-att' || $command == '-aa') {
            $this->addAttributeToModel($argv);
        } elseif ($command == 'delete-att' || $command == '-da') {
            $this->removeAttributeFromModel($argv);
        } elseif ($command == 'rename-att' || $command == '-ra') {
            $this->renameAttributeInModel($argv);
        } elseif ($command == 'update' || $command == '-um') {
            $this->updateModel($argv);
        } elseif ($command == 'update-all' || $command == '-u') {
            $this->updateModels($argv);
        } elseif ($command == 'create-controller' || $command == '-cc') {
            $this->createController($argv);
        } elseif ($command === 'help' || $command == '-h') {
            $this->printUsage();
        } else {
            $this->printUsage();
        }
    }

    private function printUsage()
    {
        Debug::out('usage: ./robin model COMMAND [CLASS_NAME [ATTRIBUTE_LIST]]');
        Debug::out('');
        Debug::out('Command     Long command        Usage');
        Debug::out('-c          create              ClassName attributeName[:TYPE][:ClassName] ...');
        Debug::out('-aa         add-att             ClassName attributeName[:TYPE][:ClassName]');
        Debug::out('-ra         rename-att          ClassName attributeNameOld attributeNameNew[:TYPE][:ClassName]');
        Debug::out('-da         delete-att          ClassName attributeName');
        Debug::out('-um         update              ClassName attributeName');
        Debug::out('-u          update-all');
        Debug::out('-cc         create-controller   ClassName');
        Debug::out('-h          help');
        Debug::out('');
        Debug::out('Types: int, decimal, float, string, text, datetime, date, time, binary, boolean');
    }

    private function checkClassName($name)
    {
        if (!$name) {
            Debug::error('Error: No Classname entered.');
            Debug::out('');
            $this->printUsage();
            die();
        } elseif (!NamingConvention::isUpperCamelCase($name)) {
            Debug::error('Error: ClassName must be in UpperCamelCase.');
            Debug::out('');
            $this->printUsage();
            die();
        }
    }

    private function checkAttName($name)
    {
        if (!NamingConvention::isLowerCamelCase($name)) {
            Debug::error('Error: attributeName must be in lowerCamelCase.');
            Debug::out('');
            $this->printUsage();
            die();
        }
        if (substr($name, -2, 2) == 'ID') {
            Debug::error('Error: Id attributeName must end with Id not ID.');
            Debug::out('');
            $this->printUsage();
            die();
        }
    }

    private function checkIfAttNameEndsWithId($name)
    {
        if (substr($name, -2, 2) != 'Id') {
            Debug::error('Error: attributeName ' . $name . ' must end with Id.');
            Debug::out('');
            $this->printUsage();
            die();
        }
    }

    private function checkStructure($structure)
    {
        if (count($structure) == 0) {
            Debug::error('Error: missing attributes.');
            Debug::out('');
            $this->printUsage();
            die();
        }

        foreach ($structure as $key => $definition) {
            $this->checkAttName($key);
            if ($definition[1]) {
                $this->checkClassName($definition[1]);
                $this->checkIfAttNameEndsWithId($key);
            }
        }
    }

    private function createController($argv)
    {
        $className = $argv[3];
        $this->checkClassName($className);
        $app = $argv[4];
        $controllerFileCreator = new ControllerFileCreator();
        $controllerFileCreator->createModelControllerFile($className, $app);
    }

    private function newModelFileCreator()
    {
        return new ModelFileCreator([
            'modelsPath' => $this->modelsPath,
            'repositoriesPath' => $this->repositoriesPath
        ]);
    }

    private function newRepositoryFileCreator()
    {
        return new RepositoryFileCreator([
            'repositoriesPath' => $this->repositoriesPath
        ]);
    }

    private function newMigrationFileCreator()
    {
        return new MigrationFileCreator([
            'migrationsPath' => $this->migrationsPath
        ]);
    }

    private function createModel($argv)
    {
        $className = $argv[3];
        $this->checkClassName($className);

        $structure = $this->parseStructure($argv, 4);
        $this->checkStructure($structure);
        $structure = $this->prepareStructure($structure);

        $modelFileCreator = $this->newModelFileCreator();
        $modelFileCreator->createBaseFile($className, $structure);
        $modelFileCreator->createFile($className, $structure);

        $repositoryFileCreator = $this->newRepositoryFileCreator();
        $repositoryFileCreator->createBaseFile($className, $structure);
        $repositoryFileCreator->createFile($className, $structure);

        $migrationFileCreator = $this->newMigrationFileCreator();
        $migrationFileCreator->createCreateFile($className, $structure);
    }

    private function updateModel($argv)
    {
        $className = $argv[3];
        $this->checkClassName($className);

        $this->_updateModel($className);
    }

    private function updateModels($argv)
    {
        $baseRepoFiles = $this->getBaseRepoFileNames();
        foreach ($baseRepoFiles as $baseRepoFile) {
            $className = str_replace('BaseRepository.php', '', $baseRepoFile);

            $this->_updateModel($className);
        }
    }

    private function _updateModel($className)
    {
        $structure = $this->getStructureFromRepo($className);

        $modelFileCreator = $this->newModelFileCreator();
        $modelFileCreator->updateBaseFile($className, $structure);
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

    private function addAttributeToModel($argv)
    {
        $className = $argv[3];
        $this->checkClassName($className);

        $structureAdd = $this->parseStructure($argv, 4);
        $this->checkStructure($structureAdd);

        $attName = key($structureAdd);
        $definitions = $structureAdd[$attName];

        $structure = $this->getStructureFromRepo($className);
        $structure[$attName] = $definitions;

        $modelFileCreator = $this->newModelFileCreator();
        $modelFileCreator->updateBaseFile($className, $structure);

        $repositoryFileCreator = $this->newRepositoryFileCreator();
        $repositoryFileCreator->updateBaseFile($className, $structure);

        $migrationFileCreator = $this->newMigrationFileCreator();
        $migrationFileCreator->createAddFile($className, $attName, $definitions);
    }

    private function removeAttributeFromModel($argv)
    {
        $className = $argv[3];
        $this->checkClassName($className);
        $attName = $argv[4];

        $structure = $this->getStructureFromRepo($className);
        $structure[$attName] = $definitions;

        $definitions = $structure[$attName];
        unset($structure[$attName]);

        $modelFileCreator = $this->newModelFileCreator();
        $modelFileCreator->updateBaseFile($className, $structure);

        $repositoryFileCreator = $this->newRepositoryFileCreator();
        $repositoryFileCreator->updateBaseFile($className, $structure);

        $migrationFileCreator = $this->newMigrationFileCreator();
        $migrationFileCreator->createRemoveFile($className, $attName, $definitions);
    }

    private function renameAttributeInModel($argv)
    {
        $className = $argv[3];
        $this->checkClassName($className);
        $attNameOld = $argv[4];
        $structureNew = $this->parseStructure($argv, 5);
        $this->checkStructure($structureNew);
        $attNameNew = key($structureNew);
        $definitionsNew = $structureNew[$attNameNew];

        $structure = $this->getStructureFromRepo($className);
        $definitionsOld = $structure[$attNameOld];
        unset($structure[$attNameOld]);
        $structure[$attNameNew] = $definitionsNew;

        $modelFileCreator = $this->newModelFileCreator();
        $modelFileCreator->updateBaseFile($className, $structure);

        $repositoryFileCreator = $this->newRepositoryFileCreator();
        $repositoryFileCreator->updateBaseFile($className, $structure);

        $migrationFileCreator = $this->newMigrationFileCreator();
        $migrationFileCreator->createRenameFile($className, $attNameOld, $definitionsOld, $attNameNew, $definitionsNew);
    }

    private function getStructureFromRepo($className)
    {
        $classBaseRepositoryName = 'App\\Repositories\\Base\\' . $className . 'BaseRepository';
        $objRepository = new $classBaseRepositoryName();
        $structure = $objRepository->getStructure();
        return $structure;
    }

    private function parseStructure($argv, $start)
    {
        $structure = [];
        $count = count($argv);
        for ($i = $start; $i < $count; $i++) {
            $array = explode(':', $argv[$i]);
            $type = $this->paraseType($array[1]);
            $structure[$array[0]] = [$type , $array[2]];
        }
        return $structure;
    }

    private function prepareStructure($structure)
    {
        unset($structure['id']);
        unset($structure['created']);
        unset($structure['changed']);

        $newStructure = [
            'id' => [DatabaseType::T_PRIMARY, ''],
            'created' => [DatabaseType::T_DATE_TIME, ''],
            'changed' => [DatabaseType::T_DATE_TIME, ''],
        ];

        foreach ($structure as $index => $definition) {
            $newStructure[$index] = $definition;
        }

        return $newStructure;
    }

    private function paraseType($type)
    {
        $types = [
            'primary'   => DatabaseType::T_PRIMARY,
            'int'       => DatabaseType::T_INT,
            'decimal'   => DatabaseType::T_DECIMAL,
            'float'     => DatabaseType::T_FLOAT,
            'string'    => DatabaseType::T_STRING,
            'text'      => DatabaseType::T_TEXT,
            'datetime'  => DatabaseType::T_DATE_TIME,
            'date'      => DatabaseType::T_DATE,
            'time'      => DatabaseType::T_TIME,
            'binary'    => DatabaseType::T_BINARY,
            'boolean'   => DatabaseType::T_BOOLEAN
        ];

        if (!$types[$type]) {
            return DatabaseType::T_INT;
        } else {
            return $types[$type];
        }
    }
}
