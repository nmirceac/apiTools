using ApiClientTools;

namespace {{ \ApiClientTools\App\Api\Base::getBaseNamespace() }}
{

    /// <summary>
    /// The {{ $class['name'] }} class
    /// {{ $class['description'] }}
    ///
    /// {{ '@' }}apiHash {{ md5(json_encode($class)) }}
    /// {{ '@' }}package {{ \ApiClientTools\App\Api\Base::getBaseNamespace() }}\{{ $class['name'] }}
@if(isset($class['internalModel']) and !empty($class['internalModel']))    /// {{'@' }}internalModel {{ $class['internalModel'] }}
@endif
    /// </summary>
    public class {{ $class['name'] }} : ApiClientTools.Client
    {
@if(isset($class['internalModel']) and !empty($class['internalModel']))        protected static string internalModel = "{{ str_replace('\\', '\\\\', $class['internalModel']) }}";

@endif
@foreach ($class['constants'] as $constant => $value)
@if(is_string($value))
        public const string {{ $constant }} = "{{ $value }}";
@elseif(is_int($value))
        public const int {{ $constant }} = {{ $value }};
@else($value)
        public const double {{ $constant }} = {{ $value }};
@endif
@endforeach

