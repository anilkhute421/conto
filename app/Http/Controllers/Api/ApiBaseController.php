<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\TenantApiRepository;
use Illuminate\Http\Request;

class ApiBaseController extends Controller
{

    // public function __construct(Request $request) {
	 	
	// 	\App::setLocale($_SERVER['HTTP_LANG']);

	// }

        
    /**
     * sendResponse
     *
     * @param  mixed $result
     * @param  mixed $message
     * @param  mixed $status
     * @return void
     */
    public function sendResponse($result,$message,$status =200, $code = 200)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
            'status'  => $status
        ];
        //\Log::emergency('before sendResponse');
        return response()->json($response,$code);
    }

  
    
    /**
     * sendSingleFieldError
     *
     * @param  mixed $error
     * @param  mixed $code
     * @param  mixed $errorMessages
     * @return void
     */
    public function sendSingleFieldError($error,$status,$code)
    {
        $response = [
            'success' => false,
            'message' => $error,
            'status'  => $status
        ];
        return response()->json($response,$code);
    }
}
