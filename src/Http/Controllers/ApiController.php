<?php namespace ApiTools\Http\Controllers;

/**
 * Class ApiController
 * @apiDescription Exposing the API interfaces
 * @package ApiTools\Http\Controllers\ApiController
 */
class ApiController extends BaseController
{
    protected $intro = [
        ['type'=>'h1', 'content'=>'API interface']
    ];

    protected $exposed = [];

    public $apiRoutesByUrl = [];
    public $apiRoutesByAction = [];

    public $excludedConstants = [
        'CREATED_AT',
        'UPDATED_AT',
    ];

    public function generateSchema()
    {
        $routes = \Route::getRoutes();
        foreach($routes->getRoutes() as $route) {
            if(isset($route->action['prefix']) and $route->action['prefix']=='api') {
                $routeInfo = [];
                $routeInfo['uri'] = $route->uri;
                $routeInfo['action'] = $route->action['controller'];
                $routeInfo['controller'] = substr($route->action['controller'], 0, strpos($route->action['controller'], '@'));
                $routeInfo['accepts'] = array_diff($route->methods, ['HEAD']);

                $this->apiRoutesByUrl[$route->uri] = $routeInfo;
                $this->apiRoutesByAction[$routeInfo['action']] = $routeInfo;
            }
        }

        $classes = [];
        foreach($this->exposed as $class) {
            $classes[] = $this->exposeClass($class);
        }

        return [
            'intro' => $this->intro,
            'classes' => $classes
        ];
    }


    public function schema()
    {
        return $this->sendResponse($this->generateSchema());
    }

    private function exposeClass($class)
    {
        $classInstance = (new $class());
        $reflector = new \ReflectionClass($class);
        $apiInfo = $this->getApiInfoFromDocComment($reflector->getDocComment());

        $name = substr($reflector->getName(), 1 + strlen($reflector->getNamespaceName()));
        $fullName = $reflector->getName();
        $parent = $reflector->getParentClass();

        $properties = $reflector->getDefaultProperties();


        $constants = $reflector->getConstants();

        $staticProperties = $reflector->getStaticProperties();

        if(isset($apiInfo['description'])) {
            $description = $apiInfo['description'];
        } else {
            $description = 'API interface for '.$name;
        }

        if(isset($apiInfo['model'])) {
            $model = $apiInfo['model'];
        } else {
            $model = $name;
        }

        $modelReflector = null;

        if(isset($classInstance->class)) {
            try {
                $modelReflector = new \ReflectionClass($classInstance->class);
            } catch (\Exception $e) {
                $modelReflector = null;
            }
        }


        if($modelReflector) {
            $constants = array_merge($modelReflector->getConstants(), $constants);
            $internalModelName = $modelReflector->getName();
        } else {
            $internalModelName = null;
        }


        if($parent) {
            $parent = $parent->name;
        }

        $methods = [];

        foreach($reflector->getMethods() as $method) {
            if($method->getModifiers()==256) {
                $methodInfo = [];
                $apiInfo = $this->getApiInfoFromDocComment($method->getDocComment());
                $methodInfo['name'] = $method->name;

                $methodInfo['api'] = $apiInfo;

                $methodInfo['route'] = null;
                if(isset($this->apiRoutesByAction[$fullName.'@'.$methodInfo['name']])) {
                    $route = $this->apiRoutesByAction[$fullName.'@'.$methodInfo['name']];
                    unset($route['action']);
                    unset($route['controller']);
                    $methodInfo['route'] = $route;
                }
                // remove if routeless
                if(is_null($methodInfo['route'])) {
                    continue;
                }

                $methodInfo['requiredParametersCount'] = $method->getNumberOfRequiredParameters();
                $methodInfo['parametersCount'] = $method->getNumberOfParameters();
                $methodInfo['parameters'] = [];
                foreach($method->getParameters() as $parameter) {
                    if($parameter->isDefaultValueAvailable()) {
                        $default = $parameter->getDefaultValue();
                        $required = false;
                    } else {
                        $default = null;
                        $required = true;
                    }

                    $type = $parameter->getType();
                    if(!is_null($type)) {
                        $type = $type->getName();
                    }

                    $methodInfo['parameters'][] = [
                        'name'=>$parameter->name,
                        'type'=>$type,
                        'required'=>$required,
                        'default'=>$default,
                    ];
                }

                $methods[] = $methodInfo;
            }
        }


        foreach($this->excludedConstants as $constantKey) {
            if(isset($constants[$constantKey])) {
                unset($constants[$constantKey]);
            }
        }

        return [
            'name'=>$name,
            'fullName'=>$fullName,
            'parent'=>$parent,
            'description'=>$description,
            'model'=>$model,
            'internalModel'=>$internalModelName,
            'properties'=>$properties,
            'constants'=>$constants,
            'staticProperties'=>$staticProperties,
            'methods'=>$methods
        ];
    }

    private function getApiInfoFromDocComment(string $docComment)
    {
        if(empty($docComment)) {
            $docComment = [];
        } else {
            $lines = explode(PHP_EOL, $docComment);
            $docComment = [];
            foreach($lines as $row=>$line) {
                $line = trim($line, "\r\n\t *");
                if(substr($line, 0, 4)=='@api') {
                    $line = substr($line, 4);
                    $param = camel_case(substr($line, 0, strpos($line, ' ')));
                    $value = substr($line, 1 + strpos($line, ' '));

                    $docComment[$param][] = $value;
                }
            }

            foreach($docComment as $param=>$values) {
                $docComment[$param] = trim(implode(' ', $values));
            }
        }
        return $docComment;
    }
}
