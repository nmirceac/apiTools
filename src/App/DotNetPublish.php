<?php namespace ApiTools\App;

class DotNetPublish
{
    public static $schema;

    public static function getSchema()
    {
        if(is_null(self::$schema)) {
            self::$schema = (new \App\Http\Controllers\Api\Api())->generateSchema();
        }
        return self::$schema;
    }

    public static function generateFiles()
    {
        foreach(self::getSchema()['classes'] as $class) {
            //if($class['name']=='Region') {
            if($class['name']=='Quote') {
            //if($class['name']=='CalculatorMyCover') {
                self::generateForClass($class);;
            }

        }
    }

    public static function generateForClass($class)
    {
        return self::getDotNetCode($class);
    }

    private static function getDotNetCode(array $class)
    {
        $dotNetCode = view('api-tools::dotnet.class', ['class'=>$class])->render();

        return trim($dotNetCode);
    }
}

