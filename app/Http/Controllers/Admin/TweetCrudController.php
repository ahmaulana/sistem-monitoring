<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TweetRequest;
use App\Models\Tweet;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Illuminate\Support\Facades\Auth;

/**
 * Class TweetCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TweetCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Tweet::class);
        CRUD::setRoute('/tweet');
        CRUD::setEntityNameStrings('tweet', 'tweet');
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
        CRUD::column('tweet');
        CRUD::addColumn([            
            'name'         => 'classification',
            'type'         => 'relationship',
            'label'        => 'Emosi',            
            'attribute' => 'emotion',            
        ]);
        CRUD::column('created_at');
        $this->crud->denyAccess('update');
        if (backpack_auth()->guest()) {
            $this->crud->denyAccess('delete');
        }
        $this->crud->denyAccess('create');

        // Filter Label
        $this->crud->addFilter(
            [
                'type' => 'dropdown',
                'name' => 'emotion',
                'label' => 'Emosi',
            ],
            function () {
                return Tweet::join('classifications', 'tweets.id', '=', 'tweet_id')->distinct()->get()->pluck('emotion', 'emotion')->toArray();
            },
            function ($value) {                
                $query = Tweet::join('classifications','tweets.id','=','tweet_id')->where('emotion',$value);
                return $this->crud->query = $query;
            }
        );

        // Filter Tanggal
        $this->crud->addFilter(
            [
                'type' => 'date_range',
                'name' => 'created_at',
                'label' => 'Filter Tanggal',
            ],
            false,
            function ($value) {                
                $dates = json_decode($value);
                $this->crud->addClause('where', 'created_at', '>=', $dates->from);
                $this->crud->addClause('where', 'created_at', '<=', $dates->to);
            }
        );
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(TweetRequest::class);

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupShowOperation()
    {
        $this->crud->removeAllButtonsFromStack('line');
        $this->crud->set('show.setFromDb', false);
        $this->crud->addColumn([
            'name' => 'post_id',
            'label' => 'Tweet ID'
        ]);
        $this->crud->addColumn([
            'name' => 'username',
            'label' => 'Username'
        ]);
        $this->crud->addColumn([
            'name' => 'tweet',
            'label' => 'Tweet'
        ]);
        $this->crud->addColumn([
            'name' => 'tweet_prepro',
            'label' => 'Tweet Prepro'
        ]);                
        CRUD::addColumn([
            // any type of relationship
            'name'         => 'classification', // name of relationship method in the model
            'type'         => 'relationship',
            'label'        => 'Emosi', // Table column heading
            // OPTIONAL
            // 'entity'    => 'detail_tweet', // the method that defines the relationship in your Model
            'attribute' => 'emotion', // foreign key attribute that is shown to user
            // 'model'     => App\Models\DetailTweet::class, // foreign key model
        ]);

        $this->crud->addColumn([
            'name' => 'created_at',
            'label' => 'Tanggal'
        ]);
        
        $this->crud->denyAccess('delete');
    }        
}
