# CodeIgniter Modules
This extension enables the creation of CodeIgniter Modules, making your application modular - Hierarchical Model View Controller (HMVC).

All modules are packed in their own directory, with their own individual files;
- Configs,
- Controllers,
- Database,
- Entities,
- Filters,
- Languages,
- Libraries,
- Models,
- Validation,
- Views, etc.
  
This allows code reusability and easy distribution of modules across other CodeIgniter applications.

## Installation
Download the file from GitHub and place them into the `app/Commands` folders in the application directory.

## Usage
This extension is a `CLI` tool, as such all operations are carried out using Commands.

Note: replace the `module` name `Example` and other sample data in the commands below.

### Make / Create
Generates a new Module.<br />
Usage: `module:make [name]`
```php
php spark module:make Example
```

### Delete
Delete a module.<br />
Usage: `module:delete [module]`
```php
php spark module:delete Example
```

### Route
Generates a new Route file.<br />
Usage: `module:route [module]`
```php
php spark module:route Example
```

### Controllers
Generates a new Controller file.<br />
Usage: `module:controller [name] [module]`
```php
php spark module:controller ExampleController Example
```

### Migrations
Generates a new migration file.<br />
Usage: `module:migration [name] [module]`
```php
php spark module:migration CreateExamplesTable Example
```

```php
php spark module:migration create_examples_table Example
```

### Migrate
Migrate a Module migration files.<br />
Usage: `module:migrate [module]`
```php
php spark module:migrate Example
```

### Seeders
Generates a new Module seeder file.<br />
Usage: `module:seeder [name] [module]`
```php
php spark module:seeder Example Example
```

```php
php spark module:seeder ExampleSeeder Example
```

### Seed
Run a db:seed command in a Module.<br />
Usage: `module:seed [module] [seeder]`
```php
php spark module:seed Example
```

```php
php spark module:seed Example ExampleSeeder
```

### Entities
Generates a new module Entity class.<br />
Usage: `module:entity [name] [module]`
```php
php spark module:entity Example Example
```

### Models
Generates a new module Model file.<br />
Usage: `module:model [name] [module]`
```php
php spark module:model ExampleModel Example
```
