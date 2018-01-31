<?php
namespace RobinTheHood\PhpFramework\FileCreators;

use RobinTheHood\Database\DatabaseType;
use RobinTheHood\NamingConvention\NamingConvention;
use RobinTheHood\PhpFramework\FileCreators\Dependency;
use RobinTheHood\PhpFramework\FileCreators\FileCreator;

class ModelFileCreator extends FileCreator
{
    private $modelFileStr;
    private $modelBaseFileStr;
    private $strLoadFiles          = [];
    private $strAttributes         = [];
    private $strSetFunctions       = [];
    private $strGetFunctions       = [];
    private $strIsFunctions        = [];
    private $strGetObjFunctions    = [];
    private $strGetObjsFunctions   = [];

    private $modelsPath = '';
    private $tmplPath = '';
    private $modelTmplFile = '';
    private $modelBaseTmplFile = '';
    private $repositoriesPath = '';

    public function __construct(array $options)
    {
        $this->modelsPath = $options['modelsPath'];
        $this->repositoriesPath = $options['repositoriesPath'];

        if ($options['tmplPath']) {
            $this->tmplPath = $options['tmplPath'];
        } else {
            $this->tmplPath = __DIR__ . '/Templates/';
        }

        $this->modelTmplFile = $this->tmplPath . 'Model.tmpl';
        $this->modelBaseTmplFile = $this->tmplPath . 'ModelBase.tmpl';
    }

    public function prepareStructure($structure)
    {
        if (isset($structure['id'])) {
            unset($structure['id']);
        }
        if (isset($structure['created'])) {
            unset($structure['created']);
        }
        if (isset($structure['changed'])) {
            unset($structure['changed']);
        }
        return $structure;
    }

    public function createFile($className, $structure)
    {
        $structure = $this->prepareStructure($structure);

        $this->createModelFileStr($className, $structure);
        $modelFile = $this->modelsPath . $className . '.php';

        $this->writeFile($modelFile, $this->modelFileStr);
    }

    public function createBaseFile($className, $structure)
    {
        $structure = $this->prepareStructure($structure);

        $this->createComponents($className, $structure);
        $this->createModelBaseFileStr($className, $structure);
        $modelBaseFile = $this->modelsPath . '/Base/' . $className . 'Base.php';

        $this->writeFile($modelBaseFile, $this->modelBaseFileStr);
    }

    public function updateBaseFile($className, $structure)
    {
        $structure = $this->prepareStructure($structure);

        $dependency = new Dependency([
            'repositoriesPath' => $this->repositoriesPath
        ]);
        $dependencies = $dependency->getDependencies($className);

        $this->createComponents($className, $structure, $dependencies);
        $this->createModelBaseFileStr($className, $structure);
        $modelBaseFile = $this->modelsPath . '/Base/' . $className . 'Base.php';

        $this->writeFile($modelBaseFile, $this->modelBaseFileStr, true);
    }


    private function createComponents($className, $structure, $dependencies = [])
    {
        foreach($structure as $name => $definitions) {
            $this->strLoadFiles[$name]         = $this->createStrLoadFile($name, $definitions);
            $this->strGetObjFunctions[$name]   = $this->createStrGetObjFunction($name, $definitions);
            $this->strAttributes[$name]        = $this->createStrAttribute($name, $definitions);
            $this->strSetFunctions[$name]      = $this->createStrSetFunction($name, $definitions);
            $this->strGetFunctions[$name]      = $this->createStrGetFunction($name, $definitions);
            $this->strIsFunctions[$name]       = $this->createStrIsFunction($name, $definitions);
        }

        foreach ($dependencies as $dependency) {
            $this->strLoadFiles[]              = $this->createStrLoadFileDependency($dependency);
            $this->strGetObjsFunctions[]       = $this->createStrGetObjsFunction($className, $dependency);
        }
    }

    private function createModelFileStr($className, $structure)
    {
        $classNameSnakeCase = NamingConvention::camelCaseToSnakeCase($className);

        $values = [
            'LOAD_FILES' => '',
            'CLASS_NAME' => $className
        ];
        $modelContent = $this->fillTemplate($this->modelTmplFile, $values);

        $this->modelFileStr = $modelContent;
    }

    private function createModelBaseFileStr($className, $structure)
    {
        foreach($this->strLoadFiles as $strLoadFile) {
            if ($strLoadFile) {
                $strLoadFileResult .= $strLoadFile;
            }
        }
        foreach($this->strAttributes as $strAttribute) {
            $strAttributeResult .= $strAttribute;
        }
        foreach($this->strSetFunctions as $strSetFunction) {
            $strSetFunctionResult .= $strSetFunction . "\n";
        }
        foreach($this->strGetFunctions as $strGetFunction) {
            $strGetFunctionResult .= $strGetFunction . "\n";
        }
        foreach($this->strIsFunctions as $strIsFunction) {
            if ($strIsFunction) {
                $strIsFunctionResult .= $strIsFunction . "\n";
            }
        }
        foreach($this->strGetObjFunctions as $strGetObjFunction) {
            if ($strGetObjFunction) {
                $strGetObjFunctionResult .= $strGetObjFunction . "\n";
            }
        }
        foreach($this->strGetObjsFunctions as $strGetObjsFunction) {
            $strGetObjsFunctionResult .= $strGetObjsFunction . "\n";
        }

        $values = [
            'LOAD_FILES' => $strLoadFileResult,
            'CLASS_NAME' => $className,
            'ATTRIBUTES' => $strAttributeResult,
            'SET_FUNCTIONS' => $strSetFunctionResult,
            'GET_FUNCTIONS' => $strGetFunctionResult,
            'IS_FUNCTIONS' => $strIsFunctionResult,
            'GET_OBJ_FUNCTIONS' => $strGetObjFunctionResult,
            'GET_OBJS_FUNCTIONS' => $strGetObjsFunctionResult
        ];
        $modelBaseContent = $this->fillTemplate($this->modelBaseTmplFile, $values);

        $this->modelBaseFileStr = $modelBaseContent;
    }

    private function createStrAttribute($name, $definition)
    {
        $spaces = 4;
        $str .= $this->writeLine('protected $' .  $name .';', $spaces);
        return $str;
    }

    private function createStrSetFunction($name, $definition)
    {
        $spaces = 4;
        $str .= $this->writeLine('public function set' . ucfirst($name) . '($value)',          $spaces);
        $str .= $this->writeLine('{',                                                          $spaces);
        $str .= $this->writeLine('    $this->' . $name . ' = $value;',                        $spaces);
        $str .= $this->writeLine('}',                                                          $spaces);
        return $str;
    }

    private function createStrGetFunction($name, $definition)
    {
        $spaces = 4;
        $str .= $this->writeLine('public function get' . ucfirst($name) . '()',    $spaces);
        $str .= $this->writeLine('{',                                              $spaces);
        $str .= $this->writeLine('    return $this->' . $name . ';',              $spaces);
        $str .= $this->writeLine('}',                                              $spaces);
        return $str;
    }

    private function createStrIsFunction($name, $definition)
    {
        $spaces = 4;
        if ($definition[0] == DatabaseType::T_DATE_TIME) {
            $str .= $this->writeLine('public function is' . ucfirst($name) . '()',                 $spaces);
            $str .= $this->writeLine('{',                                                          $spaces);
            $str .= $this->writeLine('    return DateTime::isDateTime($this->' . $name . ');',  $spaces);
            $str .= $this->writeLine('}',                                                          $spaces);
        } elseif ($definition[0] == DatabaseType::T_DATE) {
            $str .= $this->writeLine('public function is' . ucfirst($name) . '()',                 $spaces);
            $str .= $this->writeLine('{',                                                          $spaces);
            $str .= $this->writeLine('    return DateTime::isDate($this->' . $name . ');',      $spaces);
            $str .= $this->writeLine('}',                                                          $spaces);
        } elseif ($definition[0] == DatabaseType::T_TIME) {
            $str .= $this->writeLine('public function is' . ucfirst($name) . '()',                 $spaces);
            $str .= $this->writeLine('{',                                                          $spaces);
            $str .= $this->writeLine('    return DateTime::isTime($this->' . $name . ');',      $spaces);
            $str .= $this->writeLine('}',                                                          $spaces);
        }
        return $str;
    }

    private function createStrLoadFile($name, $definition)
    {
        $object = $definition[1];
        if ($object) {
            $spaces = 0;
            $str .= $this->writeLine('use App\Repositories\\' . $object . 'Repository;',            $spaces);
            return $str;
        }
    }

    private function createStrLoadFileDependency($dependency)
    {
        $str .= $this->writeLine('use App\Repositories\\' . $dependency[0] . 'Repository;',        $spaces);
        return $str;
    }

    private function createStrGetObjFunction($name, $definition)
    {
        $object = $definition[1];
        if ($object) {
            $name = substr($name, 0, strlen($name) -2);
            $spaces = 4;
            $str .= $this->writeLine('public function get' . ucfirst($name) . '($cache = true)',                               $spaces);
            $str .= $this->writeLine('{',                                                                                      $spaces);
            $str .= $this->writeLine('    if (empty($this->' . $name . ') || $cache == false) {',                                   $spaces);
            $str .= $this->writeLine('        $repo = new ' . ucfirst($object) . 'Repository();',                                $spaces);
            $str .= $this->writeLine('        $this->' . $name . ' = $repo->get($this->get' . ucfirst($name). 'Id());',       $spaces);
            $str .= $this->writeLine('    }',                                                                                  $spaces);
            $str .= $this->writeLine('    return $this->' . $name . ';',                                                      $spaces);
            $str .= $this->writeLine('}',                                                                                      $spaces);
            return $str;
        }
    }

    private function createStrGetObjsFunction($className, $dependency)
    {
        // Company:creatorId  -> User:getCompanysAsCreator();
        // Company:changerId  -> User:getCompanysAsChanger();
        // Company:userId     -> User:getCompanys();

        //varNameId -> VarName
        $foreignVarName = $dependency[1];
        $foreignSqlVarName = NamingConvention::camelCaseToSnakeCase($foreignVarName);
        $foreignSqlVarNameSave = "'" . $foreignSqlVarName . "'";
        $foreignVarObjName = substr($foreignVarName, 0, strlen($foreignVarName) - 2);
        $foreignVarObjName = ucfirst($foreignVarObjName);

        //Build functionname
        $foreignObjName = $dependency[0];
        $functionName = $dependency[0] . 's';
        if ($className != $foreignVarObjName) {
            $functionName .= 'As' . $foreignVarObjName;
        }

        //Build Varname
        $varName = lcfirst($functionName);

        $name = substr($name, 0, strlen($name) -2);
        $spaces = 4;
        $str .= $this->writeLine('public function get' . $functionName . '(array $options = [], $cache = true)',                                $spaces);
        $str .= $this->writeLine('{',                                                                                      $spaces);
        $str .= $this->writeLine('    if (empty($this->' . $varName . ') || $cache == false) {',                                   $spaces);
        $str .= $this->writeLine('        $repo = new ' . $foreignObjName . 'Repository();',                                $spaces);
        $str .= $this->writeLine('        $repo->setOptions($options);',                                                        $spaces);
        $str .= $this->writeLine('        $this->' . $varName . ' = $repo->getAllBy(' . $foreignSqlVarNameSave . ', $this->getId());',       $spaces);
        $str .= $this->writeLine('    }',                                                                                  $spaces);
        $str .= $this->writeLine('    return $this->' . $varName . ';',                                                      $spaces);
        $str .= $this->writeLine('}',                                                                                      $spaces);
        return $str;
    }
}
