<?php

namespace RobinTheHood\PhpFramework\FileCreators;

use RobinTheHood\PhpFramework\FileCreators\FileCreator;

class RepositoryFileCreator extends FileCreator
{
    private $tmplPath;
    private $repositoriesPath;
    private $repositoryTmplFile;
    private $repositoryBaseTmplFile;

    public function __construct(array $options)
    {
        $this->repositoriesPath = $options['repositoriesPath'];

        if ($options['tmplPath']) {
            $this->tmplPath = $options['tmplPath'];
        } else {
            $this->tmplPath = __DIR__ . '/Templates/';
        }

        $this->repositoryTmplFile = $this->tmplPath . 'Repository.tmpl';
        $this->repositoryBaseTmplFile = $this->tmplPath . 'RepositoryBase.tmpl';
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

    public function createBaseFile($className, $structure)
    {
        $structure = $this->prepareStructure($structure);

        foreach ($structure as $name => $definitions) {
            $strRepoAttributes[$name] = $this->createStrRepoAttribute($name, $definitions);
        }

        $last = count($strRepoAttributes);
        $count = 0;
        $strRepoAttributeResult = '';
        foreach ($strRepoAttributes as $strRepoAttribute) {
            $strRepoAttributeResult .= $strRepoAttribute;
            if (++$count != $last) {
                $strRepoAttributeResult .= ",\n";
            }
        }

        $values = [
            'CLASS_NAME' => $className,
            'ATTRIBUTES' => $strRepoAttributeResult
        ];
        $content = $this->fillTemplate($this->repositoryBaseTmplFile, $values);

        $repoBaseFile = $this->repositoriesPath . '/Base/' . $className . 'BaseRepository.php';
        $this->writeFile($repoBaseFile, $content);
    }

    public function updateBaseFile($className, $structure)
    {
        $structure = $this->prepareStructure($structure);

        foreach ($structure as $name => $definitions) {
            $strRepoAttributes[$name] = $this->createStrRepoAttribute($name, $definitions);
        }

        $last = count($strRepoAttributes);
        $count = 0;
        $strRepoAttributeResult = '';
        foreach ($strRepoAttributes as $strRepoAttribute) {
            $strRepoAttributeResult .= $strRepoAttribute;
            if (++$count != $last) {
                $strRepoAttributeResult .= ",\n";
            }
        }

        $values = [
            'CLASS_NAME' => $className,
            'ATTRIBUTES' => $strRepoAttributeResult
        ];
        $content = $this->fillTemplate($this->repositoryBaseTmplFile, $values);

        $repoBaseFile = $this->repositoriesPath . '/Base/' . $className . 'BaseRepository.php';
        $this->writeFile($repoBaseFile, $content, true);
    }

    public function createFile($className, $structure)
    {
        $structure = $this->prepareStructure($structure);

        $values = [
            'CLASS_NAME' => $className
        ];
        $content = $this->fillTemplate($this->repositoryTmplFile, $values);

        $repoFile = $this->repositoriesPath . $className . 'Repository.php';
        $this->writeFile($repoFile, $content);
    }

    private function createStrRepoAttribute($name, $definitions)
    {
        $type = $definitions[0];
        $object = $definitions[1];
        $spaces = 12;
        $str = $this->writeLine("'" . $name . "' => ['" . $type . "', '" . $object . "']", $spaces, false);
        return $str;
    }
}
