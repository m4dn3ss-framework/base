<?php

namespace m4dn3ss\framework;

/**
 * Class Entity
 * @package m4dn3ss\framework
 * @author Viacheslav Zhabonos - vyacheslav0310@gmail.com
 *
 * @property Database $db
 * @property string $tableName
 * @property array $attributes
 * @property string $primaryKey
 * @property boolean $newRecord
 */
class Entity {
    protected static $db = null;

    protected static $tableName = null;
    protected static $primaryKey = 'id';

    protected $attributes = null;
    private $newRecord = true;

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        $attribute = $this->getAttribute($name);
        if($attribute !== false) {
            return $attribute;
        }
        elseif(property_exists(get_class($this), $name)) {
            $reflection = new \ReflectionProperty(get_class($this), $name);
            if($reflection->isPublic()) {
                return $this->$name;
            }
        }
        throw new \Exception("Cannot find `$name` property at " . get_class($this));
    }

    public function __set($name, $value)
    {
        if($this->getAttribute($name) !== false) {
            $this->setAttribute($name, $value, true);
        }
        elseif(property_exists(get_class($this), $name)) {
            $this->$name = $value;
        }
    }

    public function __construct($attributes = null)
    {
        self::initDatabase();

        if($attributes) {
            if(isset($attributes[static::$primaryKey])) {
                $this->newRecord = false;
            }
            $this->setAttributes($attributes, true);
        }
        else {
            $this->setAttributes($this->getBlankAttributesFromTable(), true);
        }

        $this->afterConstruct();
    }

    private static function initDatabase()
    {
        if(self::$db == null) {
            self::$db = new Database();
        }
    }

    public static function query($statement, $parameters = array(), $fetchStyle = \PDO::FETCH_ASSOC)
    {
        self::initDatabase();
        return self::$db->query($statement, $parameters, $fetchStyle);
    }

    protected function afterConstruct() {}

    private static function getAttributesFromDatabase($condition = '', $parameters = array(), $many = false)
    {
        self::initDatabase();
        $result = self::$db->query("SELECT * FROM " . self::getTableName() . (!empty($condition) ? " WHERE $condition" : ""), $parameters);
        if($result && is_array($result) && !empty($result)) {
            if($many) {
                return $result;
            }
            elseif(isset($result[0])) {
                return $result[0];
            }
        }
        return null;
    }

    /**
     * @param $id
     * @return static
     */
    public static function findByPk($id)
    {
        $pkFieldParam = ':' . static::$primaryKey;
        $attributes = self::getAttributesFromDatabase(static::$primaryKey . "=" . $pkFieldParam, array(static::$primaryKey => $id));
        if($attributes) {
            return new static($attributes);
        }
        return null;
    }

    /**
     * @param string $condition
     * @param array $parameters
     * @return null|static
     */
    public static function findOne($condition = '', $parameters = array())
    {
        $attributes = self::getAttributesFromDatabase($condition, $parameters);
        if($attributes) {
            return new static($attributes);
        }
        return null;
    }

    /**
     * @param string $condition
     * @param array $parameters
     * @return static[]
     */
    public static function find($condition = '', $parameters = array())
    {
        $attributesArray = self::getAttributesFromDatabase($condition, $parameters, true);
        $models = array();
        foreach ($attributesArray as $attributes) {
            $models[] = new static($attributes);
        }
        return $models;
    }

    private function getBlankAttributesFromTable($onlyNames = false)
    {
        self::initDatabase();
        $columns = self::$db->query("DESCRIBE " . self::getTableName());
        $attributes = array();
        foreach ($columns as $column) {
            if($onlyNames) {
                $attributes[] = $column['Field'];
            }
            else {
                $attributes[$column['Field']] = null;
            }
        }
        return $attributes;
    }

    public static function getTableName()
    {
        return static::$tableName;
    }

    public function isNewRecord()
    {
        return $this->newRecord;
    }

    protected function getAttribute($name)
    {
        return $this->attributes && array_key_exists($name, $this->attributes) ? $this->attributes[$name] : false;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    protected function setAttribute($name, $value, $setPk = false)
    {
        if($name != static::$primaryKey || $setPk) {
            $this->attributes[$name] = $value;
        }
    }

    public function setAttributes($attributes, $setPk = false)
    {
        $tableAttributes = $this->getBlankAttributesFromTable(true);
        foreach($attributes as $name => $value) {
            if(in_array($name, $tableAttributes)) {
                $this->setAttribute($name, $value, $setPk);
            }
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function save()
    {
        if($this->beforeSave()) {
            $attributes = $this->getAttributes();
            if (array_key_exists(static::$primaryKey, $attributes)) {
                if ($this->isNewRecord() && $attributes[static::$primaryKey] === null) {
                    unset($attributes[static::$primaryKey]);
                } else if (!$this->isNewRecord()) {
                    unset($attributes[static::$primaryKey]);
                }
            }

            if ($this->isNewRecord()) {
                return self::$db->insert(self::getTableName(), $attributes);
            } else {
                $pkParam = ':' . static::$primaryKey;
                $id = $this->getAttribute(static::$primaryKey);
                if ($id) {
                    $saveResult = self::$db->update(self::getTableName(), static::$primaryKey . '=' . $pkParam, $attributes, array(static::$primaryKey => $id));
                    if ($saveResult) {
                        $this->afterSave();
                    }
                    return $saveResult;
                } else {
                    throw new \Exception("Value of primary key `" . static::$primaryKey . "` should be specified for method `save()` at class `" . get_class($this) . "`");
                }
            }
        }
        else {
            return false;
        }
    }

    /**
     * Method calling at the beginning of save() method
     * @return bool
     */
    protected function beforeSave() {
        return true;
    }

    /**
     * Method calling after method save() called successfully
     */
    protected function afterSave() {}


    public function delete()
    {
        if($this->beforeDelete() && !$this->isNewRecord()) {
            $pkParam = ':' . static::$primaryKey;
            $id = $this->getAttribute(static::$primaryKey);
            if ($id) {
                $deleteResult = self::$db->delete(self::getTableName(), static::$primaryKey . '=' . $pkParam, array(static::$primaryKey => $id));
                if ($deleteResult) {
                    $this->afterDelete();
                }
                return $deleteResult;
            } else {
                throw new \Exception("Value of primary key `" . static::$primaryKey . "` should be specified for method `delete()` at class `" . get_class($this) . "`");
            }
        }
        else {
            return false;
        }
    }

    /**
     * Method calling at the beginning of delete() method
     * @return bool
     */
    protected function beforeDelete() {
        return true;
    }

    /**
     * Method calling after method delete() called successfully
     */
    protected function afterDelete() {}

}