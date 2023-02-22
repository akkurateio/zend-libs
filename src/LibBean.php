<?php

namespace Subvitamine\Libs;

/**
 * This class generates a business class (Bean, DbTable & mapper) from a database table
 */
class LibBean {

    private $_config;
    private $_adapterName;
    private $_tableName;
    private $_schema;
    private $_className;
    private $_columns = null;

    public function __construct($tableName, $className, $schema = 'Application', $adapter = null) {
        $this->_config = \Zend_Registry::get('Zend_Config');
        $this->_adapterName = (!empty($adapter)) ? $adapter : $this->_config->multidbname;
        $this->_tableName = $tableName;
        $this->_className = $className;
        $this->_schema = $schema;
    }

    public function getColumns() {
        $resource = $this->_adapterName;

        $db = new \Zend_Db_Adapter_Pdo_Mysql(array(
            'host' => $this->_config->resources->multidb->$resource->host,
            'username' => $this->_config->resources->multidb->$resource->username,
            'password' => $this->_config->resources->multidb->$resource->password,
            'dbname' => $this->_config->resources->multidb->$resource->dbname
        ));

        $metadatas = $db->describeTable($this->_tableName);
        $this->_columns = array();
        $i = 0;
        foreach ($metadatas as $metadata) {

            $this->_columns[$i] = new \stdClass();

            // get field name
            $this->_columns[$i]->field = $metadata['COLUMN_NAME'];

            // get field type 
            $type = $metadata['DATA_TYPE'];

            // format type in order to cast the bean properties, e.g. (int)
            switch ($type) {
                case 'int':
                case 'float':
                    $type = '(' . $type . ')';
                    break;
                case 'tinyint' :
                    $type = '(boolean)';
                    break;
                default:
                    $type = '';
            }

            $this->_columns[$i]->type = $type;
            $i++;
        }
    }

    /**
     * Generate Bean Factory
     */
    public function generateBeanFactory() {
        if ($this->_columns != null) {

            $toSave = "<?php
                
use Subvitamine\Bean;

class Application_Model_Bean_" . $this->_schema . "_Factory_" . $this->_className . " extends Subvitamine\Bean\AbstractBean {
    
";

            // Properties
            foreach ($this->_columns as $column) {
                $toSave .= "    protected \$_$column->field;" . PHP_EOL;
            }

            // Constructor
            $toSave .= "
    public function __construct(\$row = null, \$objectComplete = true) {
        parent::__construct(\$row, \$objectComplete);
    }

    /* ******* */
    /* GETTERS */
    /* ******* */
";

            foreach ($this->_columns as $column) {
                $getter = $this->_generateGetter($column);
                $toSave .= "
    public function $getter() {";
                if ($column->type == '') {
                    $toSave .= "
        return \$this->_$column->field;";
                } else {
                    $toSave .= "
        return (\$this->_$column->field != null) ? $column->type \$this->_$column->field : null;";
                }
                $toSave .= "
    }
";
            }

            // Generate setters
            $toSave .= "
    /* ******* */
    /* SETTERS */
    /* ******* */
";

            foreach ($this->_columns as $column) {
                $setter = $this->_generatesetter($column);
                $toSave .= "
    public function $setter($$column->field) {
        \$this->_$column->field = $$column->field;
    }
";
            }

            $toSave .= "
}
";
            $beanPath = APPLICATION_PATH . '/models/Bean/';
            if (!file_exists($beanPath)) {
                mkdir($beanPath);
            }

            $beanSchemaPath = $beanPath . $this->_schema . '/';
            if (!file_exists($beanSchemaPath)) {
                mkdir($beanSchemaPath);
            }

            $factorySchemaPath = $beanSchemaPath . 'Factory/';
            if (!file_exists($factorySchemaPath)) {
                mkdir($factorySchemaPath);
            }

            file_put_contents($factorySchemaPath . $this->_className . '.php', $toSave);
        }
    }

    /**
     * Generate Bean
     */
    public function generateBean() {
        $beanPath = APPLICATION_PATH . '/models/Bean/';
        if (!file_exists($beanPath)) {
            mkdir($beanPath);
        }

        $beanSchemaPath = $beanPath . $this->_schema . '/';
        if (!file_exists($beanSchemaPath)) {
            mkdir($beanSchemaPath);
        }

        if ($this->_columns != null && !file_exists($beanSchemaPath . $this->_className . '.php')) {

            $toSave = "<?php
class Application_Model_Bean_" . $this->_schema . "_" . $this->_className . " extends Application_Model_Bean_" . $this->_schema . "_Factory_" . $this->_className . " {
    //Fields to ignore in light mode for toArray function
    protected \$_ignoreList = array();
}
";

            file_put_contents($beanSchemaPath . $this->_className . '.php', $toSave);
        }
    }

    /**
     * Generate DbTable if it doesn't exists
     */
    public function generateDbTable() {

        $dbtablePath = APPLICATION_PATH . '/models/DbTable/';
        if (!file_exists($dbtablePath)) {
            mkdir($dbtablePath);
        }

        $dbtableSchemaPath = $dbtablePath . $this->_schema . '/';
        if (!file_exists($dbtableSchemaPath)) {
            mkdir($dbtableSchemaPath);
        }

        if ($this->_columns != null && !file_exists($dbtableSchemaPath . $this->_className . '.php')) {

            $toSave = "<?php
               
use Subvitamine\DbTable;

abstract class Application_Model_DbTable_" . $this->_schema . "_" . $this->_className . " extends Subvitamine\DbTable\AbstractDbTable
{
    protected \$_adapter = '$this->_adapterName';
    protected \$_name = '$this->_tableName';
    protected \$_primary = 'id';
}";

            file_put_contents($dbtableSchemaPath . $this->_className . '.php', $toSave);
        }
    }

    /**
     * Generate Mapper if it doesn't exists
     */
    public function generateMapper() {

        $mapperPath = APPLICATION_PATH . '/models/mappers/';
        if (!file_exists($mapperPath)) {
            mkdir($mapperPath);
        }

        $mapperSchemaPath = APPLICATION_PATH . '/models/mappers/' . $this->_schema . '/';
        if (!file_exists($mapperSchemaPath)) {
            mkdir($mapperSchemaPath);
        }

        if ($this->_columns != null && !file_exists($mapperSchemaPath . $this->_className . '.php')) {

            $beanName = 'Application_Model_Bean_' . $this->_schema . '_' . $this->_className;

            $toSave = "<?php
class Application_Model_Mapper_" . $this->_schema . "_" . $this->_className . " extends Application_Model_DbTable_" . $this->_schema . "_" . $this->_className . "
{
    protected \$_object = '$beanName';
    
    public function getById(\$id, \$object=false)
    {
    	\$query = \$this->select()->where('id = ?', \$id);
    	\$result = \$this->cleanFetchRow(\$query, \$object);
        
        if(\$object == true) {
            return \$this->_makeObject(\$result);
        }
        else {
            return \$result;
        }
    }
}";

            file_put_contents($mapperSchemaPath . $this->_className . '.php', $toSave);
        }
    }

    private function _generateSetter($column) {
        $setter = ucwords($column->field, "_");
        $setter = str_replace('_', '', $setter);
        $setter = 'set' . $setter;
        return $setter;
    }

    private function _generateGetter($column) {
        $getter = ucwords($column->field, "_");
        $getter = str_replace('_', '', $getter);
        $getter = 'get' . $getter;
        return $getter;
    }

}
