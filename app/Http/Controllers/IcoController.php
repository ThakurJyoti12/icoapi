<?php
namespace App\Http\Controllers;
use App\Models\ico_list as ico;
use App\Models\ico_details as ico_details;
use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Redis;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
class IcoController extends BaseController
{
    public static function save_logo_in_cloudinry($start = 1, $limit = 500){
        $http_response_header = array('Content-type' => 'application/json; charset=utf-8', 'Cache-Control' => 'public, max-age=0, s-max-age=300');
        $current_date = date("Y-m-d");
        $current_time = strtotime($current_date);
        $results = ico::select('*')->take($limit)->offset($start)->get();
        $data = array();
        $result = array();
        $no_of_icos = 0;
        foreach($results as $ico){
            $id = $ico['crypto_id'];
            $data['crypto_id']  = $id;
            $img = 'https://s2.coinmarketcap.com/static/img/coins/64x64/'.$id.'.png';
            $response = cloudinary()->upload($img, [
                'folder' => 'crypto-ico-logo',
                'public_id' => $id,
                'overwrite' => false])->getSecurePath();
            $no_of_icos++;
        }
        return Response()->json(array('Response' => 'Success', 'Message' => 'Cloudinary updated with ' . $no_of_icos . ' Logo.'), 200, array('Content-type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_SLASHES);
    }
    // Save Ico Data In Main Table(ICO) In DB (v2/ico/updatedb/status=Ongoing&start=1&limit=700)
    public static function save_icos_in_db($status='Upcoming',$start = 1, $limit = 500)
    {
        $http_response_header = array('Content-type' => 'application/json; charset=utf-8', 'Cache-Control' => 'public, max-age=0, s-max-age=300');
        $start = isset($_GET['start']) && !empty($_GET['start']) ? $_GET['start'] : $start;
        $limit = isset($_GET['limit']) && !empty($_GET['limit']) ? $_GET['limit'] : $limit;
        $status =  isset($_GET['status']) && !empty($_GET['status']) ? ucfirst($_GET['status']) :ucfirst($status);
        $start = $start <= 0 ? 1 : $start;
        $limit = $limit <= 0 ? 1 : $limit;
        if (!str_contains('Upcoming Ongoing Ended', $status)){
            return Response()->json(
                array('response' => 'Error', 'message' => 'You have passed wrong coin status')
                , 200,
                $http_response_header, JSON_UNESCAPED_SLASHES
            );
        }
        $url = 'https://api.coinmarketcap.com/data-api/v3/ico/search?status='.$status.'&start='.$start.'&limit='.$limit.'';
        $response = Http::get($url);
        //   $url =  'https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&order=gecko_desc&per_page=' . $per_page . '&page=' . $pageno . '&sparkline=false&price_change_percentage=24h%2C7d%2C30d%2C1y';
        $coins  = $response->json();
        $icos = $coins['data']['icoList'];
        $no_of_icos = 0;
        $ids = array();
        $data = array();
        if (isset($icos) && $icos != "" && is_array($icos)) {
            foreach ($icos as $ico) {
                $cryptoId  = isset($ico['cryptoId'])?$ico['cryptoId']:'';
                $icoPriceUsd = isset($ico['icoPriceUsd'])?$ico['icoPriceUsd']:'0';
                $currentStage = isset($ico['currentStage'])?$ico['currentStage']:'';
                $start_date = isset($ico['start'])?$ico['start']:'';
                $goalUsd = isset($ico['goalUsd'])?$ico['goalUsd']:'';
                $end_date = isset($ico['end'])?$ico['end']:'';
                $launchPad = $ico['launchPad'];
                $exchange_name = isset($launchPad['exchangeName'])?$launchPad['exchangeName']:'';
                $launchpadUrl = isset($launchPad['launchpadUrl'])?$launchPad['launchpadUrl']:'';
                $crypto = $ico['crypto'];
                $id = $crypto['id'];
                $name = isset($crypto['name'])?$crypto['name']:"";
                $symbol = isset($crypto['symbol'])?$crypto['symbol']:'';
                $slug  = isset($crypto['slug'])?$crypto['slug']:'';
                $logo = isset($crypto['logo'])?$crypto['logo']:'';
                $contracts_name = isset($crypto['contracts'][0]['name'])?$crypto['contracts'][0]['name']:'';
                $newstartDate = date("Y-m-d G:i:s", strtotime($start_date));
                $newEndDate =  date("Y-m-d G:i:s", strtotime($end_date));
                $cmc_logo = 'https://res.cloudinary.com/dgk6g13vx/image/upload/crypto-ico-logos/'.$cryptoId.'.png';
                if(!empty($start_date && $end_date)){
                    $get_start_date = strtotime($start_date);
                    $get_end_date = strtotime($end_date);
                    $current_date = date("Y-m-d H:i:s");
                    $current_time = strtotime($current_date);
                    if($get_start_date>$current_time){
                        $status = 'Upcoming';
                    }elseif($get_start_date<$current_time && $get_end_date>=$current_time){
                        $status = 'Ongoing';
                    }elseif($get_start_date==$current_time && $get_end_date==$current_time){
                        $status = 'Ongoing';
                    }elseif($get_end_date<= $current_time){
                        $status = 'Ended';
                    }
                    $unique_id=(int)$cryptoId+(int)$get_start_date+(int)$get_end_date+(int)$goalUsd;
                }else{
                    $whatIWant = substr($no_of_icos, strpos($no_of_icos, ")") + 0);
                    $get_end_date = '';
                    $get_start_date='';
                    if($status=='Upcoming'){
                        $j = 1;
                        $unique_id = $cryptoId.$j;
                    }elseif($status=='Ongoing'){
                        $k = 2;
                        $unique_id = $cryptoId.$whatIWant.$k;
                    }else{
                        $l = 3;
                        $unique_id = $cryptoId.$whatIWant.$l;
                    }
                }

                // if($status=='Upcoming'){
                //     $j = 1;
                //     $unique_id = $cryptoId.$whatIWant.$j;
                // }elseif($status=='Ongoing'){
                //     $k = 2;
                //     $unique_id = $cryptoId.$whatIWant.$k;
                // }else{
                //     $l = 3;
                //     $unique_id = $cryptoId.$whatIWant.$l;
                // }
                // VAR_DUMP($icoPriceUsd);
                $extra_data = json_encode($icos);
                ico::updateOrCreate(array('unique_id' => $unique_id),
                  array('crypto_id' => $cryptoId,'icoPriceUsd' => $icoPriceUsd, 'currentStage' => $currentStage,
                  'goal'=>$goalUsd,'contracts_name'=>$contracts_name, 'startdate' => $newstartDate, 'enddate' => $newEndDate,
                   'exchangeName' => $exchange_name, 'launchpadUrl' => $launchpadUrl,'name' => $name,'symbol' => strtoupper($symbol),
                          'slug'=>$slug,'logo' => $cmc_logo,'cmc_logo'=>$logo,'status' => $status,'created_at' => date('Y-m-d G:i:s'), 'updated_at' => date('Y-m-d G:i:s'))
                );
                $no_of_icos++;
            }
        } else {
            return Response()->json(array('Response' => 'Error', 'Message' => 'unable to connect to the api. Database update failed!'), 200, array('Content-type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_SLASHES);
        }
        return Response()->json(array('Response' => 'Success', 'Message' => 'Database updated with ' . $no_of_icos . ' icos.'), 200, array('Content-type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_SLASHES);
    }
    // Fetch Data in Main Table Of DB (ICO) db (http://localhost:8000/v2/ico/list/status=Ongoing&start=1&limit=700)
    public static function fetch_data_in_db($status='Upcoming',$start = 0, $limit = 500)
    {
        $http_response_header = array('Content-type' => 'application/json; charset=utf-8', 'Cache-Control' => 'public, max-age=0, s-max-age=300');
        $start = isset($_GET['start']) && !empty($_GET['start']) ? $_GET['start'] : $start;
        $limit = isset($_GET['limit']) && !empty($_GET['limit']) ? $_GET['limit'] : $limit;
        $status =  isset($_GET['status']) && !empty($_GET['status']) ? ucfirst($_GET['status']) :ucfirst($status);
        $start = $start < 0 ? 0 : $start;
        $limit = $limit < 0 ? 1 : $limit;
        $cache_key = 'v2-fetchdata-'.$status.'-'.$start.'-'.$limit;
        if (Cache::has($cache_key)) {
             $response = Cache::get($cache_key);
             return Response()->json($response,
              200,
              $http_response_header,
              JSON_UNESCAPED_SLASHES );
         }
        if (!str_contains('Upcoming Ongoing Ended', $status)){
            return Response()->json(
                array('response' => 'Error', 'message' => 'You have passed wrong coin status')
                , 200,
                $http_response_header, JSON_UNESCAPED_SLASHES
            );
        }
        $results = ico::select('*')->where([
            ['status', '=', $status],
        ])->take($limit)->offset($start)->get();
        $result = array();
        foreach($results as $ico){
            $data['crypto_id']  = $ico['crypto_id'];
            $data['unique_id'] = $ico['unique_id'];
            $data['icoPriceUsd'] = $ico['icoPriceUsd'];
            $data['currentStage'] = isset($ico['currentStage'])?$ico['currentStage']:'';
            $data['start_date'] = isset($ico['startdate'])?$ico['startdate']:'';
            $data['end_date'] = isset($ico['enddate'])?$ico['enddate']:'';
            $data['contracts_name'] = isset($ico['contracts_name'])?$ico['contracts_name']:'';
            $data['goal'] = isset($ico['goal'])?$ico['goal']:'';
            $launchPad = $ico['launchPad'];
             $data['exchange_name'] = isset($ico['exchangeName'])?$ico['exchangeName']:'';
             $data['launchpadUrl'] = isset($ico['launchpadUrl'])?$ico['launchpadUrl']:'';
            $data['name'] = isset($ico['name'])?$ico['name']:"";
            $data['symbol'] = isset($ico['symbol'])?$ico['symbol']:'';
             $data['slug']  = isset($ico['slug'])?$ico['slug']:'';
             $data['logo'] = isset($ico['logo'])?$ico['logo']:'';
             $data['status'] = isset($ico['status'])?$ico['status']:'';
            $result[]=$data;
        }
        /*
        *  Cache::put takes 3rd argument for cache expiry.
        *  provide minutes for cache to expire
        */
        Cache::put($cache_key, $result, 5*60);
        return Response()->json($result,
        200,
        $http_response_header,
         JSON_UNESCAPED_SLASHES
       );
    }
    /**
     * This function is used to save all ico single page data in db
     * Url used v2/ico/list/singlepage
     */
    public static function save_single_page_data_in_db(){
        $url = 'https://ico-list-json.herokuapp.com/';
        $response = Http::get($url);
        $data = $response->json();
        $single_page_data = $data;
        $i = 0;
        if (isset($single_page_data) && $single_page_data != "" && is_array($single_page_data)) {
            foreach ($single_page_data as $key=>$ico) {
                $status = $ico['info']['ico']['status'];
                $arrSku = $ico['info'];
                unset($arrSku["ico"]["stages"]);
                $info = json_encode($arrSku);
                $common_id = $ico['info']['id'];
                $name = $ico['info']['name'];
                $symbol = $ico['info']['symbol'];
                $slug = $ico['info']['slug'];
                // ico_details::updateOrCreate(array('common_id' => $common_id));
                // {"single": "Towel", "many": ["Toilet Paper", "Mirror", "Soap"]}
                ico_details::updateOrCreate(array('crypto_id' => $common_id),
                    array('name' => $name,'symbol' => $symbol,'status'=>$status,'slug'=>$slug,'extra_data'=>$info,'created_at' => date('Y-m-d G:i:s'), 'updated_at' => date('Y-m-d G:i:s'))
                );
              $i++;
            }
        }else {
            return Response()->json(array('Response' => 'Error', 'Message' => 'unable to connect to the api. Database update failed!'), 200, array('Content-type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_SLASHES);
        }
        return Response()->json(array('Response' => 'Success', 'Message' => 'Database updated with ' . $i . ' single page icos.'), 200, array('Content-type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_SLASHES);
    }
    /**
     * This function is used to  Save particular single page id data in db
     * Url usedhttps://api.cryptocurrencyxyz.com/public/v2/ico/search/status=Ended&crypto_id=12614
     */
    public static function save_single_page_particular_id_data_in_db($status='Upcoming',$crypto_id=''){
        $http_response_header = array('Content-type' => 'application/json; charset=utf-8', 'Cache-Control' => 'public, max-age=0, s-max-age=300');
        $status =  isset($_GET['status']) && !empty($_GET['status']) ? ucfirst($_GET['status']) :ucfirst($status);
        $crypto_id =  isset($_GET['crypto_id']) && !empty($_GET['crypto_id']) ? $_GET['crypto_id'] :$crypto_id;
        if (!str_contains('Upcoming Ongoing Ended All', $status)){
            return Response()->json(
                array('response' => 'Error', 'message' => 'You have passed wrong coin status')
                , 200,
                $http_response_header, JSON_UNESCAPED_SLASHES
            );
        }
        if(empty($crypto_id)){
            return Response()->json(
                array('response' => 'Error', 'message' => 'Please add crypto id.')
                , 200,
                $http_response_header, JSON_UNESCAPED_SLASHES
            );
        }
        $url = 'https://ico-list-json.herokuapp.com/search?status='.$status.'&crypto_id='.$crypto_id.'';
        $response = Http::get($url);
        $data  = $response->json();
        $single_page_data = $data;
        $i = 0;
        if (isset($single_page_data) && $single_page_data != "" && is_array($single_page_data)) {
            foreach ($single_page_data as $key=>$ico) {
                $status = $ico['info']['ico']['status'];
                $arrSku = $ico['info'];
                //Convert array to json form...
                $info = json_encode($arrSku);
                $common_id = $ico['info']['id'];
                $name = $ico['info']['name'];
                $symbol = $ico['info']['symbol'];
                $slug = $ico['info']['slug'];
                // ico_details::updateOrCreate(array('common_id' => $common_id));
                // {"single": "Towel", "many": ["Toilet Paper", "Mirror", "Soap"]}
                ico_details::updateOrCreate(array('crypto_id' => $common_id),
                    array('name' => $name,'symbol' => $symbol,'slug'=>$slug,'status'=>$status,'extra_data'=>$info,'created_at' => date('Y-m-d G:i:s'), 'updated_at' => date('Y-m-d G:i:s'))
                );
              $i++;
            }
        }else {
            return Response()->json(array('Response' => 'Error', 'Message' => 'unable to connect to the api. Database update failed!'), 200, array('Content-type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_SLASHES);
        }
        return Response()->json(array('Response' => 'Success', 'Message' => 'Database updated with ' . $i . ' single page icos.'), 200, array('Content-type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_SLASHES);
    }
    /**
     * Fetch single page data in db based on crypto_id
     * Url is usedhttps://api.cryptocurrencyxyz.com/public/v2/ico/search/singlepage/slug=Metapad
     */
    public static function fetch_single_page_data_in_db($slug){
        $result = array();
        $cache_key = 'v2-ico-single-page-data-'.$slug.'';
        $http_response_header = array('Content-type' => 'application/json; charset=utf-8', 'Cache-Control' => 'public, max-age=0, s-max-age=300');
        $slug =  isset($_GET['slug']) && !empty($_GET['slug']) ? $_GET['crypto_id']:$slug;
        if (Cache::has($cache_key)) {
            $response = Cache::get($cache_key);
            return Response()->json($response,
             200,
             $http_response_header,
             JSON_UNESCAPED_SLASHES );
        }
        if (empty($slug)){
            return Response()->json(
                array('response' => 'Error', 'message' => 'Crypto Slug is Missing')
                , 200,
                $http_response_header, JSON_UNESCAPED_SLASHES
            );
        }
        $results = ico_details::select('*')->where([
            ['slug', '=', $slug],
        ])->get();
        foreach($results as $ico){
            $data['crypto_id']  = $ico['crypto_id'];
            $data['name'] = isset($ico['name'])?$ico['name']:"";
            $data['symbol'] = isset($ico['symbol'])?$ico['symbol']:'';
            $data['status']  = isset($ico['status'])?$ico['status']:'';
            $data['slug']  = isset($ico['slug'])?$ico['slug']:'';
            $data['extra_data'] = isset($ico['extra_data'])?$ico['extra_data']:'';
            $result[]=$data;
        }
        if (isset($result) && $result != "" && is_array($result)&&count($result)>0) {
            Cache::put($cache_key, $result, 5*60);
            return Response()->json($result,
                200,
                $http_response_header,
                JSON_UNESCAPED_SLASHES
            );
        }else{
            return Response()->json(array('Response' => 'Error', 'Message' => 'Given Slug Data Is Not Found in Database'), 200, array('Content-type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_SLASHES);
        }
    }
    public static function fetch_ids_from_db(){
        $current_date = date("Y-m-d");
        $current_time = strtotime($current_date);
        $results = ico::select('*')->where('created_at', 'LIKE', $current_date.'%')
        ->where('status', '!=', 'Ended')->get();
        // var_dump($results);
        $data = array();
        $result = array();
        foreach($results as $ico){
            // var_dump($ico);
            $id = $ico['crypto_id'];
            $slug = $ico['slug'];
            //$data['crypto_id']  = $id;
            $data['slug']  = $slug;
            $result[] = $data;
        }
        $final_id = implode(',', array_map(function ($entry) {
            return ($entry[key($entry)]);
          }, $result));
        //   var_dump($final_id);
        return $final_id;
    }
    public static function save_latest_id_logo_in_db(){
        $no_of_logos = 0;
        $current_date = date("Y-m-d");
        $current_time = strtotime($current_date);
        $results = ico::select('*')->where('created_at', 'LIKE', $current_date.'%')
        ->where('status', '!=', 'Ended')->get();
        $data = array();
        $result = array();
        foreach($results as $ico){
            $id = $ico['crypto_id'];
            $data['crypto_id']  = $id;
            $result[] = $data;
            $img = 'https://s2.coinmarketcap.com/static/img/coins/64x64/'.$id.'.png';
            $response = cloudinary()->upload($img, [
                'folder' => 'crypto-ico-logo',
                'public_id' => $id,
                'overwrite' => false
            ])->getSecurePath();
        $no_of_logos++;
        }
        if($no_of_logos==0){
            return Response()->json(array('Response' => 'Error', 'Message' => 'There is no new id found'), 200, array('Content-type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_SLASHES);
        }else{
            return Response()->json(array('Response' => 'Success', 'Message' => 'Cloudinary updated with ' . $no_of_logos . ' Logo.'), 200, array('Content-type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_SLASHES);
        }
    }
    public static function save_particular_id_in_db(){
        $final_id = IcoController::fetch_ids_from_db();
        if(empty($final_id)){
            return Response()->json(array('Response' => 'Error', 'Message' => 'There is no new id found'), 200, array('Content-type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_SLASHES);
        }
        $url = 'https://ico-list-json.herokuapp.com/search?slug='.$final_id.'';
        $response = Http::get($url);
        $data  = $response->json();
        $single_page_data = $data;
        $i = 0;
        if (isset($single_page_data) && $single_page_data != "" && is_array($single_page_data)) {
            foreach ($single_page_data as $key=>$ico) {
                $status = $ico['info']['ico']['status'];
                $arrSku = $ico['info'];
                //Convert array to json form...
                $info = json_encode($arrSku);
                $common_id = $ico['info']['id'];
                $name = $ico['info']['name'];
                $symbol = $ico['info']['symbol'];
                $slug = $ico['info']['slug'];
                // ico_details::updateOrCreate(array('common_id' => $common_id));
                // {"single": "Towel", "many": ["Toilet Paper", "Mirror", "Soap"]}
                ico_details::updateOrCreate(array('crypto_id' => $common_id),
                    array('name' => $name,'symbol' => $symbol,'slug'=>$slug,'status'=>$status,'extra_data'=>$info,'created_at' => date('Y-m-d G:i:s'), 'updated_at' => date('Y-m-d G:i:s'))
                );
              $i++;
            }
        }else {
            return Response()->json(array('Response' => 'Error', 'Message' => 'unable to connect to the api. Database update failed!'), 200, array('Content-type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_SLASHES);
        }
        return Response()->json(array('Response' => 'Success', 'Message' => 'Database updated with ' . $i . ' single page icos.'), 200, array('Content-type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_SLASHES);
    }
    /**
     * Update ico status
     */
    public static function refresh_database(){
        $http_response_header = array('Content-type' => 'application/json; charset=utf-8', 'Cache-Control' => 'public, max-age=0, s-max-age=300');
        $current_date = date("Y-m-d G:i:s");
        $current_time = strtotime($current_date);
        $results =  ico::where('startdate', '<', $current_date)
          ->where('enddate', '>=', $current_date)
           ->where('status', '!=', 'Ongoing')
          ->get();
          $found_something =   isset($results[0]['crypto_id'])?$results[0]['crypto_id']:'';
        if(!empty($found_something)){
            $results =  ico::where('startdate', '<', $current_date)
            ->where('enddate', '>=', $current_date)
            ->where('status', '!=', 'Ongoing')
            ->update(['status' => 'Ongoing']);
        }
        $results =  ico::where('startdate', '=', $current_date)
        ->where('enddate', '=', $current_date)
         ->where('status', '!=', 'Ongoing')
        ->get();
        $found_something =   isset($results[0]['crypto_id'])?$results[0]['crypto_id']:'';
        if(!empty($found_something)){
         $results =  ico::where('startdate', '=', $current_date)
           ->where('enddate', '=', $current_date)
           ->update(['status' => 'Ongoing']);
        }
        $results =  ico::where('enddate', '<', $current_date)
        ->where('status', '!=', 'Ended')
        ->get();
        $found_something =   isset($results[0]['crypto_id'])?$results[0]['crypto_id']:'';
        if(!empty($found_something)){
        $results = ico::where('enddate', '<', $current_date)
         ->update(['status' => 'Ended']);
        }
        $results =  ico::where('startdate', '=', '1970-01-01 01:00:00')
        ->where('enddate', '=', '1970-01-01 01:00:00')
        ->where('status', '!=', 'Upcoming')
        ->get();
        $found_something =   isset($results[0]['crypto_id'])?$results[0]['crypto_id']:'';
        if(!empty($found_something)){
         $results =  ico::where('startdate', '=', '1970-01-01 01:00:00')
         ->where('enddate', '=', '1970-01-01 01:00:00')
         ->update(['status' => 'Upcoming']);
        }
        return Response()->json(array('Response' => 'Success', 'Message' => 'All ICO Status is updated'), 200, array('Content-type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_SLASHES);
    }
    /**
     *
     * This function will flush entire website's cache (REDIS).
     * It must be used with caution and should not called unnecessarily
     *
     * @example '/v2/refresh-api/'
     *
     * @return string message about successfully fushing cache
     *
     **/
    public static function clear_all_cache(){
        $http_response_header   =   array('Content-type'=> 'application/json; charset=utf-8','Cache-Control'=>'public, max-age=0, s-max-age=180');
        Cache::flush();
        $response = "Entire API cache is flushed successfully!";
        return Response()->json($response,
                                 200,
                                 $http_response_header,
                                  JSON_UNESCAPED_SLASHES
                                );
    }
}
