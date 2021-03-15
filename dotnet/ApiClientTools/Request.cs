using System;
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
    public class Request
    {
        public System.Net.Http.HttpMethod method;
        public string requestUrl;
        public string endpointUrl;


        public Dictionary<string, string> endpointParams;
        public Dictionary<string, string> endpointUrlData;
        public ExpandoObject payloadData;

        public void buildUrl()
        {
            requestUrl = endpointUrl;

            if(endpointParams != null && endpointParams.Count>0) {
                foreach (Match match in Regex.Matches(endpointUrl, @"{(.*?)}")) {
                   var matchedParam = match.Value.TrimStart('{').TrimEnd('}');
                    if(endpointParams.ContainsKey(matchedParam)) {
                        requestUrl = requestUrl.Replace(match.Value, HttpUtility.UrlEncode(endpointParams[matchedParam]));
                    }
                }
            }

            if(endpointUrlData != null && endpointUrlData.Count>0) {
                List<string> dataParts = new List<string>();
                foreach(KeyValuePair<string, string> part in endpointUrlData) {
                    dataParts.Add(HttpUtility.UrlEncode(part.Key)+"="+HttpUtility.UrlEncode(part.Value));
                }
                requestUrl += "?"+String.Join("&", dataParts.ToArray());
            }

            requestUrl = Config.getBaseUrl() + '/' + requestUrl.TrimStart(new char[] {'/'});
        }

        public HttpRequestMessage getHttpRequest()
        {
            buildUrl();
            HttpRequestMessage request = new HttpRequestMessage(method, requestUrl);
            string apiKey = Config.getKey();

            request.Headers.Add("Accept", "application/json");
            request.Headers.Add("X-Agent", "apiClientTools.dotNet");
            request.Headers.Add("X-Api-Key", apiKey);

            if(payloadData!=null && ((System.Collections.Generic.IDictionary<String, object>)payloadData).Count > 0) {
                var jsonPayload = JsonConvert.SerializeObject(payloadData);
                var requestContent = new StringContent(jsonPayload, System.Text.Encoding.UTF8, "application/json");
                request.Content = requestContent;
            }


            return request;
        }
    }
}
