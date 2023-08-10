<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Create an Entity class in Modular structure
 *
 * @package App\Commands
 * @author Solomon Ochepa <solomonochepa@gmail.com>
 */
class ModuleEntity extends BaseCommand
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
    protected $name        = 'module:entity';

    /**
     * Command description
     *
     * @var string
     */
    protected $description = 'Generates a new module Entity class.';

    /**
     * Command usage
     *
     * @var string
     */
    protected $usage        = 'module:entity [name] [module] [options]';

    /**
     * Command example
     *
     * @var string
     */
    protected $example      = 'module:entity Example Example';

    /**
     * the Command's Arguments
     *
     * @var array
     */
    protected $arguments    = [
        'name'      => 'The entity class name.',
        'module'    => 'The module name.'
    ];

    /**
     * the Command's Options
     *
     * @var array
     */
    protected $options = [
        '-m' => 'Migration',
    ];

    protected $entity;

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

        // entity name
        while (!isset($params[0])) {
            CLI::error("NOTICE:\t\tThe Entity class name field is required.");

            CLI::write("USAGE:\t\t{$this->usage}", "green");
            CLI::write("EXAMPLE:\t{$this->example}\n", "green");

            $input = CLI::prompt('Entity');
            if (CLI::strlen($input)) {
                $params[0] = $input;
            }
        }

        if (strlen(preg_replace('/[^A-Za-z0-9]+/', '', $params[0])) <> mb_strlen($params[0])) {
            CLI::error("Entity class name must be plain ascii characters A-z, and can contain numbers 0-9");
            return;
        }

        // Module name
        while (!isset($params[1])) {
            CLI::error("NOTICE:\t\tThe Module name field is required.");

            CLI::write("USAGE:\t\t{$this->usage}", "green");
            CLI::write("EXAMPLE:\t{$this->example}\n", "green");

            $input = CLI::prompt('Module', "{$params[0]}");
            if (CLI::strlen($input)) {
                $params[1] = $input;
            }
        }

        if (strlen(preg_replace('/[^A-Za-z0-9]+/', '', $params[1])) <> mb_strlen($params[1])) {
            CLI::error("Module name must be plain ascii characters A-z, and can contain numbers 0-9");
            return;
        }

        $this->entity               = ucfirst($params[0]);
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

        // CLI::getOption('f') == ''

        try {
            $this->createEntity();
        } catch (\Exception $e) {
            CLI::error($e);
        }
    }

    /**
     * Create entity file
     */
    protected function createEntity()
    {
        $entities_path  = $this->createDir('Entities');
        $class          = $this->entity;
        $db_table       = plural(strtolower($this->entity));
        $file           = $entities_path . DIRECTORY_SEPARATOR . $class . '.php';

        if (!file_exists($file)) {
            $template = "<?php

namespace {$this->module}\\Entities;

use CodeIgniter\Entity\Entity;

class {$class} extends Entity
{
    // code
}
";

            file_put_contents($file, $template);
            CLI::write("Entity: {$file}");
        } else {
            CLI::error("Entity allready exists!");
        }
    }

    /**
     * create module Dir
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
