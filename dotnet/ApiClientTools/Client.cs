﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.Rendering;
using Microsoft.EntityFrameworkCore;
using System.Net.Http;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System.Dynamic;
using System.Web;
using System.Text.RegularExpressions;

namespace ApiClientTools
{
    public class Client
    {
        public static HttpClientHandler getHttpHandler()
        {
            return new HttpClientHandler{
                MaxResponseHeadersLength = 1024,
                UseCookies = false,
                // UseDefaultCredentials = false,
                // Proxy = new WebProxy("http://localhost:8888", false, new string[]{}),
                // UseProxy = true,
            };
        }

        public static async Task<object> doGet(string endpoint, Dictionary<string, string> endpointParams = null, Dictionary<string, string> endpointData = null)
        {
            dynamic response = doGetRequest(endpoint, endpointParams, endpointData);
            return response.data;
        }


        public static ApiClientTools.Response doGetRequest(string endpoint, Dictionary<string, string> endpointParams = null, Dictionary<string, string> endpointData = null)
        {
            ApiClientTools.Request request = new ApiClientTools.Request();
            request.endpointUrl = endpoint;
            request.endpointParams = endpointParams;
            request.endpointUrlData = endpointData;
            request.method = System.Net.Http.HttpMethod.Get;

            HttpClient client = new HttpClient(getHttpHandler());
            var response = client.SendAsync(request.getHttpRequest()).Result;

            ApiClientTools.Response apiClientToolsResponse = ApiClientTools.Response.processResponse(request, response);
            return apiClientToolsResponse;
        }

        public static async Task<object> doPost(string endpoint, Dictionary<string, string> endpointParams = null, ExpandoObject data = null)
        {
            dynamic response = doPostRequest(endpoint, endpointParams, data);
            return response.data;
        }

        public static ApiClientTools.Response doPostRequest(string endpoint, Dictionary<string, string> endpointParams = null, ExpandoObject data = null)
        {
            ApiClientTools.Request request = new ApiClientTools.Request();
            request.endpointUrl = endpoint;
            request.endpointParams = endpointParams;
            request.payloadData = data;
            request.method = System.Net.Http.HttpMethod.Post;

            HttpClient client = new HttpClient(getHttpHandler());
            var response = client.SendAsync(request.getHttpRequest()).Result;

            ApiClientTools.Response apiClientToolsResponse = ApiClientTools.Response.processResponse(request, response);
            return apiClientToolsResponse;
        }
    }
}
