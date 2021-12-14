<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * This is a model class for 'cmc' table and only required to make database access easier
 **/
class ico_details extends Model
{

    protected $table = 'ico_details';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'crypto_id','extra_data', 'name', 'symbol','slug',
        'status',
       
    ];
    

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}