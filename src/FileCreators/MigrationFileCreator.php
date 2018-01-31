<?php
namespace RobinTheHood\PhpFramework\FileCreators;

use RobinTheHood\Database\DatabaseType;
use RobinTheHood\NamingConvention\NamingConvention;
use RobinTheHood\PhpFramework\FileCreators\FileCreator;

class MigrationFileCreator extends FileCreator
{
    private $migrationsPath = '';
    private $migrationTmplPath = '';
    private $migrationTmplCreateFile = '';
    private $migrationTmplRemoveFile = '';
    private $migrationTmplAddFile = '';
    private $migrationTmplRenameFile = '';

    public function __construct(array $options)
    {
        $this->migrationsPath = $options['migrationsPath'];

        if ($options['migrationTmplPath']) {
            $this->migrationTmplPath = $options['migrationTmplPath'];
        } else {
            $this->migrationTmplPath = __DIR__ . '/Templates/';
        }

        $this->migrationTmplCreateFile = $this->migrationTmplPath . 'MigrationCreate.tmpl';
        $this->migrationTmplRemoveFile = $this->migrationTmplPath . 'MigrationUpdate.tmpl';
        $this->migrationTmplAddFile    = $this->migrationTmplPath . 'MigrationUpdate.tmpl';
        $this->migrationTmplRenameFile = $this->migrationTmplPath . 'MigrationUpdate.tmpl';
    }

    public function createCreateFile($className, $structure)
    {
        foreach($structure as $name => $definitions) {
            $strMigrationAttributes[$name] = $this->createStrMigrationAttribute($name, $definitions);
        }

        $count = 0;
        foreach($strMigrationAttributes as $strMigrationAttribute) {
            $strMigrationAttributeResult .= $strMigrationAttribute;

            // Last iteration
            if (++$count != count($strMigrationAttributes)) {
                $strMigrationAttributeResult .= ",\n";
            }
        }

        $values = [
            'CLASS_NAME' => $className,
            'TABLE_NAME' => NamingConvention::camelCaseToSnakeCase($className),
            'ATTRIBUTES' => $strMigrationAttributeResult
        ];
        $migrationContent = $this->fillTemplate($this->migrationTmplCreateFile, $values);

        $migrationFileName = date('YmdHis') . '_create_' . NamingConvention::camelCaseToSnakeCase($className) . '.php';
        $this->writeFile($this->migrationsPath . $migrationFileName, $migrationContent);
    }

    public function createRemoveFile($className, $name, $definition)
    {
        $classNameSnakeCase = NamingConvention::camelCaseToSnakeCase($className);
        $nameSnakeCase = NamingConvention::camelCaseToSnakeCase($name);

        $migrationTmplFile = $this->migrationTmplPath . 'MigrationUpdate.tmpl';

        $migrationClassName = 'Remove' . ucfirst($name) . 'From' . $className;

        $strUp = $this->getStrRemoveColumn($className, $name, $definition);
        $strDown = $this->getStrAddColumn($className, $name, $definition);

        $values = [
            'CLASS_NAME' => $migrationClassName,
            'UP'         => $strUp,
            'DOWN'       => $strDown,
        ];
        $migrationContent = $this->fillTemplate($this->migrationTmplRemoveFile, $values);

        $migrationFileName = date('YmdHis') . '_remove_' . $nameSnakeCase . '_from_' . $classNameSnakeCase . '.php';
        $this->writeFile($this->migrationsPath . $migrationFileName, $migrationContent);
    }

    public function createAddFile($className, $name, $definition)
    {
        $classNameSnakeCase = NamingConvention::camelCaseToSnakeCase($className);
        $nameSnakeCase = NamingConvention::camelCaseToSnakeCase($name);

        $migrationClassName = 'Add' . ucfirst($name) . 'To' . $className;

        $strUp = $this->getStrAddColumn($className, $name, $definition);
        $strDown = $this->getStrRemoveColumn($className, $name, $definition);

        $values = [
            'CLASS_NAME' => $migrationClassName,
            'UP'         => $strUp,
            'DOWN'       => $strDown,
        ];
        $migrationContent = $this->fillTemplate($this->migrationTmplAddFile, $values);

        $migrationFileName = date('YmdHis') . '_add_' . $nameSnakeCase . '_to_' . $classNameSnakeCase . '.php';
        $this->writeFile($this->migrationsPath . $migrationFileName, $migrationContent);
    }

    public function createRenameFile($className, $nameOld, $definitionOld, $nameNew, $definitionNew)
    {
        $classNameSnakeCase = NamingConvention::camelCaseToSnakeCase($className);

        $nameOldSnakeCase = NamingConvention::camelCaseToSnakeCase($nameOld);
        $nameNewSnakeCase = NamingConvention::camelCaseToSnakeCase($nameNew);

        $migrationClassName = 'Rename' . ucfirst($nameOld) . 'To' . ucfirst($nameNew) . 'In' . $className;

        $strUp = $this->getStrRenameColumn($className, $nameOld, $definitionOld, $nameNew, $definitionNew);
        $strDown = $this->getStrRenameColumn($className, $nameNew, $definitionNew, $nameOld, $definitionOld);

        $values = [
            'CLASS_NAME' => $migrationClassName,
            'UP'         => $strUp,
            'DOWN'       => $strDown,
        ];
        $migrationContent = $this->fillTemplate($this->migrationTmplRenameFile, $values);

        $migrationFileName = date('YmdHis') . '_rename_' . $nameOldSnakeCase . '_to_' . $nameNewSnakeCase . '_in_' . $classNameSnakeCase . '.php';
        $this->writeFile($this->migrationsPath . $migrationFileName, $migrationContent);
    }

    private function getStrAddColumn($className, $name, $definition)
    {
        $classNameSnakeCase   = NamingConvention::camelCaseToSnakeCase($className);
        $nameSnakeCase        = NamingConvention::camelCaseToSnakeCase($name);
        $type                 = $definition[0];
        $classNameSnakeCase   = "'$classNameSnakeCase'";
        $nameSnakeCase        = "'$nameSnakeCase'";
        $type                 = "'$type'";

        $spaces = 8;
        $str .= $this->writeLine('$this->addColumn(' . $classNameSnakeCase . ', ' . $nameSnakeCase . ', ' . $type. ');', $spaces, false);
        return $str;
    }

    private function getStrRenameColumn($className, $nameOld, $definitionOld, $nameNew, $definitionNew)
    {
        $classNameSnakeCase   = NamingConvention::camelCaseToSnakeCase($className);
        $nameOldSnakeCase     = NamingConvention::camelCaseToSnakeCase($nameOld);
        $nameNewSnakeCase     = NamingConvention::camelCaseToSnakeCase($nameNew);
        $typeNew              = $definitionNew[0];

        $classNameSnakeCase   = "'$classNameSnakeCase'";
        $nameOldSnakeCase     = "'$nameOldSnakeCase'";
        $nameNewSnakeCase     = "'$nameNewSnakeCase'";
        $typeNew              = "'$typeNew'";

        $spaces = 8;
        $str .= $this->writeLine('$this->renameColumn(' . $classNameSnakeCase . ', ' . $nameOldSnakeCase . ', ' . $nameNewSnakeCase . ', ' . $typeNew . ');', $spaces, false);
        return $str;
    }

    private function getStrRemoveColumn($className, $name, $definition)
    {
        $classNameSnakeCase   = NamingConvention::camelCaseToSnakeCase($className);
        $nameSnakeCase        = NamingConvention::camelCaseToSnakeCase($name);
        $classNameSnakeCase   = "'$classNameSnakeCase'";
        $nameSnakeCase        = "'$nameSnakeCase'";

        $spaces = 8;
        $str .= $this->writeLine('$this->removeColumn(' . $classNameSnakeCase . ', ' . $nameSnakeCase . ');', $spaces, false);
        return $str;
    }

    private function createStrMigrationAttribute($name, $definitions)
    {
        $nameSnakeCase = NamingConvention::camelCaseToSnakeCase($name);

        $type = $definitions[0];
        $spaces = 12;
        if ($type == DatabaseType::T_PRIMARY) {
            $str .= $this->writeLine("['$nameSnakeCase', '$type', true]", $spaces, false);
        } else {
            $str .= $this->writeLine("['$nameSnakeCase', '$type']", $spaces, false);
        }
        return $str;
    }
}
