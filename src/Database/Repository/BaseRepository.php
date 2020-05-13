<?php
namespace RobinTheHood\PhpFramework\Database\Repository;

use RobinTheHood\Database\DatabaseType;
use RobinTheHood\Database\DatabaseAction;
use RobinTheHood\DateTime\DateTime;
use RobinTheHood\NamingConvention\NamingConvention;
use RobinTheHood\PhpFramework\Database\DatabaseObject\DatabaseObjectCreator;
use RobinTheHood\SqlBuilder\SqlBuilder;

class BaseRepository
{
    protected $structure;
    protected $className;

    public function __construct()
    {
        $this->addStructure([
            'created' => [DatabaseType::T_DATE_TIME, ''],
            'changed' => [DatabaseType::T_DATE_TIME, '']
        ]);
    }

    protected function addStructure(array $structure)
    {
        foreach($structure as $key => $definition) {
            $this->structure[$key] = $definition;
        }
    }

    public function getStructure()
    {
        return $this->structure;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getTableName()
    {
        return NamingConvention::camelCaseToSnakeCase($this->className);
    }

    public function get($id)
    {
        $query = new SqlBuilder();
        $query->select('*')->setTable($this->getTableName())
            ->limit(1)
            ->where()
            ->equals('id', $id);

        $result = DatabaseAction::query($query->sql(), $query->getMappings());

        $obj = DatabaseObjectCreator::createObjectsFromArray($result, $this->className);

        if (isset($obj[0])) {
            return $obj[0];
        } else {
            return null;
        }
    }

    public function add($obj)
    {
        if (is_a($obj, $this->className)) {
            return null;
        }

        $obj->setCreated(DateTime::dbDateTimeNow());
        $obj->setChanged(DateTime::dbDateTimeNow());
        
        $query = new SqlBuilder();
        $query = $query->insert()->setTable($this->getTableName());

        foreach ($this->structure as $keyCamelCase => $definitions) {
            $columnName = NamingConvention::camelCaseToSnakeCase($keyCamelCase);
            $value = $obj->get($keyCamelCase);
            $query->addValue($columnName, $value);
        }

        $id = DatabaseAction::execute($query->sql(), $query->getMappings());

        $obj->setId($id);

        return $id;
    }

    public function update($obj)
    {
        if (is_a($obj, $this->className)) {
            return null;
        }

        $obj->setChanged(DateTime::dbDateTimeNow());

        $query = new SqlBuilder();
        $query = $query->update()->setTable($this->getTableName());

        $query->where()
            ->equals('id', $obj->getId());

        foreach ($this->structure as $keyCamelCase => $definitions) {
            $columnName = NamingConvention::camelCaseToSnakeCase($keyCamelCase);
            $value = $obj->get($keyCamelCase);
            $query->setValue($columnName, $value);
        }

        DatabaseAction::execute($query->sql(), $query->getMappings());

        return $obj->getId();
    }

    public function delete($obj)
    {
        if (is_a($obj, $this->className)) {
            return null;
        }

        $query = new SqlBuilder();
        $query = $query->delete()->setTable($this->getTableName())
            ->where()
            ->equals('id', $obj->getId());

        DatabaseAction::execute($query->sql(), $query->getMappings());
    }
}
