<?php

namespace RobinTheHood\PhpFramework\Database\Repository;

use RobinTheHood\Database\DatabaseType;
use RobinTheHood\Database\DatabaseAction;
use RobinTheHood\PhpFramework\ArrayHelper;
use RobinTheHood\PhpFramework\Database\DatabaseObject\DatabaseObjectCreator;
use RobinTheHood\PhpFramework\Database\Repository\BaseRepository;
use RobinTheHood\SqlBuilder\SqlBuilder;

class Repository extends BaseRepository
{
    protected $options = [];

    public function save($obj)
    {
        if ($obj->getId() > 0) {
            return $this->update($obj);
        } else {
            return $this->add($obj);
        }
    }

    public function newQuery()
    {
        $sqlBuilder = new SqlBuilder();
        $query = $sqlBuilder->select('*')->setTable($this->getTableName());
        return $query;
    }

    public function getAllByQuery($query)
    {
        $result = DatabaseAction::query($query->sql(), $query->getMappings());
        $objs = DatabaseObjectCreator::createObjectsFromArray($result, $this->className);
        return $objs;
    }

    public function getAll($options = [])
    {
        $query = $this->newQuery();
        if (!empty($options['orderBy'])) {
            $query->orderBy($options['orderBy']);
        } elseif (ArrayHelper::getIfSet($this->options, 'orderBy')) {
            $query->orderBy($this->options['orderBy']);
        }
        return $this->getAllByQuery($query);
    }

    public function getAllBy($columnName, $columnValue, $options = [])
    {
        $query = $this->newQuery();
        $query->where()
              ->equals($columnName, $columnValue);
        if (!empty($options['orderBy'])) {
            $query->orderBy($options['orderBy']);
        } elseif (ArrayHelper::getIfSet($this->options, 'orderBy')) {
            $query->orderBy($this->options['orderBy']);
        }

        return $this->getAllByQuery($query);
    }

    public function getAllByArray($values)
    {
        $query = $this->newQuery();
        $where = $query->where();

        foreach ($values as $column => $value) {
            $where->equals($column, $value);
        }

        if (ArrayHelper::getIfSet($this->options, 'orderBy')) {
            $query->orderBy($this->options['orderBy']);
        }

        return $this->getAllByQuery($query);
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function orderBy($orderBy)
    {
        $this->options['orderBy'] = $orderBy;
    }
}
    //
    //
    //
    // public function getAll()
    // {
    //     $query = $this->newQuery();
    //     return getAllByQuery($query);
    // }
    //
    // public function getAllBy($column, $value)
    // {
    //     $query = $this->newQuery()
    //         ->where()
    //         ->equal($column, $value);
    //     return getAllByQuery($query);
    // }
    //
    // public function getAllByArray($values)
    // {
    //     $query = $this->newQuery()
    //         ->where();
    //         ->equal($column, $value);
    //     return getAllByQuery($query);
    // }
    //
    // public function getAllContainsArray($values) {}
    //
    // public function getAllBySql($sql)
    // {
    //
    // }
    //
    // public function deleteAllBy($values) {}
    //
    //
    //
    //
    //
    //
    // public function orderBy($orderBy)
    // {
    //     $this->createSqlBuilder();
    //     $this->sqlBuilder->orderBy($orderBy);
    // }
    //
    // public function groubBy($groupBy)
    // {
    //     $this->createSqlBuilder();
    //     $this->sqlBuilder->groupBy($groupBy);
    // }
    //
    // public function enableCount()
    // {
    //     $this->count = true;
    // }
    //
    // public function disableCount()
    // {
    //     $this->count = false;
    // }
    //
    // public function search($serach)
    // {
    //     $this->createSqlBuilder();
    //     $this->sqlBuilder->search($serach);
    // }
    //
    // public function deleteAllBy($column, $value)
    // {
    //     $sql = 'DELETE FROM `' . $this->getTableName()
    //         . '` WHERE ' . $column . ' = ' . DatabaseSql::dbQuote($value)
    //         . $this->sqlBuilder->getWhere();
    //
    //     $dbh = Database::getInstance();
    //     $dbh->query($sql, PDO::FETCH_ASSOC);
    // }
    //
    // public function getAll()
    // {
    //     $this->createSqlBuilder();
    //
    //     $sql = $this->getSelect();
    //     $sql .= $this->sqlBuilder->getWhere('WHERE');
    //     $sql .= $this->sqlBuilder->getGroupBy('GROUP BY');
    //     $sql .= $this->sqlBuilder->getOrderBy('ORDER BY');
    //     $sql .= $this->sqlBuilder->getLimit('LIMIT');
    //
    //     $this->sqlBuilder->reset();
    //
    //     return $this->executeSql($sql);
    // }
    //
    // public function getAllBy($column, $value)
    // {
    //     $this->createSqlBuilder();
    //
    //     $sql = $this->getSelect();
    //     $sql .= ' WHERE ' . $column . ' = ' . DatabaseSql::dbQuote($value);
    //     $sql .= $this->sqlBuilder->getWhere('AND');
    //     $sql .= $this->sqlBuilder->getGroupBy('GROUP BY');
    //     $sql .= $this->sqlBuilder->getOrderBy('ORDER BY');
    //     $sql .= $this->sqlBuilder->getLimit('LIMIT');
    //
    //     $this->sqlBuilder->reset();
    //
    //     return $this->executeSql($sql);
    // }
    //
    // public function getAllByArray($values)
    // {
    //     $this->createSqlBuilder();
    //
    //     foreach ($values as $key => $value) {
    //         $this->sqlBuilder->where($key  . ' = ' . DatabaseSql::dbQuote($value), 'AND');
    //     }
    //
    //     $sql = $this->getSelect();
    //     $sql .= $this->sqlBuilder->getWhere('WHERE');
    //     $sql .= $this->sqlBuilder->getGroupBy('GROUP BY');
    //     $sql .= $this->sqlBuilder->getOrderBy('ORDER BY');
    //     $sql .= $this->sqlBuilder->getLimit('LIMIT');
    //
    //     $this->sqlBuilder->reset();
    //
    //     return $this->executeSql($sql);
    // }
    //
    // public function getAllContainsArray($values)
    // {
    //     $this->createSqlBuilder();
    //
    //     foreach ($values as $key => $value) {
    //         $where .= $key  . ' LIKE ' . DatabaseSql::dbQuote('%' . $value . '%');
    //         if (++$count != count($values)) {
    //             $where .= ' OR ';
    //         }
    //     }
    //
    //     $sql = $this->getSelect();
    //     $sql .= 'WHERE (' . $where . ')';
    //     $sql .= $this->sqlBuilder->getWhere('AND');
    //     $sql .= $this->sqlBuilder->getGroupBy('GROUP BY');
    //     $sql .= $this->sqlBuilder->getOrderBy('ORDER BY');
    //     $sql .= $this->sqlBuilder->getLimit('LIMIT');
    //
    //     $this->sqlBuilder->reset();
    //
    //     return $this->executeSql($sql);
    // }
    //
    // protected function getSelect()
    // {
    //     if ($this->count) {
    //         $sql = 'SELECT count(*) count FROM `' . $this->getTableName() . '`';
    //     } else {
    //         $sql = 'SELECT * FROM `' . $this->getTableName() . '`';
    //     }
    //     return $sql;
    // }
    //
    // protected function countFromSql($sql)
    // {
    //     $dbh = Database::getInstance();
    //     $dbResult = $dbh->query($sql, PDO::FETCH_ASSOC);
    //     if (!$dbResult) {
    //         Debug::out($dbh->errorInfo());
    //     }
    //     foreach($dbResult as $row) {
    //         return $row['count'];
    //     }
    // }
    //
    // protected function getResultFromSql($sql)
    // {
    //     $dbh = Database::getInstance();
    //     $dbResult = $dbh->query($sql, PDO::FETCH_ASSOC);
    //     if (!$dbResult) {
    //         Debug::out($dbh->errorInfo());
    //     }
    //     return $dbResult;
    // }
    //
    // protected function executeSql($sql)
    // {
    //     if ($this->count) {
    //         return $this->countFromSql($sql);
    //     } else {
    //         return $this->createObjsFromSql($sql);
    //     }
    // }
// }
