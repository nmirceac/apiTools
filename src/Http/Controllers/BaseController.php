<?php namespace ApiTools\Http\Controllers;

class BaseController
{
    public $class;
    protected $keyName = 'id';
    protected $itemName = 'item';

    protected $orderAsc = true;
    protected $orderBy = null;

    protected $itemsPerPage = 15;

    protected $singleRelationships = [];
    protected $multipleRelationships = [];

    protected $singleAppends = [];
    protected $multipleAppends = [];

    protected $searchColumns = [];

    /**
     * @apiDescription Returns items list
     * @apiSupportsPagination
     * @apiRequestParamPage 1
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index()
    {
        if (!empty($this->multipleAppends)) {
            $this->class::setStaticAppends($this->multipleAppends);
        }

        return $this->sendResponse($this->getSearchQuery()->paginate(request('itemsPerPage', $this->itemsPerPage)));
    }

    /**
     * @apiDescription Returns one item
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(int $id)
    {
        $hasSoftDeletes = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses($this->class));

        if ($hasSoftDeletes) {
            $i = $this->class::withTrashed()->with($this->singleRelationships);
        } else {
            $i = $this->class::with($this->singleRelationships);
        }

        $i = $i->find($id);

        if (!$i) {
            return $this->sendError('Not Found');
        }

        $i->append($this->singleAppends);

        if (method_exists($this, 'beforeGet')) {
            $this->beforeGet($i);
        }

        return $this->sendResponse($i);
    }

    /**
     * @apiDescription Returns search results
     * @param bool $trashedOnly
     * @return mixed
     */
    public function searchQuery(bool $trashedOnly = false)
    {
        $results = $this->class::query();

        if ($trashedOnly) {
            $results->onlyTrashed();
        }

        $results->with($this->multipleRelationships);

        return $results;
    }

    /**
     * @apiDescription Returns the search query
     * @param bool $trashedOnly
     * @return mixed
     * @throws \Exception
     */
    private function getSearchQuery(bool $trashedOnly = false)
    {
        if (method_exists($this, 'searchQuery')) {
            $results = $this->searchQuery($trashedOnly);
        } else {
            throw new \Exception('No searchQuery method defined');
        }

        if (method_exists($this, 'beforeSearch')) {
            $this->beforeSearch();
        }

        $orderBy = $this->orderBy ? $this->orderBy : $this->keyName;
        if ($this->orderAsc) {
            $results->orderBy($orderBy, 'ASC');
        } else {
            $results->orderBy($orderBy, 'DESC');
        }

        return $results;
    }

    private static function allowIpForRay($ip)
    {
        $allow = true;

        if(!empty(config('api.ray_ip_whitelist')) and !in_array($ip, config('api.ray_ip_whitelist'))) {
            $allow = false;
        }

        if(!empty(config('api.ray_ip_blacklist')) and in_array($ip, config('api.ray_ip_blacklist'))) {
            $allow = false;
        }

        return $allow;
    }

    /**
     * success response method.
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendResponse($data)
    {
        $response = [
            'success' => true,
            'data' => $data
        ];

        if(config('api.ray') and function_exists('ray')) {
            $ip = request()->ip();
            $allow = self::allowIpForRay($ip);

            if($allow) {
                $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4);
                $significantTrace = $trace[1];
                $title = self::getTitlePartsFromSignificantTrace('API RESPONSE', $significantTrace);

                $rayPayload = [
                    'success' => true,
                    'code' => 200,
                    'data' => $data,
                    'ip'=>implode(', ', request()->ips()),
                    'trace' => $trace,
                ];

                ray(implode(' - ', $title), $rayPayload);
            }
        }

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @param $error
     * @param array $errorMessages
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        if(config('api.ray') and function_exists('ray')) {
            $ip = request()->ip();
            $allow = self::allowIpForRay($ip);

            if($allow) {
                $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4);
                $significantTrace = $trace[1];
                $title = self::getTitlePartsFromSignificantTrace('API RESPONSE', $significantTrace);

                ray(implode(' - ', $title), [
                    'success' => false,
                    'code' => $code,
                    'error' => $error,
                    'errorMessages' => $errorMessages,
                    'ip'=>implode(', ', request()->ips()),
                    'trace' => $trace,
                ])->red();
            }
        }

        return response()->json($response, $code);
    }

    /**
     * Return titles from label and trace information
     * @param $label
     * @param $significantTrace
     * @return array
     */
    public static function getTitlePartsFromSignificantTrace($label, $significantTrace)
    {
        $title[] = strtoupper(config('app.name'));
        $title[] = $label;
        if(isset($significantTrace['class']) and isset($significantTrace['function'])) {
            $title[] = $significantTrace['class'];
            $title[] = $significantTrace['function'];

            if(isset($significantTrace['args']) and !empty($significantTrace['args'])) {
                foreach($significantTrace['args'] as $arg) {
                    if(empty($arg) or is_array($arg)) {
                        continue;
                    }
                    $title[] = $arg;
                }
            }
        }

        return $title;
    }

    protected static function getRayRequestData()
    {
        $request = request()->all();
        foreach($request as $param=>$value) {
            if(is_array($value)) {
                $request[$param] = self::getRayRequestDataArray($value);
            }
        }

        return $request;
    }

    private static function getRayRequestDataArray($array)
    {
        $maskValuesParam = [
            'password',
            'Password',
            'urlBase64',
            'content',
        ];

        foreach($array as $param=>$value) {
            if(is_array($value)) {
                $request[$param] = self::getRayRequestDataArray($value);
            }

            if(in_array($param, $maskValuesParam)) {
                $request[$param] = '...';
            }
        }

        return $array;
    }

    /**
     * Acknowledge response method.
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendAck($data = [])
    {
        $response = [
            'success' => true,
            'ack' => true
        ];

        if(!empty($data)) {
            $response['data'] = $data;
        }

        if(config('api.ray') and function_exists('ray')) {
            $ip = request()->ip();
            $allow = self::allowIpForRay($ip);

            if($allow) {
                $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4);
                $significantTrace = $trace[1];
                $title = self::getTitlePartsFromSignificantTrace('API RESPONSE', $significantTrace);

                $log = null;
                if(config('api.ray_log_class')) {
                    $class = config('api.ray_log_class');
                    try {
                        $log = $class::getLabel(request('type'));
                    } catch (\Exception $e) {
                        $log = null;
                    }

                    if($log and request('user_id')) {
                        $log.=' - '.request('user_id');
                    }
                }

                $rayPayload = [
                    'success' => true,
                    'code' => 200,
                    'data' => $data,
                    'request' => self::getRayRequestData(),
                    'ip'=>implode(', ', request()->ips()),
                    'trace' => $trace,
                ];


                if($log) {
                    $rayPayload = array_merge(['log'=>$log], $rayPayload);
                }

                ray(implode(' - ', $title), $rayPayload)->green();
            }
        }

        return response()->json($response, 200);
    }

    public static function authId()
    {
        return request()->attributes->get('auth_id');
    }

    public static function authImpersonatorId()
    {
        return request()->attributes->get('auth_impersonator_id');
    }

    protected function getApiAuthId()
    {
        return self::authId();
    }

    protected function getApiAuthImpersonatorId()
    {
        return self::authImpersonatorId();
    }

    protected function getApiAuthUser()
    {
        return \App\User::find($this->getApiAuthId());
    }

    /**
     * @apiDescription Add images
     * @param int $id
     * @apiPostParamImages images
     * @return \Illuminate\Http\JsonResponse
     */
    public function addImages(int $id)
    {
        $i = $this->class::find($id);

        $payload = request()->all();

        $imagesPayload = $this->imagesDetect($payload);

        if(is_null($i)) {
            return $this->sendError(ucfirst($this->itemName).' not found', [], 404);
        }

        if($imagesPayload) {
            $this->imagesAttach($i, $imagesPayload);
        } else {
            return $this->sendError('No images sent', [], 404);
        }

        return $this->sendAck();
    }

    /**
     * @apiDescription Get all images - supports the optional role parameter with the request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImages(int $id)
    {
        $i = $this->class::find($id);

        $payload = request()->all();

        $imagesPayload = $this->imagesDetect($payload);

        if(is_null($i)) {
            return $this->sendError(ucfirst($this->itemName).' not found', [], 404);
        }

        $role = request('role', 'images');

        return $this->sendResponse($i->imagesByRole($role)->get());
    }

    /**
     * @apiDescription Get paginated images - supports the optional role parameter with the request
     * @param int $id
     * @param int $page
     * @param int $itemsPerPage
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImagesPaginated(int $id, int $page, int $itemsPerPage=12)
    {
        $i = $this->class::find($id);

        $payload = request()->all();

        $imagesPayload = $this->imagesDetect($payload);

        if(is_null($i)) {
            return $this->sendError(ucfirst($this->itemName).' not found', [], 404);
        }

        $role = request('role', 'images');

        $pagination = $i->imagesByRole($role)
            ->paginate($itemsPerPage, ['*'], 'page', $page)
            ->withPath('');

        return $this->sendResponse($pagination);
    }

    /**
     * @apiDescription Update the images' order
     * @param int $id
     * @apiPostParamImagesIds ids
     * @apiPostParamRole role
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorderImages(int $id)
    {
        $i = $this->class::find($id);
        if(is_null($i)) {
            return $this->sendError(ucfirst($this->itemName).' not found', [], 404);
        }

        $this->sendAck(request('ids'));

        $i->reorderImagesByRole(request('ids'), request('role', 'images'));

        return $this->sendAck();
    }

    /**
     * @apiDescription Delete a image by id
     * @param int $id
     * @param int $imageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage(int $id, int $imageId)
    {
        $i = $this->class::find($id);
        if(is_null($i)) {
            return $this->sendError(ucfirst($this->itemName).' not found', [], 404);
        }

        $i->clearImage($imageId);

        return $this->sendAck();
    }

    /**
     * Detect if thumbnail is present in payload
     * @param $payload
     */
    protected function thumnbnailDetect(&$payload)
    {
        $thumbnailDetected = false;
        foreach($payload as $param=>$value) {
            if(in_array($param, ['thumbnailUrl', 'thumbnailContent', 'thumbnailFormat', 'thumbnailUrlBase64'])) {
                unset($payload[$param]);
                if(!is_array($thumbnailDetected)) {
                    $thumbnailDetected = [];
                }
                $thumbnailDetected[$param] = $value;
            }
        }
        return $thumbnailDetected;
    }

    /**
     * Auto load thumbnail to model
     * @param $model
     * @param $thumbnailData
     */
    protected function thumbnailAttach($model, $thumbnailData, $name='')
    {
        if(isset($thumbnailData['thumbnailUrl']) and !empty($thumbnailData['thumbnailUrl'])) {
            $extension = null;
            if(isset($thumbnailData['thumbnailFormat']) and !empty($thumbnailData['thumbnailFormat'])) {
                $detectedFormat = $thumbnailData['thumbnailFormat'];
            } else {
                $detectedFormat = strtolower(substr($thumbnailData['thumbnailUrl'], -4));
            }

            if($detectedFormat == 'jpeg' or $detectedFormat == '.jpg') {
                $extension = 'jpeg';
            }
            if(trim($detectedFormat, ' .') == 'png') {
                $extension = 'png';
            }

            if(is_null($extension)) {
                return;
            }

            $contents = file_get_contents($thumbnailData['thumbnailUrl']);

            if(empty($contents)) {
                return;
            }

            $metadata['mime'] = 'image/'.$extension;
            if($name) {
                $metadata['name'] = $name;
            } else {
                $metadata['name'] = substr($thumbnailData['thumbnailUrl'], strrpos($thumbnailData['thumbnailUrl'], '/')+1);
            }
            $metadata['basename'] = $metadata['name'];
            $metadata['extension'] = $extension;
            $metadata['size'] = strlen($contents);
            $metadata['originalPath'] = $thumbnailData['thumbnailUrl'];
            $metadata['hash'] = md5($contents);

            $image = \App\ImageStore::create($metadata, $contents);
        } else if(isset($thumbnailData['thumbnailContent']) and !empty($thumbnailData['thumbnailContent'])) {
            $extension = null;
            if(isset($thumbnailData['thumbnailFormat']) and !empty($thumbnailData['thumbnailFormat'])) {
                $detectedFormat = $thumbnailData['thumbnailFormat'];
            } else {
                return;
            }

            if($detectedFormat == 'jpeg' or $detectedFormat == '.jpg') {
                $extension = 'jpeg';
            }
            if(trim($detectedFormat, ' .') == 'png') {
                $extension = 'png';
            }

            if(is_null($extension)) {
                return;
            }

            $contents = base64_decode($thumbnailData['thumbnailContent']);

            if(empty($contents)) {
                return;
            }

            $metadata['mime'] = 'image/'.$extension;
            if($name) {
                $metadata['name'] = $name;
            } else {
                $metadata['name'] = $this->itemName.' '.$this->keyName.' avatar';
            }
            $metadata['basename'] = $metadata['name'];
            $metadata['extension'] = $extension;
            $metadata['size'] = strlen($contents);
            $metadata['hash'] = md5($contents);

            $image = \App\ImageStore::create($metadata, $contents);
        } else if(isset($thumbnailData['thumbnailUrlBase64']) and !empty($thumbnailData['thumbnailUrlBase64'])) {
            $detectedFormat = substr($thumbnailData['thumbnailUrlBase64'], 0,  strpos($thumbnailData['thumbnailUrlBase64'], ';'));
            $detectedFormat = substr($detectedFormat, strpos($detectedFormat, '/') + 1);
            $base64Content = substr($thumbnailData['thumbnailUrlBase64'], strpos($thumbnailData['thumbnailUrlBase64'], ',') + 1);

            if($detectedFormat == 'jpeg' or $detectedFormat == '.jpg') {
                $extension = 'jpeg';
            }
            if(trim($detectedFormat, ' .') == 'png') {
                $extension = 'png';
            }

            if(is_null($extension)) {
                return;
            }

            $contents = base64_decode($base64Content);

            if(empty($contents)) {
                return;
            }

            $metadata['mime'] = 'image/'.$extension;
            $metadata['extension'] = $extension;
            $metadata['size'] = strlen($contents);
            $metadata['hash'] = md5($contents);

            if(isset($image['name']) and !empty($image['name'])) {
                $metadata['name'] = $image['name'];
            } else {
                $metadata['name'] = $this->itemName.' '.$this->keyName.' avatar';
            }
            $metadata['basename'] = $metadata['name'];

            $image = \App\ImageStore::create($metadata, $contents);
        }

        if(isset($image) and $image) {
            $model->clearImagesByRole('thumbnail', true);
            $image->attach($model, 'thumbnail');
        }
    }

    /**
     * Detect if images are present in payload
     * @param $payload
     */
    protected function imagesDetect(&$payload)
    {
        $imagesDetected = false;
        if(!isset($payload['images']) or (isset($payload['images']) and !is_array($payload['images']))) {
            return false;
        }

        $imagesDetected = [];
        foreach($payload['images'] as $image) {
            if(
                (isset($image['url']) and !empty($image['url']))
                or (isset($image['content']) and !empty($image['content']) and isset($image['format']) and !empty($image['format']))
                or (isset($image['urlBase64']) and !empty($image['urlBase64']))
            ) {
                $imagesDetected[] = $image;
            }
        }

        unset($payload['images']);
        if(empty($imagesDetected)) {
            return false;
        }

        return $imagesDetected;
    }

    /**
     * Auto load images to model
     * @param $model
     * @param $imagesPayload
     */
    protected function imagesAttach($model, $imagesPayload, $role='images')
    {
        foreach($imagesPayload as $image) {
            if(isset($image['url']) and !empty($image['url'])) {
                $extension = null;
                if(isset($image['format']) and !empty($image['format'])) {
                    $detectedFormat = $image['format'];
                } else {
                    $detectedFormat = strtolower(substr($image['url'], -4));
                }

                if($detectedFormat == 'jpeg' or $detectedFormat == '.jpg') {
                    $extension = 'jpeg';
                }
                if(trim($detectedFormat, ' .') == 'png') {
                    $extension = 'png';
                }

                if(is_null($extension)) {
                    continue;
                }

                $contents = file_get_contents($image['url']);

                if(empty($contents)) {
                    continue;
                }

                $metadata['mime'] = 'image/'.$extension;
                $metadata['extension'] = $extension;
                $metadata['size'] = strlen($contents);
                $metadata['originalPath'] = $image['url'];
                $metadata['hash'] = md5($contents);

                if(isset($image['name']) and !empty($image['name'])) {
                    $metadata['name'] = $image['name'];
                } else {
                    $metadata['name'] = $metadata['hash'];
                }
                $metadata['basename'] = $metadata['name'];

                $image = \App\ImageStore::create($metadata, $contents);

                if(isset($image['role']) and !empty($image['role'])) {
                    $image->attach($model, $image['role']);
                } else {
                    $image->attach($model, $role);
                }
            } else if(isset($image['content']) and !empty($image['content'])) {
                $extension = null;
                if(isset($image['format']) and !empty($image['format'])) {
                    $detectedFormat = $image['format'];
                } else {
                    continue;
                }

                if($detectedFormat == 'jpeg' or $detectedFormat == '.jpg') {
                    $extension = 'jpeg';
                }
                if(trim($detectedFormat, ' .') == 'png') {
                    $extension = 'png';
                }

                if(is_null($extension)) {
                    continue;
                }

                $contents = base64_decode($image['content']);

                if(empty($contents)) {
                    continue;
                }

                $metadata['mime'] = 'image/'.$extension;
                $metadata['extension'] = $extension;
                $metadata['size'] = strlen($contents);
                $metadata['hash'] = md5($contents);

                if(isset($image['name']) and !empty($image['name'])) {
                    $metadata['name'] = $image['name'];
                } else {
                    $metadata['name'] = $metadata['hash'];
                }
                $metadata['basename'] = $metadata['name'];

                $image = \App\ImageStore::create($metadata, $contents);

                if(isset($image['role']) and !empty($image['role'])) {
                    $image->attach($model, $image['role']);
                } else {
                    $image->attach($model, $role);
                }
            } else if(isset($image['urlBase64']) and !empty($image['urlBase64'])) {
                $detectedFormat = substr($image['urlBase64'], 0,  strpos($image['urlBase64'], ';'));
                $detectedFormat = substr($detectedFormat, strpos($detectedFormat, '/') + 1);
                $base64Content = substr($image['urlBase64'], strpos($image['urlBase64'], ',') + 1);

                if($detectedFormat == 'jpeg' or $detectedFormat == '.jpg') {
                    $extension = 'jpeg';
                }
                if(trim($detectedFormat, ' .') == 'png') {
                    $extension = 'png';
                }

                if(is_null($extension)) {
                    continue;
                }

                $contents = base64_decode($base64Content);

                if(empty($contents)) {
                    continue;
                }

                $metadata['mime'] = 'image/'.$extension;
                $metadata['extension'] = $extension;
                $metadata['size'] = strlen($contents);
                $metadata['hash'] = md5($contents);

                if(isset($image['name']) and !empty($image['name'])) {
                    $metadata['name'] = $image['name'];
                } else {
                    $metadata['name'] = $metadata['hash'];
                }
                $metadata['basename'] = $metadata['name'];

                $image = \App\ImageStore::create($metadata, $contents);

                if(isset($image['role']) and !empty($image['role'])) {
                    $image->attach($model, $image['role']);
                } else {
                    $image->attach($model, $role);
                }
            }
        }
    }

    /**
     * Detect if files are present in payload
     * @param $payload
     */
    protected function filesDetect(&$payload)
    {
        $filesDetected = false;
        if(!isset($payload['files']) or (isset($payload['files']) and !is_array($payload['files']))) {
            return false;
        }

        $filesDetected = [];
        foreach($payload['files'] as $file) {
            if(
                (isset($file['url']) and !empty($file['url']))
                or (isset($file['content']) and !empty($file['content']) and isset($file['mime']) and !empty($file['mime']))
                or (isset($file['urlBase64']) and !empty($file['urlBase64']))
            ) {
                $filesDetected[] = $file;
            }
        }

        unset($payload['files']);
        if(empty($filesDetected)) {
            return false;
        }

        return $filesDetected;
    }

    /**
     * Auto load files to model
     * @param $model
     * @param $imagesPayload
     */
    protected function filesAttach($model, $filesPayload, $role='files')
    {
        foreach($filesPayload as $file) {
            if(isset($file['url']) and !empty($file['url'])) {
                if(isset($file['extension']) and !empty($file['extension'])) {
                    $metadata['extension'] = $file['extension'];
                } else {
                    continue;
                }

                if(isset($file['mime']) and !empty($file['mime'])) {
                    $metadata['mime'] = $file['mime'];
                } else {
                    continue;
                }

                $contents = file_get_contents($file['url']);

                if(empty($contents)) {
                    continue;
                }

                $metadata['size'] = strlen($contents);
                $metadata['originalPath'] = $file['url'];
                $metadata['hash'] = md5($contents);

                if(isset($file['name']) and !empty($file['name'])) {
                    $metadata['name'] = $file['name'];
                } else {
                    $metadata['name'] = $metadata['hash'];
                }
                $metadata['basename'] = $metadata['name'];

                $file = \App\File::create($metadata, $contents);

                if(isset($file['role']) and !empty($file['role'])) {
                    $file->attach($model, $file['role']);
                } else {
                    $file->attach($model, $role);
                }
            } else if(isset($file['content']) and !empty($file['content'])) {
                if(isset($file['extension']) and !empty($file['extension'])) {
                    $metadata['extension'] = $file['extension'];
                } else {
                    continue;
                }

                if(isset($file['mime']) and !empty($file['mime'])) {
                    $metadata['mime'] = $file['mime'];
                } else {
                    continue;
                }

                $contents = base64_decode($file['content']);

                if(empty($contents)) {
                    continue;
                }

                $metadata['size'] = strlen($contents);
                $metadata['hash'] = md5($contents);

                if(isset($file['name']) and !empty($file['name'])) {
                    $metadata['name'] = $file['name'];
                } else {
                    $metadata['name'] = $metadata['hash'];
                }
                $metadata['basename'] = $metadata['name'];

                $file = \App\File::create($metadata, $contents);

                if(isset($file['role']) and !empty($file['role'])) {
                    $file->attach($model, $file['role']);
                } else {
                    $file->attach($model, $role);
                }
            } else if(isset($file['urlBase64']) and !empty($file['urlBase64'])) {
                $mime = substr($file['urlBase64'], 5,  strpos($file['urlBase64'], ';') - 5);
                $base64Content = substr($file['urlBase64'], strpos($file['urlBase64'], ',') + 1);

                if(isset($file['extension']) and !empty($file['extension'])) {
                    $metadata['extension'] = $file['extension'];
                } else {
                    continue;
                }

                $contents = base64_decode($base64Content);

                if(empty($contents)) {
                    continue;
                }

                $metadata['mime'] = $mime;
                $metadata['size'] = strlen($contents);
                $metadata['hash'] = md5($contents);

                if(isset($file['name']) and !empty($file['name'])) {
                    $metadata['name'] = $file['name'];
                } else {
                    $metadata['name'] = $metadata['hash'];
                }
                $metadata['basename'] = $metadata['name'];

                $file = \App\File::create($metadata, $contents);

                if(isset($file['role']) and !empty($file['role'])) {
                    $file->attach($model, $file['role']);
                } else {
                    $file->attach($model, $role);
                }
            }
        }
    }
}
