<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\KeywordRequest;
use App\Models\Keyword;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class KeywordCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class KeywordCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Keyword::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/keyword');
        CRUD::setEntityNameStrings('keyword', 'keyword');
        $this->crud->denyAccess('show');
        CRUD::orderBy('status', 'DESC');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('keyword');
        CRUD::addColumn([
            'name' => 'status',
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
        CRUD::setValidation(KeywordRequest::class);


        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
        CRUD::addField([
            'name' => 'keyword',
            'label' => 'Keyword'
        ]);
        CRUD::replaceSaveActions(
            [
                'name' => 'Simpan Keyword',
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
            'name' => 'keyword',
            'label' => 'Keyword'
        ]);
        CRUD::addField([
            'type' => 'select_from_array',
            'name' => 'status',
            'label' => 'Status',
            'options' => [1 => 'Aktifkan', 0 => 'Tidak Aktif']
        ]);
        CRUD::replaceSaveActions(
            [
                'name' => 'Update Keyword',
                'visible' => function ($crud) {
                    return true;
                },
                'redirect' => function ($crud, $request, $itemId) {
                    return $crud->route;
                },
            ],
        );
    }

    public function store(KeywordRequest $request)
    {
        $status = Keyword::count() == 0 ? 1 : 0;

        //Save to DB
        Keyword::create([
            'keyword' => request()->keyword,
            'status' => $status
        ]);
        \Alert::add('success', 'Keyword berhasil dibuat')->flash();
        return \Redirect::to($this->crud->route);
    }

    public function update(KeywordRequest $request)
    {        
        $id = request()->id;
        $status = Keyword::find($id);
        if ($status->status == 1) {
            \Alert::add('danger', 'Tidak dapat mengubah keyword aktif!')->flash();
            return \Redirect::to($this->crud->route);
        }

        //Actived the new one        
        if (request()->status == 1) {
            Keyword::where('status', 1)
                ->update([
                    'status' => 0
                ]);
        }

        Keyword::find($id)
            ->update([
                'keyword' => request()->keyword,
                'status' => request()->status
            ]);

        \Alert::add('success', 'Keyword berhasil diperbarui!')->flash();
        return \Redirect::to($this->crud->route);
    }

    public function destroy($id)
    {
        $model_name = Keyword::find($id);
        //Cek aktif
        if ($model_name->status == 1) {
            \Alert::add('danger', 'Tidak dapat menghapus keyword aktif!')->flash();
            return false;
        }
        $this->crud->hasAccessOrFail('delete');

        return $this->crud->delete($id);
    }
}
