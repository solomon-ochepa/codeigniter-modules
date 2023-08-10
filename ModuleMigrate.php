<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\MigrationRunner;
use Config\Services;

/**
 * Create a Seeder in Modular structure
 *
 * @package App\Commands
 * @author Solomon Ochepa <solomonochepa@gmail.com>
 */
class ModuleMigrate extends BaseCommand
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
    protected $name        = 'module:migrate';

    /**
     * Command description
     *
     * @var string
     */
    protected $description = 'Migrate a Module migration files.';

    /**
     * Command usage
     *
     * @var string
     */
    protected $usage        = 'module:migrate [module] [options]';

    /**
     * Command example
     *
     * @var string
     */
    protected $example      = 'module:migrate Example';

    /**
     * the Command's Arguments
     *
     * @var array
     */
    protected $arguments    = [
        'module' => '(optional) The module name.'
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

        // $this->seeder               = ucfirst($params[0]);

        $namespaces = [];

        if ($params[0] === 'all') {
            $modules_path = glob(APPPATH . 'Modules' . DIRECTORY_SEPARATOR . '*');

            foreach ($modules_path as $module_path) {
                $namespaces[] = basename($module_path);
            }
        } else {
            foreach ($params as $key => $module) {
                if ($module) {
                    $namespaces[] = $module;
                }
            }
        }

        try {
            $migrate    = Services::migrations();

            foreach ($namespaces as $key => $namespace) {
                $module_path = APPPATH . 'Modules' . DIRECTORY_SEPARATOR . $namespace;

                if (!is_dir($module_path)) {
                    CLI::error("Module [{$namespace}] not found.");
                    unset($namespaces[$key]);
                } else {
                    CLI::write("Migrating " . $namespace);

                    // $migrate->setNamespace($namespace ?? null)->latest();
                    command("migrate -n {$namespace}");
                }
                CLI::write("\n");
            }
        } catch (\Exception $e) {
            CLI::error($e);
        }
    }
}
