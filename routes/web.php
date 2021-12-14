<?php

/** @var \Laravel\Lumen\Routing\Router $router */
use App\Http\Controllers\IcoController;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->group(['prefix' => 'v2'], function () use ($router) {
    // Save Data in db https://api.cryptocurrencyxyz.com/public/v2/ico/updatedb/status=Ongoing&start=1&limit=700
    $router->get('ico/updatedb/status={status}&start={start}&limit={limit}', function($status,$start,$limit){
        return IcoController::save_icos_in_db( $status,$start, $limit );
    } );
    // Fetch Data in db https://api.cryptocurrencyxyz.com/public/v2/ico/list/status=Ongoing&start=1&limit=700
    $router->get('ico/list/status={status}&start={start}&limit={limit}', function($status,$start,$limit){
        return IcoController::fetch_data_in_db( $status,$start, $limit );
    } );
    // Save all single page data in db v2/ico/list/singlepage
     $router->get('ico/list/singlepage', function(){
        return IcoController::save_single_page_data_in_db();
    } );
    // Logo Update
    $router->get('ico/update/logo/start={start}/limit={limit}', function($start,$limit){
        return IcoController::save_logo_in_cloudinry($start,$limit);
    } );
    $router->get('ico/update/logo/latest_id', function(){
        return IcoController::save_latest_id_logo_in_db();
    } );
    // Save particular single page id data in db https://api.cryptocurrencyxyz.com/public/v2/ico/search/status=Ended&crypto_id=12614,123,456
     $router->get('ico/search/status={status}&crypto_id={crypto_id}', function($status,$crypto_id){
         return IcoController::save_single_page_particular_id_data_in_db( $status,$crypto_id);
     } );
    // Fetch single page data from db based on slug https://api.cryptocurrencyxyz.com/public/v2/ico/list/singlepage/slug='dragon-kart'
    $router->get('ico/search/singlepage/slug={slug}', function($slug){
        return IcoController::fetch_single_page_data_in_db( $slug);
    } );
    $router->get('ico/search/latest/id', function(){
        return IcoController::save_particular_id_in_db();
    } );
    $router->get('refresh-db',function(){
        return IcoController::refresh_database();
    });
    $router->get('help', function(){
        $ApiEndPoints = array(
                    'Commands'=>array(
                    array('EndPoint'=>'ico/updatedb/status=Ongoing&start=1&limit=700',
                        'Description'=>'Save all icos data in database.You can set status Upcoming,Ongoing,Ended',
                        'Example'=>'https://api.cryptocurrencyxyz.com/public/v2/ico/updatedb/status=Ongoing&start=1&limit=700'
                    ),
                    array('EndPoint'=>'ico/list/status={status}&start={start}&limit={limit}',
                        'Description'=>'Get all icos data.You can set status Upcoming,Ongoing,Ended',
                        'Example'=>'https://api.cryptocurrencyxyz.com/public/v2/ico/list/status=Ongoing&start=1&limit=700',
                    ),

                    array('EndPoint'=>'ico/search/status={status}&crypto_id={crypto_id}',
                        'Description'=>'Save particular id or multiple ids data in db.You can set status Upcoming,Ongoing,Ended,All.All status is used if your all id status is not same.',
                        'Example'=>array('https://api.cryptocurrencyxyz.com/public/v2/ico/search/status=All&crypto_id=12614,123,456',
                        'https://api.cryptocurrencyxyz.com/public/v2/ico/search/status=Ended&crypto_id=12614',
                        'https://api.cryptocurrencyxyz.com/public/v2/ico/search/status=Upcoming&crypto_id=12614,123,456'
                        )
                    ),
                    array('EndPoint'=>'ico/search/singlepage/slug={slug}',
                        'Description'=>'Fetch single page data from db based on slug',
                        'Example'=>'https://api.cryptocurrencyxyz.com/public/v2/ico/list/singlepage/slug=dragon-kart'
                    ),
                    array('EndPoint'=>'refresh-db',
                    'Description'=>'This function will update status of icos based on start date and end date (REDIS)',
                    'Example'=>'https://api.cryptocurrencyxyz.com/public/v2/refresh-db'
                ),
                    array('EndPoint'=>'refresh-api',
                        'Description'=>'This function will flush entire website cache (REDIS)',
                        'Example'=>'https://api.cryptocurrencyxyz.com/public/refresh-api'
                    )
                ));
            return Response()->json( $ApiEndPoints ,200,
        array('Content-type'=> 'application/json; charset=utf-8'),
        JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    } );
});
$router->get('refresh-api', [ 'uses'=>'IcoController@clear_all_cache' ]);
