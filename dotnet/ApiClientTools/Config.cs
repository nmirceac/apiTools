using System;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.Rendering;
using Microsoft.EntityFrameworkCore;

namespace ApiClientTools
{
    public class Config
    {
        public static string getBaseUrl()
        {
            var apiBaseUrl = Environment.GetEnvironmentVariable("API_CLIENT_BASE_URL").TrimEnd(new char[] {'/'});
    
            return apiBaseUrl;
        }

        public static string getKey()
        {
            var apiKey = Environment.GetEnvironmentVariable("API_CLIENT_SECRET");

            return apiKey;
        }

        public static bool getDebug()
        {
            var apiDebug = Convert.ToBoolean(Environment.GetEnvironmentVariable("API_CLIENT_DEBUG"));

            return apiDebug;
        }
    }
}
