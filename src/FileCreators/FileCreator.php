<?php
namespace RobinTheHood\PhpFramework\FileCreators;

use RobinTheHood\Terminal\Terminal;

class FileCreator
{
    protected function writeLine($str, $spaces, $newLine = true)
    {
        for($i =0 ; $i < $spaces; $i++) {
            $strSpaces .= ' ';
        }
        if ($newLine) {
            return $strSpaces . $str . "\n";
        } else {
            return $strSpaces . $str;
        }
    }

    protected function writeFile($file, $content, $update = false)
    {
        $fileName = array_pop(explode('/', $file));

        if (!file_exists($file) || $update) {
            if ($update) {
                Terminal::out('updating: ', Terminal::GREEN);
            } else {
                Terminal::out('creating: ', Terminal::GREEN);
            }
            Terminal::out($fileName . "\n", Terminal::WHITE);

            file_put_contents($file, $content);
        } else {
            Terminal::out('already exists: ', Terminal::YELLOW);
            Terminal::out($fileName . "\n", Terminal::WHITE);
        }
    }

    protected function fillTemplate($file, $values)
    {
        $migrationContent = $migrationTmplFile = file_get_contents($file);
        foreach($values as $key => $value) {
            $migrationContent = str_replace('{' .  $key . '}', $value, $migrationContent);
        }
        return $migrationContent;
    }
}
