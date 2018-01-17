<?php
namespace RobinTheHood\PhpFramework\Database\DatabaseObject;

use RobinTheHood\PhpFramework\Object\Object;

class DatabaseObject extends Object
{
    protected $id = -1;
    protected $created = '';
    protected $changed = '';

    public function __construct($array = [])
    {
        $this->loadFromArray($array);
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setCreated($created)
    {
        $this->created = $created;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setChanged($changed)
    {
        $this->changed = $changed;
    }

    public function getChanged()
    {
        return $this->changed;
    }

    public function isCreated()
    {
        return DateTime::isDateTime($this->created);
    }

    public function isChangend()
    {
        return DateTime::isDateTime($this->changed);
    }
}
