<?php

namespace Brackets\AdminGenerator\Generate;

use Symfony\Component\Console\Input\InputOption;

class IndexRequest extends ClassGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'admin:generate:request:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an Index request class';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $force = $this->option('force');

        if ($this->generateClass($force)) {
            $this->info('Generating ' . $this->classFullName . ' finished');
        }
    }

    protected function buildClass(): string
    {
        return view('brackets/admin-generator::index-request', [
            'modelBaseName' => $this->modelBaseName,
            'modelDotNotation' => $this->modelDotNotation,
            'modelWithNamespaceFromDefault' => $this->modelWithNamespaceFromDefault,
            'modelVariableName' => $this->modelVariableName,

            'columnsToQuery' => $this->readColumnsFromTable($this->tableName)->filter(function ($column) {
                return !($column['type'] == 'text' || $column['name'] == "password" || $column['name'] == "remember_token" || $column['name'] == "slug" || $column['name'] == "created_at" || $column['name'] == "updated_at" || $column['name'] == "deleted_at");
            })->pluck('name')->toArray(),
        ])->render();
    }

    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating request'],
        ];
    }

    public function generateClassNameFromTable(string $tableName): string
    {
        return 'Index' . $this->modelBaseName;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return $rootNamespace . '\Http\Requests\Admin\\' . $this->modelWithNamespaceFromDefault;
    }
}