<?php

namespace m4dn3ss\framework;

/**
 * Class Database
 * @package m4dn3ss\framework
 * @author Viacheslav Zhabonos - vyacheslav0310@gmail.com
 *
 * @property array $parameters
 * @property \PDO $db
 */

class Database
{
    private static $db = null;

    /**
     * Database constructor.
     * @param null $params
     * @throws \Exception
     */
    public function __construct($params = null)
    {
        if(self::$db === null) {
            $error = null;
            if ($params === null)
                $error = 'No database connection parameters are provided';

            if (!isset($params['username']))
                $error = 'No parameter `username` provided for database connection';

            if (!isset($params['password']))
                $error = 'No parameter `password` provided for database connection';

            if (!isset($params['schema']))
                $error = 'No parameter `schema` provided for database connection';

            if (!isset($params['host']))
                $error = 'No parameter `host` provided for database connection';

            if ($error === null) {
                $this->connect($params);
            } else {
                throw new \Exception($error);
            }
        }
    }

    /**
     * Connects to database
     * @param $params
     */
    private function connect($params)
    {
        $dsn = 'mysql' .
            ':host=' . $params['host'] .
            ((!empty($params['port'])) ? (';port=' . $params['port']) : '') .
            ';dbname=' . $params['schema'] .
            ((!empty($params['charset'])) ? (';charset=' . $params['charset']) : '');
        self::$db = new \PDO($dsn, $params['username'], $params['password']);
    }

    /**
     * @param \PDOStatement $stmt
     * @param array $parameters
     */
    private function bindParams(&$stmt, $parameters)
    {
        foreach ($parameters as $parameter => $variable) {
            if(strpos(':', $parameter) != 0) {
                $parameter = ':' . $parameter;
            }
            $type = \PDO::PARAM_STR;
            if($parameter == ':id') {
                $type = \PDO::PARAM_INT;
            }
            $stmt->bindParam($parameter, $variable, $type);
        }
    }

    /**
     * @param string $condition
     * @param array $parameters
     * @param array $binding
     */
    private function processConditionParameters(&$condition, &$parameters, &$binding)
    {
        foreach ($parameters as $param => $value) {
            $this->processConditionParameter($param, $value,$condition, $parameters, $binding);
        }
    }

    private function processConditionParameter($parameter, $variable, &$condition, &$parameters, &$binding)
    {
        if(isset($binding[$parameter])) {
            $newParameter = $parameter . uniqid();
            if(!isset($binding[$newParameter])) {
                $parameters[$newParameter] = $variable;
                unset($parameters[$parameter]);
                $condition = str_replace($parameter, $newParameter, $condition);
            }
            else {
                $this->processConditionParameter($newParameter, $variable, $condition, $parameters, $binding);
            }
        }
    }

    /**
     * @param $statement
     * @param array $parameters
     * @param int $fetchStyle
     * @return array
     */
    public function query($statement, $parameters = array(), $fetchStyle = \PDO::FETCH_ASSOC)
    {
        $stmt = self::$db->prepare($statement);
        $stmt->execute($parameters);
        return $stmt->fetchAll($fetchStyle);
    }

    /**
     * Inserts specified values into specified table
     * @param $table
     * @param array $values
     * @return bool
     * @throws \Exception
     */
    public function insert($table, $values = array())
    {
        $variables = array();
        foreach ($values as $parameter => $variable) {
            $variables[] = "`$parameter`=:$parameter";
        }
        if(!empty($variables)) {
            $query = "INSERT INTO `$table` SET " . implode(', ', $variables);
            $stmt = self::$db->prepare($query);
            return $stmt->execute($values);
        }
        else {
            throw new \Exception('No $values specified for insert() at '. __CLASS__ . ' class');
        }
    }

    /**
     * Updates rows in table
     * @param $table
     * @param string $condition
     * @param array $values
     * @param array $parameters
     * @return bool
     * @throws \Exception
     */
    public function update($table, $condition = '', $values = array(), $parameters = array())
    {
        $variables = array();
        $binding = array();
        foreach ($values as $parameter => $variable) {
            $variables[] = "`$parameter`=:$parameter ";
            $binding[$parameter] = $variable;
        }
        if(!empty($variables)) {
            $this->processConditionParameters($condition, $parameters, $binding);
            $binding = array_merge($binding, $parameters);
            $query = "UPDATE `$table` SET " . implode(',', $variables) . (!empty($condition) ? "WHERE $condition" : "");
            $stmt = self::$db->prepare($query);
//            $this->bindParams($stmt, $binding);
            return $stmt->execute($binding);
        }
        else {
            throw new \Exception('No $values specified for insert() at '. __CLASS__ . ' class');
        }
    }

    /**
     * @param $table
     * @param string $condition
     * @param array $parameters
     * @return bool
     */
    public function delete($table, $condition = '', $parameters = array())
    {
        $stmt = self::$db->prepare("DELETE FROM $table " . (!empty($condition) ? "WHERE $condition" : ""));
        return $stmt->execute($parameters);
    }
}