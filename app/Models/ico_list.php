<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * This is a model class for 'cmc' table and only required to make database access easier
 **/
class ico_list extends Model
{

    protected $table = 'crypto_ico';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'crypto_id', 'unique_id','icoPriceUsd', 'currentStage','startdate','enddate','exchangeName','goal','contracts_name', 'launchpadUrl','name','symbol', 'slug','logo',
       'cmc_logo','status'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
