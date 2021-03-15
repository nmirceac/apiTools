using System;
using System.Dynamic;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.Rendering;
using Microsoft.EntityFrameworkCore;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;

namespace ApiClientTools
{
    public class Response
    {
        public ApiClientTools.Request request;
        public System.Net.Http.HttpResponseMessage httpResponse;

        private dynamic jsonResponse;
        private bool success;

        public dynamic data
        {
            get { 
                if(((System.Collections.Generic.IDictionary<String, object>)jsonResponse).ContainsKey("data")) {
                    return jsonResponse.data;
                } else {
                    return null;
                }
            }
        }

        private string errorMessage;
        

        public static Response processResponse(ApiClientTools.Request request, System.Net.Http.HttpResponseMessage httpResponse)
        {
            Response response = new Response();

            var stringResponse = httpResponse.Content.ReadAsStringAsync().Result;
            response.success = httpResponse.IsSuccessStatusCode;

            ExpandoObject responseType = new ExpandoObject();

            if(response.success)
            {
                try {
                    response.jsonResponse = JsonConvert.DeserializeAnonymousType(stringResponse, responseType);
                } catch (JsonReaderException ex) {
                    if(ApiClientTools.Config.getDebug()) {
                        throw new ArgumentException("There was a problem parsing the response JSON to the request to "+request.requestUrl+"\n\n", stringResponse, ex);
                    } else {
                        throw new ArgumentException("There was a problem parsing the JSON\n\n", stringResponse.Substring(0, 128), ex);
                    }
                }

                if(request.method == System.Net.Http.HttpMethod.Post)
                {
                    if(!((System.Collections.Generic.IDictionary<String, object>)response.jsonResponse).ContainsKey("ack")) {
                        if(ApiClientTools.Config.getDebug()) {
                            throw new ArgumentException("The POST response to the request to "+request.requestUrl+" doesn't include an ACK:\n\n", stringResponse);
                        } else {
                            throw new ArgumentException("The POST response doesn't include an ACK");
                        }
                    }
                }
                
            } else {
                try {
                    response.jsonResponse = JsonConvert.DeserializeAnonymousType(stringResponse, responseType);
                } catch (JsonReaderException ex) {
                    if(ApiClientTools.Config.getDebug()) {
                        throw new ArgumentException("There was a problem parsing the JSON of a unsuccessful response\n\n", stringResponse, ex);
                    } else {
                        throw new ArgumentException("There was a problem parsing the JSON or an unsuccessful response\n\n", stringResponse.Substring(0, 128), ex);
                    }
                }

                response.jsonResponse = JsonConvert.DeserializeAnonymousType(stringResponse, responseType);
                response.errorMessage = response.jsonResponse.message;
                throw new ArgumentException(response.jsonResponse.message, stringResponse);
            }

            return response;
        }
    }
}
