<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DatasetRequest;
use App\Imports\DatasetsImport;
use App\Models\Dataset;
use App\Preprocessing\PreprocessingService;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Maatwebsite\Excel\Facades\Excel;

use function PHPSTORM_META\map;

/**
 * Class DatasetCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DatasetCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Dataset::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/dataset');
        CRUD::setEntityNameStrings('dataset', 'dataset');
        CRUD::orderBy('id', 'ASC');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('id');
        CRUD::column('text');
        CRUD::column('label');
        CRUD::addColumn([
            'name' => 'type',
            'label' => 'Tipe'
        ]);
        $this->crud->addButtonFromModelFunction('top', 'template_dataset', 'downloadTemplate', 'end');

        //Filter
        $this->crud->addFilter([
            'type' => 'dropdown',
            'name' => 'label',
            'label' => 'Label',
        ], [
            'senang' => 'Senang',
            'sedih' => 'Sedih',
            'marah' => 'Marah',
            'cinta' => 'Cinta',
            'takut' => 'Takut',
        ], function ($value) { // if the filter is active
            $this->crud->addClause('where', 'label', $value);
        });

        $this->crud->addFilter([
            'type' => 'dropdown',
            'name' => 'type',
            'label' => 'Tipe'
        ], [
            'training' => 'Training',
            'testing' => 'Testing',            
        ], function ($value) { // if the filter is active
            $this->crud->addClause('where', 'type', $value);
        });        
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(DatasetRequest::class);

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */

        CRUD::addField([   // Browse
            'name'      => 'dataset',
            'label'     => 'File Dataset',
            'type'      => 'upload',
            'upload'    => true,
            'disk'      => 'uploads'
        ]);

        CRUD::replaceSaveActions(
            [
                'name' => 'Upload',
                'visible' => function ($crud) {
                    return true;
                },
                'redirect' => function ($crud, $request, $itemId) {
                    return $crud->route;
                },
            ],
        );
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->crud->addField([
            'type' => 'textarea',
            'label' => 'Teks',
            'name' => 'text',
            'attributes' => [
                'rows' => 5,
            ]
        ]);
        $this->crud->addField([
            'type' => 'select_from_array',
            'label' => 'Label',
            'name' => 'label',
            'allows_null' => false,
            'options' => ['senang' => 'Senang', 'sedih' => 'Sedih', 'marah' => 'Marah', 'cinta' => 'Cinta', 'takut' => 'Takut']
        ]);

        $this->crud->addField([
            'type' => 'select_from_array',
            'label' => 'Tipe',
            'name' => 'type',
            'allows_null' => false,
            'options' => ['training' => 'Training', 'testing' => 'Testing']
        ]);
    }

    protected function setupShowOperation()
    {
        $this->crud->removeAllButtonsFromStack('line');
        $this->crud->denyAccess('delete');
    }

    public function store(DatasetRequest $request)
    {
        Excel::import(new DatasetsImport(), request()->file('dataset'));
        \Alert::add('success', 'Dataset sedang diproses')->flash();
        return \Redirect::to($this->crud->route);
    }

    public function update()
    {
        request()->validate([
            'text' => 'required|min:100|max:320'
        ], [
            'text.required' => 'Teks tidak boleh kosong!',
            'text.min' => 'Teks tidak boleh kurang dari 100 karakter!',
            'text.max' => 'Teks tidak boleh lebih dari 320 karakter!',
        ]);

        $pre_pro = PreprocessingService::index([request()->text]);
        $data = Dataset::where('id', request()->id)
            ->update([
                'text' => request()->text,
                'text_prepro' => $pre_pro[0]['result'],
                'label' => request()->label,
                'type' => request()->type
            ]);

        \Alert::add('success', 'Data berhasil diperbarui!')->flash();
        return \Redirect::to($this->crud->route);
    }
}
