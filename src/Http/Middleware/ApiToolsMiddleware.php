<?php namespace ApiTools\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiToolsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $requestCheckRequired = true;

        if(config('api.debug')) {
            $requestCheckRequired = false;
        }

        if(config('api.allowAuthenticatedRequests') and \Auth::id()) {
            $requestCheckRequired = false;
        }

        if($requestCheckRequired) {
            $requestCheck = $this->checkRequestHeaders($request);
            if($requestCheck) {
                return $requestCheck;
                exit();
            }
        }

        $agent = $request->header('x-agent');
        if($agent) {
            $request->attributes->add(['agent'=>$agent]);
        }

        $identifier = $request->header('x-identifier');
        if($identifier) {
            $request->attributes->add(['identifier'=>$identifier]);
        }

        $authId = $request->header('x-auth-id');
        if($authId) {
            $request->attributes->add(['auth_id'=>(int) $authId]);

            $impersonatorId = $request->header('x-auth-impersonator-id');
            if($impersonatorId) {
                $request->attributes->add(['auth_impersonator_id'=>(int) $impersonatorId]);
            }
        }

        $locale = $request->header('x-locale');
        if($locale) {
            \App::setLocale($locale);
        }

        if(config('api.logging.enabled') and config('api.logging.model')) {
            $loggingModel = config('api.logging.model');
            if(!class_exists($loggingModel)) {
                return $this->respondWithError('Unable to log the api request to class '.$loggingModel, 404);
            }
            $loggingModel::log($request);
        }

        return $next($request);
    }

    public function checkRequestHeaders($request)
    {
        $apiKey = $request->header('x-api-key');

        if (! $apiKey) {
            return $this->respondWithError('Missing API Key', 401);
        }

        if (config('api.secret') != $apiKey) {
            return $this->respondWithError('Wrong API Key.', 403);
        } else {
            //$request->attributes->add(['application_id'=>$application->id]);
        }

        return null;
    }

    public function respondWithError($message = 'Not authorized', $statusCode = 401)
    {
        $response = ['error' =>
            [
                'message' => $message,
                'status_code' => $statusCode
            ]
        ];

        return response()->json($response, $statusCode);
    }
}
