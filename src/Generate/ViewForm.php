<?php

namespace Brackets\AdminGenerator\Generate;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputOption;

class ViewForm extends ViewGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'admin:generate:form';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate create and edit view templates';

    /**
     * Path for create view
     *
     * @var string
     */
    protected $create = 'create';

    /**
     * Path for edit view
     *
     * @var string
     */
    protected $edit = 'edit';

    /**
     * Path for form view
     *
     * @var string
     */
    protected $form = 'form';

    /**
     * Path for form right view
     *
     * @var string
     */
    protected $formRight = 'form-right';

    /**
     * Path for js view
     *
     * @var string
     */
    protected $formJs = 'form-js';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $force = $this->option('force');

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        if (!empty($template = $this->option('template'))) {
            $this->create = 'templates.' . $template . '.create';
            $this->edit = 'templates.' . $template . '.edit';
            $this->form = 'templates.' . $template . '.form';
            $this->formRight = 'templates.' . $template . 'form-right';
            $this->formJs = 'templates.' . $template . '.form-js';
        }

        if (!empty($belongsToMany = $this->option('belongs-to-many'))) {
            $this->setBelongToManyRelation($belongsToMany);
        }

        $viewPath = resource_path('views/admin/' . $this->modelViewsDirectory . '/components/form-elements.blade.php');
        if ($this->alreadyExists($viewPath) && !$force) {
            $this->error('File ' . $viewPath . ' already exists!');
        } else {
            if ($this->alreadyExists($viewPath) && $force) {
                $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
                $this->files->delete($viewPath);
            }

            $this->makeDirectory($viewPath);

            $this->files->put($viewPath, $this->buildForm());

            $this->info('Generating ' . $viewPath . ' finished');
        }

        if (in_array("published_at", array_column($this->getVisibleColumns($this->tableName, $this->modelVariableName)->toArray(), 'name'))) {
            $viewPath = resource_path('views/admin/' . $this->modelViewsDirectory . '/components/form-elements-right.blade.php');
            if ($this->alreadyExists($viewPath) && !$force) {
                $this->error('File ' . $viewPath . ' already exists!');
            } else {
                if ($this->alreadyExists($viewPath) && $force) {
                    $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
                    $this->files->delete($viewPath);
                }

                $this->makeDirectory($viewPath);

                $this->files->put($viewPath, $this->buildFormRight());

                $this->info('Generating ' . $viewPath . ' finished');
            }
        }

        $viewPath = resource_path('views/admin/' . $this->modelViewsDirectory . '/create.blade.php');
        if ($this->alreadyExists($viewPath) && !$force) {
            $this->error('File ' . $viewPath . ' already exists!');
        } else {
            if ($this->alreadyExists($viewPath) && $force) {
                $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
                $this->files->delete($viewPath);
            }

            $this->makeDirectory($viewPath);

            $this->files->put($viewPath, $this->buildCreate());

            $this->info('Generating ' . $viewPath . ' finished');
        }

        $viewPath = resource_path('views/admin/' . $this->modelViewsDirectory . '/edit.blade.php');
        if ($this->alreadyExists($viewPath) && !$force) {
            $this->error('File ' . $viewPath . ' already exists!');
        } else {
            if ($this->alreadyExists($viewPath) && $force) {
                $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
                $this->files->delete($viewPath);
            }

            $this->makeDirectory($viewPath);

            $this->files->put($viewPath, $this->buildEdit());

            $this->info('Generating ' . $viewPath . ' finished');
        }

        $formJsPath = resource_path('js/admin/' . $this->modelJSName . '/Form.js');

        if ($this->alreadyExists($formJsPath) && !$force) {
            $this->error('File ' . $formJsPath . ' already exists!');
        } else {
            if ($this->alreadyExists($formJsPath) && $force) {
                $this->warn('File ' . $formJsPath . ' already exists! File will be deleted.');
                $this->files->delete($formJsPath);
            }

            $this->makeDirectory($formJsPath);

            $this->files->put($formJsPath, $this->buildFormJs());
            $this->info('Generating ' . $formJsPath . ' finished');
        }

        $indexJsPath = resource_path('js/admin/' . $this->modelJSName . '/index.js');
        $bootstrapJsPath = resource_path('js/admin/index.js');

        if ($this->appendIfNotAlreadyAppended($indexJsPath, "import './Form';" . PHP_EOL)) {
            $this->info('Appending Form to ' . $indexJsPath . ' finished');
        }
        if ($this->appendIfNotAlreadyAppended($bootstrapJsPath, "import './" . $this->modelJSName . "';" . PHP_EOL)) {
            $this->info('Appending Form to ' . $bootstrapJsPath . ' finished');
        }
    }

    protected function isUsedTwoColumnsLayout(): bool
    {
        return in_array("published_at", array_column($this->readColumnsFromTable($this->tableName)->toArray(), 'name'));
    }

    protected function buildForm(): string
    {
        return view('brackets/admin-generator::' . $this->form, [
            'modelBaseName' => $this->modelBaseName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelPlural' => $this->modelPlural,
            'modelDotNotation' => $this->modelDotNotation,
            'modelLangFormat' => $this->modelLangFormat,

            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName)->sortBy(function ($column) {
                return !($column['type'] == "json");
            }),
            'hasTranslatable' => $this->readColumnsFromTable($this->tableName)->filter(function ($column) {
                    return $column['type'] == "json";
                })->count() > 0,
            'wysiwygTextColumnNames' => ['text', 'body', 'description'],
            'relations' => $this->relations,
        ])->render();
    }

    protected function buildFormRight(): string
    {
        return view('brackets/admin-generator::' . $this->formRight, [
            'modelBaseName' => $this->modelBaseName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelPlural' => $this->modelPlural,
            'modelDotNotation' => $this->modelDotNotation,
            'modelLangFormat' => $this->modelLangFormat,
            'modelVariableName' => $this->modelVariableName,

            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName)->sortBy(function ($column) {
                return !($column['type'] == "json");
            }),
            'hasTranslatable' => $this->readColumnsFromTable($this->tableName)->filter(function ($column) {
                    return $column['type'] == "json";
                })->count() > 0,
            'translatableTextarea' => ['perex', 'text', 'body'],
            'relations' => $this->relations,
        ])->render();
    }

    protected function buildCreate(): string
    {
        return view('brackets/admin-generator::' . $this->create, [
            'modelBaseName' => $this->modelBaseName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelVariableName' => $this->modelVariableName,
            'modelPlural' => $this->modelPlural,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelDotNotation' => $this->modelDotNotation,
            'modelJSName' => $this->modelJSName,
            'modelLangFormat' => $this->modelLangFormat,
            'resource' => $this->resource,
            'isUsedTwoColumnsLayout' => $this->isUsedTwoColumnsLayout(),

            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName),
            'hasTranslatable' => $this->readColumnsFromTable($this->tableName)->filter(function ($column) {
                    return $column['type'] == "json";
                })->count() > 0,
        ])->render();
    }

    protected function buildEdit(): string
    {
        return view('brackets/admin-generator::' . $this->edit, [
            'modelBaseName' => $this->modelBaseName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelVariableName' => $this->modelVariableName,
            'modelPlural' => $this->modelPlural,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelDotNotation' => $this->modelDotNotation,
            'modelJSName' => $this->modelJSName,
            'modelLangFormat' => $this->modelLangFormat,
            'resource' => $this->resource,
            'isUsedTwoColumnsLayout' => $this->isUsedTwoColumnsLayout(),

            'modelTitle' => $this->readColumnsFromTable($this->tableName)->filter(function ($column) {
                return in_array($column['name'], ['title', 'name', 'first_name', 'email']);
            })->first(null, ['name' => 'id'])['name'],
            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName),
            'hasTranslatable' => $this->readColumnsFromTable($this->tableName)->filter(function ($column) {
                    return $column['type'] == "json";
                })->count() > 0,
        ])->render();
    }

    protected function buildFormJs(): string
    {
        return view('brackets/admin-generator::' . $this->formJs, [
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelJSName' => $this->modelJSName,

            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName),
        ])->render();
    }

    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating form'],
        ];
    }
}
