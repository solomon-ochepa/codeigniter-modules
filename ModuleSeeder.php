<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Create a Seeder in Modular structure
 *
 * @package App\Commands
 * @author Solomon Ochepa <solomonochepa@gmail.com>
 */
class ModuleSeeder extends BaseCommand
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
    protected $name        = 'module:seeder';

    /**
     * Command description
     *
     * @var string
     */
    protected $description = 'Generates a new Module seeder file.';

    /**
     * Command usage
     *
     * @var string
     */
    protected $usage        = 'module:seeder [name] [module] [options]';

    /**
     * Command example
     *
     * @var string
     */
    protected $example      = 'module:seeder ExampleSeeder Example';

    /**
     * the Command's Arguments
     *
     * @var array
     */
    protected $arguments    = [
        'name' => 'The seeder class name.',
        'module' => 'The module name.'
    ];

    /**
     * the Command's Options
     *
     * @var array
     */
    protected $options = [
        '-f|--force' => 'Force',
    ];

    protected $seeder;

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
            CLI::error("NOTICE:\t\tThe Seeder name field is required.");

            CLI::write("USAGE:\t\t{$this->usage}", "green");
            CLI::write("EXAMPLE:\t{$this->example}\n", "green");

            $input = CLI::prompt('Seeder');
            if (CLI::strlen($input)) {
                $params[0] = $input;
            }
        }

        if (strlen(preg_replace('/[^A-Za-z0-9]+/', '', $params[0])) <> mb_strlen($params[0])) {
            CLI::error("Seeder name must be plain ascii characters A-z, and can contain numbers 0-9");
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

        $this->seeder               = ucfirst($params[0]);
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
            $this->createSeeder();
        } catch (\Exception $e) {
            CLI::error($e);
        }
    }

    protected function createSeeder()
    {
        $this->createDir('Database', true);
        $seeder_path = $this->createDir('Database/Seeds', true);

        // Construct class name ~ ExampleSeeder
        $pattern = '/(\w+)Seeder/';

        preg_match($pattern, $this->seeder, $matches);
        if (!isset($matches[1])) {
            $this->seeder = $this->seeder . "Seeder";
        }

        $file = "$seeder_path/{$this->seeder}.php";

        if (!file_exists($file)) {
            $template = "<?php

namespace {$this->module}\\Database\\Seeds;

use CodeIgniter\\Database\\Seeder;
use {$this->module}\\Models\\{$this->module}Model;

class {$this->seeder} extends Seeder
{
    public function run()
    {
        \${$this->module_lower_plural} = [
            [
                '' => '',
            ]
        ];

        foreach (\${$this->module_lower_plural} as \$key => \${$this->module_lower}) {
            # code...
        }
    }
}
";

            file_put_contents($file, $template);
            CLI::write("Seeder: {$file}");
        } else {
            CLI::error("Seeder allready exists!");
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
}
