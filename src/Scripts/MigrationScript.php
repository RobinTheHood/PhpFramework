<?php
namespace RobinTheHood\PhpFramework\Scripts;

use RobinTheHood\Migration\Migration;
use RobinTheHood\Terminal\Terminal;

class MigrationScript
{
    private $migration;

    public function __construct($config)
    {
        $this->migration = new Migration($config);
    }

    public function action($argv)
    {
        $command = $argv[2];
        if ($command == 'migrate' || $command == '-m') {
            $this->migration->up();

        } elseif ($command == 'rollback' || $command == '-r') {
            $this->migration->rollback();

        } elseif ($command == 'status' || $command == '-s') {
            $this->migration->printStatus();

        } else {
            Terminal::outln('Commands:');
            Terminal::outln('migrate or -m');
            Terminal::outln('rollback or -r');
            Terminal::outln('status or -s');
        }
    }
}
