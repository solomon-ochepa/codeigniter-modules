<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\MigrationRunner;
use Config\Services;

/**
 * Run db:seed command in Modular structure
 *
 * @package App\Commands
 * @author Solomon Ochepa <solomonochepa@gmail.com>
 */
class ModuleSeed extends BaseCommand
{
    /**
     * Group
     *
     * @var string
     */
    protected $group       = 'Module';

    /**
     * Command's name
     *
     * @var string
     */
    protected $name        = 'module:seed';

    /**
     * Command description
     *
     * @var string
     */
    protected $description = 'Run a db:seed command in a Module.';

    /**
     * Command usage
     *
     * @var string
     */
    protected $usage        = 'module:seed [module] [seeder]';

    /**
     * Command example
     *
     * @var string
     */
    protected $example      = 'module:seed Example ExampleSeeder';

    /**
     * the Command's Arguments
     *
     * @var array
     */
    protected $arguments    = [
        'module' => '(optional) The module name.',
        'seeder' => '(optional) The seeder name.',
    ];

    /**
     * the Command's Options
     *
     * @var array
     */
    protected $options = [];

    protected $seeder;

    /**
     * Module Name
     */
    protected $module;
    protected $module_lower;
    protected $module_plural;
    protected $module_plural_lower;

    /**
     * Module folder (default /Modules)
     */
    protected $module_path;

    /** @var String $module_basename Modules root dir. */
    protected $module_basename;

    /**
     * Run route:update CLI
     */
    public function run(array $params)
    {
        helper('inflector');

        while (!isset($params[0])) {
            CLI::write("All modules will be migrated, if no name is specified.");

            CLI::write("USAGE:\t\t{$this->usage}", "green");
            CLI::write("EXAMPLE:\t{$this->example}\n", "green");

            $input = CLI::prompt('Module', 'all');
            if (CLI::strlen($input)) {
                $params[0] = $input;
            }
        }

        if (strlen(preg_replace('/[^A-Za-z0-9]+/', '', $params[0])) <> mb_strlen($params[0])) {
            CLI::error("Module name must be plain ascii characters A-z, and can contain numbers 0-9");
            return;
        }

        $this->module   = ucfirst($params[0]);
        $this->seeder   = isset($params[1]) ? $params[1] : null;

        $modules        = APPPATH . 'Modules' . DIRECTORY_SEPARATOR;
        $module_path    = $modules . $this->module;
        if ($params[0] !== "all" and !is_dir($module_path)) {
            CLI::error("Module [{$this->module}] not found.");
            return;
        }

        // $this->seeder               = ucfirst($params[0]);

        $seeders = [];

        if ($params[0] === 'all') {
            $modules_path = glob(APPPATH . 'Modules' . DIRECTORY_SEPARATOR . '*');

            foreach ($modules_path as $module_path) {
                $module = ucfirst(basename($module_path));

                if (file_exists("{$module_path}/Database/Seeds/DatabaseSeeder.php")) {
                    $seeders[] = str_ireplace(['.php', '/'], ['', "\\\\"], "{$module}/Database/Seeds/DatabaseSeeder");
                } else {
                    $seeder_files = glob("{$module_path}/Database/Seeds/*");
                    foreach ($seeder_files as $seeder_file) {
                        $seeders[] = str_ireplace(['.php', '/'], ['', "\\\\"], explode('Modules/', $seeder_file)[1]);
                    }
                }
            }
        } else {
            if ($this->seeder) {
                $seeders[] = "{$this->module}\\Database\\Seeds\\{$this->seeder}";
            } elseif (file_exists("{$module_path}/Database/Seeds/DatabaseSeeder.php")) {
                $seeders[] = str_ireplace(['.php', '/'], ['', "\\\\"], "{$this->module}/Database/Seeds/DatabaseSeeder");
            } else {
                $seeder_files = glob(APPPATH . "Modules/{$this->module}/Database/Seeds/*");
                foreach ($seeder_files as $seeder_file) {
                    $seeders[] = str_ireplace(['.php', '/'], ['', "\\\\"], explode('Modules/', $seeder_file)[1]);
                }
            }
        }

        // dd($seeders);

        try {
            foreach ($seeders as $key => $seeder) {
                CLI::write("Seeding [$seeder]");

                command("db:seed {$seeder}");

                CLI::write("\n");
            }
        } catch (\Exception $e) {
            CLI::error($e);
        }
    }
}
