<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Create a Migration in Modular structure
 *
 * @package App\Commands
 * @author Solomon Ochepa <solomonochepa@gmail.com>
 */
class ModuleMigration extends BaseCommand
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
    protected $name        = 'module:migration';

    /**
     * Command description
     *
     * @var string
     */
    protected $description = 'Generates a new migration file.';

    /**
     * Command usage
     *
     * @var string
     */
    protected $usage        = 'module:migration [name] [module] [options]';

    /**
     * Command example
     *
     * @var string
     */
    protected $example      = 'module:migration create_examples_table Example';

    /**
     * the Command's Arguments
     *
     * @var array
     */
    protected $arguments    = [
        'name' => 'The migration class name.',
        'module' => 'The module name.'
    ];

    /**
     * the Command's Options
     *
     * @var array
     */
    protected $options = [
        '-s' => 'Seed',
    ];

    /** @var string $migration Migration class name */
    protected $migration;

    /**
     * Module Name
     */
    protected $module;
    protected $module_lower;
    protected $module_plural;
    protected $module_lower_plural;

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
            CLI::error("NOTICE:\t\tThe Migration name field is required.");

            CLI::write("USAGE:\t\t{$this->usage}", "green");
            CLI::write("EXAMPLE:\t{$this->example}\n", "green");

            $input = CLI::prompt('Migration');
            if (CLI::strlen($input)) {
                $params[0] = $input;
            }
        }

        if (strlen(preg_replace('/[^A-Za-z0-9_]+/', '', $params[0])) <> mb_strlen($params[0])) {
            CLI::error("Migration name must be plain ascii characters A-z, can contain numbers 0-9 and underscore (_)");
            return;
        }

        while (!isset($params[1])) {
            CLI::error("NOTICE:\t\tThe Module name field is required.");

            CLI::write("USAGE:\t\t{$this->usage}", "green");
            CLI::write("EXAMPLE:\t{$this->example}\n", "green");

            $input = CLI::prompt('Module');
            if (CLI::strlen($input)) {
                $params[1] = $input;
            }
        }

        if (strlen(preg_replace('/[^A-Za-z0-9]+/', '', $params[1])) <> mb_strlen($params[1])) {
            CLI::error("Module name must be plain ascii characters A-z, and can contain numbers 0-9");
            return;
        }

        $this->migration            = $this->str_title($params[0]);
        $this->module               = ucfirst($params[1]);
        $this->module_lower         = strtolower($this->module);
        $this->module_plural        = plural($this->module);
        $this->module_lower_plural  = strtolower($this->module_plural);
        $this->module_basename      = basename(APPPATH) . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $this->module;
        $this->module_path          = APPPATH . '..' . DIRECTORY_SEPARATOR . $this->module_basename;

        // Confirm module.
        if (!is_dir($this->module_path)) {
            CLI::error("Module [{$this->module}] not found.\n");
            return;
        }

        $this->module_path = realpath($this->module_path);

        try {
            $this->createMigration();

            if (CLI::getOption('s')) {
                $this->createSeeder();
            }
        } catch (\Exception $e) {
            CLI::error($e);
        }
    }

    /**
     * function createDir
     *
     * Create directory and set, if required, gitkeep to keep this in git.
     *
     * @param type $folder
     * @param type $gitkeep
     * @return string
     */
    protected function createDir($folder, $gitkeep = false)
    {
        $dir = $this->module_path . DIRECTORY_SEPARATOR .  $folder;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
            if ($gitkeep) {
                file_put_contents($dir .  '/.gitkeep', '');
            }
        }

        return $dir;
    }

    protected function createMigration()
    {
        $this->createDir('Database', true);
        $migration_path = $this->createDir('Database/Migrations', true);

        // Extract table name from option (if set)
        $table = strtolower(CLI::getOption('t') ?? CLI::getOption('table'));

        // Extract table name from class name. (Ex. CreateExampleTable)
        if (!$table) {
            $pattern = '/Create(\w+)Table/';

            preg_match($pattern, $this->migration, $matches);
            if (isset($matches[1])) {
                $table = $this->str_chained($matches[1]);
            }
        }

        // Use the migration class name
        if (!$table) {
            $table = strtolower(plural($this->migration));
        }

        $prefix         = date("Y-m-d-his");
        $class          = $this->migration;
        $file           = "$migration_path/{$prefix}_{$this->str_chained($this->migration)}.php";

        if (!file_exists($file)) {
            $template = "<?php

namespace Modules\\{$this->module}\\Database\\Migrations;

use CodeIgniter\\Database\\Migration;

class {$class} extends Migration
{
    public function up()
    {
        \$this->forge->addField('id');
        \$this->forge->addField([
            // 'id'            => ['type' => 'varbinary', 'constraint' => 36],
            'active'        => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'title'         => ['type' => 'VARCHAR', 'constraint' => 255,],
            'slug'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'description'   => ['type' => 'TEXT', 'null' => true],
            // more fields
            'created_at'    => ['type' => 'datetime', 'null' => true],
            'updated_at'    => ['type' => 'datetime', 'null' => true],
        ]);

        // \$this->forge->addPrimaryKey('id');
        \$this->forge->createTable('{$table}', true);
    }

    public function down()
    {
        \$this->forge->dropTable('{$table}', true);
    }
}
";

            file_put_contents($file, $template);
            CLI::write("Migration: {$file}");
        } else {
            CLI::error("Migration allready exists!");
        }
    }


    protected function createSeeder()
    {
        $this->createDir('Database', true);
        $seeder_path = $this->createDir('Database/Seeds', true);
        $class = "{$this->module}Seeder";
        $file = "$seeder_path/{$class}.php";

        if (!file_exists($file)) {
            // ...
        } else {
            CLI::error("Seeder allready exists!");
        }
    }

    public function str_chained(string $text)
    {
        // Replace uppercase letters with lowercase letters and prepend them with an underscore
        $outputString = preg_replace_callback('/([A-Z])/', function ($matches) {
            return '_' . strtolower($matches[1]);
        }, $text);

        // Remove any leading underscores
        $outputString = ltrim($outputString, '_');

        return $outputString;
    }

    public function str_title(string $text)
    {
        $words = explode('_', $text);
        $titleCaseWords = array_map('ucfirst', $words);
        $titleCase = implode('', $titleCaseWords);

        // Remove any leading underscores
        $titleCase = ltrim($titleCase, '_');

        return $titleCase;
    }
}
