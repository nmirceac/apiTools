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

        return response()->json($response, $code);
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

        return response()->json($response, 200);
    }
}
