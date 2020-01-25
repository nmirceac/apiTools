<?php namespace ApiTools\Http\Middleware;

class ApiToolsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $request = request();
        $apiKey = $request->header('x-api-key');
        //$apiSecret = $request->header('x-api-secret');

        if(config('api.debug')==false) {
            if (! $apiKey) {
                $this->respondWithError('Missing API Key', 401)->send();
                exit();
            }

            if (config('api.secret') != $apiKey) {
                $this->respondWithError('Wrong API Key.', 403)->send();
                exit();
            } else {
                //$request->attributes->add(['application_id'=>$application->id]);
            }
        }

        $authId = $request->header('x-auth-id');
        if($authId) {
            $request->attributes->add(['auth_id'=>$authId]);
        }

        return $next($request);

    }

    public function respondWithError($message = 'Not authorized', $statusCode = 401)
    {
        $response = ['error' => [
            'message' => $message,
            'status_code' => $statusCode
        ]
        ];

        return response()->json($response, $statusCode);
    }
}
