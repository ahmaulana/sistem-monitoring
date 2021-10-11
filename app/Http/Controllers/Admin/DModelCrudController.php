<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DModelRequest;
use App\Jobs\ModelJob;
use App\Models\Dataset;
use App\Models\DModel;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Storage;

/**
 * Class DModelCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DModelCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation {
        destroy as traitDestroy;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\DModel::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/dmodel');
        CRUD::setEntityNameStrings('Model', 'Model');
        CRUD::orderBy('actived', 'DESC');
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
        CRUD::addColumn([
            'name' => 'model_name',
            'label' => 'Model',
        ]);
        CRUD::addColumn([
            'name' => 'accuracy',
            'label' => 'Akurasi',
        ]);
        CRUD::addColumn([
            'name' => 'actived',
            'label' => 'Status',
            'type' => 'boolean',
            'options' => [0 => 'Tidak Aktif', 1 => 'Aktif']
        ]);
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(DModelRequest::class);

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */

        CRUD::addfield([
            'name' => 'model_name',
            'label' => 'Nama Model',
            'type' => 'text',
            'attributes' => [
                'placeholder' => 'Masukkan nama model'
            ]
        ]);

        CRUD::addfield([   // Textarea
            'name'  => 'model_desc',
            'label' => 'Deskripsi Model',
            'type'  => 'textarea'
        ]);

        CRUD::replaceSaveActions(
            [
                'name' => 'Buat Model',
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

        CRUD::addField([
            'name' => 'model_name',
            'label' => 'Nama Model',
            'attributes' => [
                'readonly' => 'readonly'
            ]
        ]);
        CRUD::addField([
            'type' => 'textarea',
            'name' => 'model_desc',
            'label' => 'Deskripsi',
        ]);
        CRUD::addField([
            'type' => 'select_from_array',
            'name' => 'actived',
            'label' => 'Status',
            'options' => [1 => 'Aktifkan', 0 => 'Tidak Aktif']
        ]);

        CRUD::replaceSaveActions(
            [
                'name' => 'Update Model',
                'visible' => function ($crud) {
                    return true;
                },
                'redirect' => function ($crud, $request, $itemId) {
                    return $crud->route;
                },
            ],
        );
    }

    protected function setupShowOperation()
    {
        $this->crud->removeAllButtonsFromStack('line');
        $this->crud->denyAccess('delete');
    }

    public function store(DmodelRequest $request)
    {
        //Check Dataset
        $dataset = Dataset::count();
        if($dataset){
            dispatch(new ModelJob(request()->all()));
            \Alert::add('success', 'Model sedang dibuat')->flash();
            return \Redirect::to($this->crud->route);
        } else {
            \Alert::add('danger', 'Dataset masih kosong')->flash();
            return \Redirect::to(backpack_url('dataset') . '/create');
        }
    }

    public function update($id)
    {
        request()->validate([
            'model_desc' => 'max:280'
        ],[
            'model_desc.max' => 'Panjang deskripsi maksimal 280 karakter!'
        ]);
        $status = DModel::find($id);
        if ($status->actived == 1) {
            \Alert::add('danger', 'Tidak dapat memperbarui model aktif!')->flash();
            return \Redirect::to($this->crud->route);
        }

        //Actived the new one        
        if (request()->actived == 1) {
            DModel::where('actived', 1)
                ->update([
                    'actived' => 0
                ]);
        }

        DModel::find($id)
            ->update([
                'model_desc' => request()->model_desc,
                'actived' => request()->actived
            ]);

        \Alert::add('success', 'Model berhasil diperbarui!')->flash();
        return \Redirect::to($this->crud->route);
    }

    public function destroy($id)
    {
        $model_name = DModel::select('model_name', 'actived')->where('id', $id)->first();
        //Cek aktif
        if ($model_name->actived == 1) {
            \Alert::add('danger', 'Tidak dapat menghapus model aktif!')->flash();
            return false;
        }

        unlink(storage_path() . '/model/' . $model_name->model_name . '.model');
        $this->crud->hasAccessOrFail('delete');

        return $this->crud->delete($id);
    }
}
