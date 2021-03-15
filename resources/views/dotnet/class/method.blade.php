
@if(in_array('GET', $method['route']['accepts']))
        /// <summary>
        /// {{ $method['name'] }}
        /// </summary>
@if(isset($method['api']['description']) and !empty($method['api']['description']))
        /// <remarks>
        /// {!! $method['api']['description'] !!}
        /// </remarks>
@endif
@foreach ($method['parameters'] as $parameter)
        /// <param name="{{ $parameter['name'] }}">{{ $parameter['required'] ? 'Required' : 'Optional' }} parameter '{{ $parameter['name'] }}' of type {{ $parameter['type']=='float' ? 'double' : $parameter['type'] }}</param>
@endforeach
        /// <returns>
        /// Data object
        /// </returns>
        public static object {{ $method['name'] }}({!! \ApiClientTools\Commands\PublishCommand::getDotNetParametersStrings($method)['parametersString'] !!})
        {
@if(!empty(\ApiClientTools\Commands\PublishCommand::getDotNetParametersStrings($method)['methodBodyContent']))
            {!! \ApiClientTools\Commands\PublishCommand::getDotNetParametersStrings($method)['methodBodyContent'] !!}
@endif
            return doGet("{{ $method['route']['uri'] }}"{!! \ApiClientTools\Commands\PublishCommand::getDotNetParametersStrings($method)['methodParametersString'] !!}, endpointUrlData).Result;
        }
@elseif(in_array('POST', $method['route']['accepts']))
        /// <summary>
        /// {{ $method['name'] }}
        /// </summary>
@if(isset($method['api']['description']) and !empty($method['api']['description']))
        /// <remarks>
        /// {!! $method['api']['description'] !!}
        /// </remarks>
@endif
@foreach ($method['parameters'] as $parameter)
        /// <param name="{{ $parameter['name'] }}">{{ $parameter['required'] ? 'Required' : 'Optional' }} parameter '{{ $parameter['name'] }}' of type {{ $parameter['type']=='float' ? 'double' : $parameter['type'] }}</param>
@endforeach
        /// <param name="endpointData">Required parameter endpointData of type System.Dynamic.ExpandoObject</param>
        /// <returns>
        /// Data object
        /// </returns>
        public static object {{ $method['name'] }}({{ \ApiClientTools\Commands\PublishCommand::getDotNetParametersStrings($method)['parametersString'] }}{{ \ApiClientTools\Commands\PublishCommand::getDotNetParametersStrings($method)['postParamsString'] }})
        {
@if(!empty(\ApiClientTools\Commands\PublishCommand::getDotNetParametersStrings($method)['methodBodyContent']))
            {!! \ApiClientTools\Commands\PublishCommand::getDotNetParametersStrings($method)['methodBodyContent'] !!}
@endif
            return doPost("{{ $method['route']['uri'] }}"{!! \ApiClientTools\Commands\PublishCommand::getDotNetParametersStrings($method)['methodParametersString'] !!}, endpointData).Result;
        }
@endif
