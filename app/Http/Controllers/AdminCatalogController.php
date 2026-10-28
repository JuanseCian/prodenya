<?php

namespace App\Http\Controllers;

use App\Models\Barrio;
use App\Models\Localidad;
use App\Models\Problematica;
use App\Models\Provincia;
use App\Models\Rol;
use App\Models\TipoDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class AdminCatalogController extends Controller
{
    public function index(Request $request, string $catalog): View
    {
        $definition = $this->definition($catalog);
        $modelClass = $definition['model'];
        $buscar = trim((string) $request->query('buscar', ''));

        $items = $modelClass::query()
            ->when($definition['with'] ?? [], fn ($query, $relations) => $query->with($relations))
            ->when($buscar !== '', function ($query) use ($buscar, $definition) {
                $query->where(function ($query) use ($buscar, $definition) {
                    foreach ($definition['search'] as $column) {
                        $query->orWhere($column, 'like', "%{$buscar}%");
                    }
                });
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('backend.catalogos.index', [
            'catalog' => $catalog,
            'definition' => $definition,
            'items' => $items,
            'buscar' => $buscar,
        ]);
    }

    public function create(string $catalog): View
    {
        return view('backend.catalogos.form', [
            'catalog' => $catalog,
            'definition' => $this->definition($catalog),
            'item' => null,
            'options' => $this->options($catalog),
        ]);
    }

    public function store(Request $request, string $catalog)
    {
        $definition = $this->definition($catalog);
        $datos = $request->validate($this->rules($request, $definition, $catalog));
        $datos = $this->normalizarDatos($request, $definition, $datos);

        $model = new $definition['model'];
        $model->fill($datos);
        $this->setAuditUser($model, 'updated_by');
        $model->save();

        return redirect()
            ->route('admin.catalogos.index', ['catalog' => $catalog])
            ->with('success', "{$definition['singular']} creado correctamente.");
    }

    public function edit(string $catalog, int $item): View
    {
        $definition = $this->definition($catalog);
        $model = $definition['model'];
        $query = $model::query();

        if (! empty($definition['with'])) {
            $query->with($definition['with']);
        }

        return view('backend.catalogos.form', [
            'catalog' => $catalog,
            'definition' => $definition,
            'item' => $query->findOrFail($item),
            'options' => $this->options($catalog),
        ]);
    }

    public function update(Request $request, string $catalog, int $item)
    {
        $definition = $this->definition($catalog);
        $model = $definition['model']::query()->findOrFail($item);
        $datos = $request->validate($this->rules($request, $definition, $catalog, $model));
        $datos = $this->normalizarDatos($request, $definition, $datos);

        $model->fill($datos);
        $this->setAuditUser($model, 'updated_by');
        $model->save();

        return redirect()
            ->route('admin.catalogos.index', ['catalog' => $catalog])
            ->with('success', "{$definition['singular']} actualizado correctamente.");
    }

    public function destroy(string $catalog, int $item)
    {
        $definition = $this->definition($catalog);
        $model = $definition['model']::query()->findOrFail($item);

        try {
            $this->setAuditUser($model, 'deleted_by');

            if (Schema::hasColumn($model->getTable(), 'deleted_by')) {
                $model->save();
            }

            $model->delete();
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'general' => "No se puede eliminar este {$definition['singular']} porque está siendo utilizado por otros registros.",
            ]);
        }

        return redirect()
            ->route('admin.catalogos.index', ['catalog' => $catalog])
            ->with('success', "{$definition['singular']} eliminado correctamente.");
    }

    private function definition(string $catalog): array
    {
        return match ($catalog) {
            'roles' => [
                'title' => 'Roles',
                'singular' => 'Rol',
                'model' => Rol::class,
                'search' => ['nombre', 'descripcion'],
                'with' => [],
                'columns' => [
                    ['label' => 'Nombre', 'key' => 'nombre'],
                    ['label' => 'Descripción', 'key' => 'descripcion'],
                ],
                'fields' => [
                    ['name' => 'nombre', 'label' => 'Nombre', 'type' => 'text', 'maxlength' => 50, 'required' => true, 'unique' => true],
                    ['name' => 'descripcion', 'label' => 'Descripción', 'type' => 'textarea', 'maxlength' => 255],
                ],
            ],
            'provincias' => [
                'title' => 'Provincias',
                'singular' => 'Provincia',
                'model' => Provincia::class,
                'search' => ['nombre'],
                'with' => [],
                'columns' => [['label' => 'Nombre', 'key' => 'nombre']],
                'fields' => [['name' => 'nombre', 'label' => 'Nombre', 'type' => 'text', 'maxlength' => 100, 'required' => true, 'unique' => true]],
            ],
            'localidades' => [
                'title' => 'Localidades',
                'singular' => 'Localidad',
                'model' => Localidad::class,
                'search' => ['nombre'],
                'with' => ['provincia'],
                'columns' => [
                    ['label' => 'Nombre', 'key' => 'nombre'],
                    ['label' => 'Provincia', 'key' => 'provincia.nombre'],
                ],
                'fields' => [
                    ['name' => 'nombre', 'label' => 'Nombre', 'type' => 'text', 'maxlength' => 100, 'required' => true, 'unique' => true],
                    ['name' => 'provincia_id', 'label' => 'Provincia', 'type' => 'select', 'option_key' => 'provincias', 'required' => true],
                ],
            ],
            'barrios' => [
                'title' => 'Barrios',
                'singular' => 'Barrio',
                'model' => Barrio::class,
                'search' => ['nombre'],
                'with' => ['localidad.provincia'],
                'columns' => [
                    ['label' => 'Nombre', 'key' => 'nombre'],
                    ['label' => 'Localidad', 'key' => 'localidad.nombre'],
                    ['label' => 'Provincia', 'key' => 'localidad.provincia.nombre'],
                ],
                'fields' => [
                    ['name' => 'nombre', 'label' => 'Nombre', 'type' => 'text', 'maxlength' => 100, 'required' => true, 'unique' => true],
                    ['name' => 'localidad_id', 'label' => 'Localidad', 'type' => 'select', 'option_key' => 'localidades', 'required' => true],
                ],
            ],
            'tipos-documento' => [
                'title' => 'Tipos de documento',
                'singular' => 'Tipo de documento',
                'model' => TipoDocumento::class,
                'search' => ['nombre'],
                'with' => [],
                'columns' => [['label' => 'Nombre', 'key' => 'nombre']],
                'fields' => [['name' => 'nombre', 'label' => 'Nombre', 'type' => 'text', 'maxlength' => 80, 'required' => true, 'unique' => true]],
            ],
            'problematicas' => [
                'title' => 'Tipos de problemáticas',
                'singular' => 'Problemática',
                'model' => Problematica::class,
                'search' => ['nombre'],
                'with' => [],
                'columns' => [['label' => 'Nombre', 'key' => 'nombre']],
                'fields' => [['name' => 'nombre', 'label' => 'Nombre', 'type' => 'text', 'maxlength' => 190, 'required' => true]],
            ],
            default => abort(404),
        };
    }

    private function options(string $catalog): array
    {
        return match ($catalog) {
            'localidades' => [
                'provincias' => Provincia::orderBy('nombre')->get(['id', 'nombre'])->all(),
            ],
            'barrios' => [
                'localidades' => Localidad::with('provincia')->orderBy('nombre')->get()->map(fn (Localidad $localidad) => [
                    'id' => $localidad->id,
                    'nombre' => $localidad->nombre.' · '.($localidad->provincia?->nombre ?? 'Sin provincia'),
                ])->all(),
            ],
            default => [],
        };
    }

    private function rules(Request $request, array $definition, string $catalog, ?object $model = null): array
    {
        $rules = [];
        $table = (new $definition['model'])->getTable();

        foreach ($definition['fields'] as $field) {
            $name = $field['name'];
            $fieldRules = [];

            if ($field['type'] === 'checkbox') {
                $fieldRules[] = 'nullable';
                $fieldRules[] = 'boolean';
            } else {
                $fieldRules[] = ($field['required'] ?? false) ? 'required' : 'nullable';
                $fieldRules[] = $field['type'] === 'select' ? 'integer' : 'string';
            }

            if (isset($field['maxlength'])) {
                $fieldRules[] = 'max:'.$field['maxlength'];
            }

            if (($field['option_key'] ?? null) === 'provincias') {
                $fieldRules[] = 'exists:provincias,id';
            }

            if (($field['option_key'] ?? null) === 'localidades') {
                $fieldRules[] = 'exists:localidades,id';
            }

            if ($field['unique'] ?? false) {
                $unique = Rule::unique($table, $name);

                if ($model) {
                    $unique->ignore($model->getKey());
                }

                if (in_array($catalog, ['localidades', 'barrios'], true) && $name === 'nombre') {
                    $parent = $catalog === 'localidades' ? 'provincia_id' : 'localidad_id';
                    $unique->where(fn ($query) => $query->where($parent, $request->input($parent)));
                }

                $fieldRules[] = $unique;
            }

            $rules[$name] = $fieldRules;
        }

        return $rules;
    }

    private function normalizarDatos(Request $request, array $definition, array $datos): array
    {
        foreach ($definition['fields'] as $field) {
            if ($field['type'] === 'checkbox') {
                $datos[$field['name']] = $request->boolean($field['name']);
            }
        }

        return $datos;
    }

    private function setAuditUser(object $model, string $column): void
    {
        if (Schema::hasColumn($model->getTable(), $column)) {
            $model->{$column} = auth()->id();
        }
    }
}
