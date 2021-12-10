# IpfsPinning.SDK.Api.PinsApi

All URIs are relative to *https://pinning-service.example.com*

Method | HTTP request | Description
------------- | ------------- | -------------
[**PinsGet**](PinsApi.md#pinsget) | **GET** /pins | List pin objects
[**PinsPost**](PinsApi.md#pinspost) | **POST** /pins | Add pin object
[**PinsRequestidDelete**](PinsApi.md#pinsrequestiddelete) | **DELETE** /pins/{requestid} | Remove pin object
[**PinsRequestidGet**](PinsApi.md#pinsrequestidget) | **GET** /pins/{requestid} | Get pin object
[**PinsRequestidPost**](PinsApi.md#pinsrequestidpost) | **POST** /pins/{requestid} | Replace pin object


<a name="pinsget"></a>
# **PinsGet**
> PinResults PinsGet (List<string> cid = null, string name = null, TextMatchingStrategy? match = null, List<Status> status = null, DateTime? before = null, DateTime? after = null, int? limit = null, Dictionary<string, string> meta = null)

List pin objects

List all the pin objects, matching optional filters; when no filter is provided, only successful pins are returned

### Example
```csharp
using System.Collections.Generic;
using System.Diagnostics;
using IpfsPinning.SDK.Api;
using IpfsPinning.SDK.Client;
using IpfsPinning.SDK.Model;

namespace Example
{
    public class PinsGetExample
    {
        public static void Main()
        {
            Configuration config = new Configuration();
            config.BasePath = "https://pinning-service.example.com";
            // Configure Bearer token for authorization: accessToken
            config.AccessToken = "YOUR_BEARER_TOKEN";

            var apiInstance = new PinsApi(config);
            var cid = new List<string>(); // List<string> | Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional) 
            var name = PreciousData.pdf;  // string | Return pin objects with specified name (by default a case-sensitive, exact match) (optional) 
            var match = exact;  // TextMatchingStrategy? | Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional) 
            var status = new List<Status>(); // List<Status> | Return pin objects for pins with the specified status (optional) 
            var before = 2020-07-27T17:32:28Z;  // DateTime? | Return results created (queued) before provided timestamp (optional) 
            var after = 2020-07-27T17:32:28Z;  // DateTime? | Return results created (queued) after provided timestamp (optional) 
            var limit = 56;  // int? | Max records to return (optional)  (default to 10)
            var meta = new Dictionary<string, string>(); // Dictionary<string, string> | Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional) 

            try
            {
                // List pin objects
                PinResults result = apiInstance.PinsGet(cid, name, match, status, before, after, limit, meta);
                Debug.WriteLine(result);
            }
            catch (ApiException  e)
            {
                Debug.Print("Exception when calling PinsApi.PinsGet: " + e.Message );
                Debug.Print("Status Code: "+ e.ErrorCode);
                Debug.Print(e.StackTrace);
            }
        }
    }
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **cid** | [**List&lt;string&gt;**](string.md)| Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts | [optional] 
 **name** | **string**| Return pin objects with specified name (by default a case-sensitive, exact match) | [optional] 
 **match** | **TextMatchingStrategy?**| Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies | [optional] 
 **status** | [**List&lt;Status&gt;**](Status.md)| Return pin objects for pins with the specified status | [optional] 
 **before** | **DateTime?**| Return results created (queued) before provided timestamp | [optional] 
 **after** | **DateTime?**| Return results created (queued) after provided timestamp | [optional] 
 **limit** | **int?**| Max records to return | [optional] [default to 10]
 **meta** | [**Dictionary&lt;string, string&gt;**](string.md)| Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport | [optional] 

### Return type

[**PinResults**](PinResults.md)

### Authorization

[accessToken](../README.md#accessToken)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
| **200** | Successful response (PinResults object) |  -  |
| **400** | Error response (Bad request) |  -  |
| **401** | Error response (Unauthorized; access token is missing or invalid) |  -  |
| **404** | Error response (The specified resource was not found) |  -  |
| **409** | Error response (Insufficient funds) |  -  |
| **4XX** | Error response (Custom service error) |  -  |
| **5XX** | Error response (Unexpected internal server error) |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

<a name="pinspost"></a>
# **PinsPost**
> PinStatus PinsPost (Pin pin)

Add pin object

Add a new pin object for the current access token

### Example
```csharp
using System.Collections.Generic;
using System.Diagnostics;
using IpfsPinning.SDK.Api;
using IpfsPinning.SDK.Client;
using IpfsPinning.SDK.Model;

namespace Example
{
    public class PinsPostExample
    {
        public static void Main()
        {
            Configuration config = new Configuration();
            config.BasePath = "https://pinning-service.example.com";
            // Configure Bearer token for authorization: accessToken
            config.AccessToken = "YOUR_BEARER_TOKEN";

            var apiInstance = new PinsApi(config);
            var pin = new Pin(); // Pin | 

            try
            {
                // Add pin object
                PinStatus result = apiInstance.PinsPost(pin);
                Debug.WriteLine(result);
            }
            catch (ApiException  e)
            {
                Debug.Print("Exception when calling PinsApi.PinsPost: " + e.Message );
                Debug.Print("Status Code: "+ e.ErrorCode);
                Debug.Print(e.StackTrace);
            }
        }
    }
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **pin** | [**Pin**](Pin.md)|  | 

### Return type

[**PinStatus**](PinStatus.md)

### Authorization

[accessToken](../README.md#accessToken)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
| **202** | Successful response (PinStatus object) |  -  |
| **400** | Error response (Bad request) |  -  |
| **401** | Error response (Unauthorized; access token is missing or invalid) |  -  |
| **404** | Error response (The specified resource was not found) |  -  |
| **409** | Error response (Insufficient funds) |  -  |
| **4XX** | Error response (Custom service error) |  -  |
| **5XX** | Error response (Unexpected internal server error) |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

<a name="pinsrequestiddelete"></a>
# **PinsRequestidDelete**
> void PinsRequestidDelete (string requestid)

Remove pin object

Remove a pin object

### Example
```csharp
using System.Collections.Generic;
using System.Diagnostics;
using IpfsPinning.SDK.Api;
using IpfsPinning.SDK.Client;
using IpfsPinning.SDK.Model;

namespace Example
{
    public class PinsRequestidDeleteExample
    {
        public static void Main()
        {
            Configuration config = new Configuration();
            config.BasePath = "https://pinning-service.example.com";
            // Configure Bearer token for authorization: accessToken
            config.AccessToken = "YOUR_BEARER_TOKEN";

            var apiInstance = new PinsApi(config);
            var requestid = requestid_example;  // string | 

            try
            {
                // Remove pin object
                apiInstance.PinsRequestidDelete(requestid);
            }
            catch (ApiException  e)
            {
                Debug.Print("Exception when calling PinsApi.PinsRequestidDelete: " + e.Message );
                Debug.Print("Status Code: "+ e.ErrorCode);
                Debug.Print(e.StackTrace);
            }
        }
    }
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **requestid** | **string**|  | 

### Return type

void (empty response body)

### Authorization

[accessToken](../README.md#accessToken)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
| **202** | Successful response (no body, pin removed) |  -  |
| **400** | Error response (Bad request) |  -  |
| **401** | Error response (Unauthorized; access token is missing or invalid) |  -  |
| **404** | Error response (The specified resource was not found) |  -  |
| **409** | Error response (Insufficient funds) |  -  |
| **4XX** | Error response (Custom service error) |  -  |
| **5XX** | Error response (Unexpected internal server error) |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

<a name="pinsrequestidget"></a>
# **PinsRequestidGet**
> PinStatus PinsRequestidGet (string requestid)

Get pin object

Get a pin object and its status

### Example
```csharp
using System.Collections.Generic;
using System.Diagnostics;
using IpfsPinning.SDK.Api;
using IpfsPinning.SDK.Client;
using IpfsPinning.SDK.Model;

namespace Example
{
    public class PinsRequestidGetExample
    {
        public static void Main()
        {
            Configuration config = new Configuration();
            config.BasePath = "https://pinning-service.example.com";
            // Configure Bearer token for authorization: accessToken
            config.AccessToken = "YOUR_BEARER_TOKEN";

            var apiInstance = new PinsApi(config);
            var requestid = requestid_example;  // string | 

            try
            {
                // Get pin object
                PinStatus result = apiInstance.PinsRequestidGet(requestid);
                Debug.WriteLine(result);
            }
            catch (ApiException  e)
            {
                Debug.Print("Exception when calling PinsApi.PinsRequestidGet: " + e.Message );
                Debug.Print("Status Code: "+ e.ErrorCode);
                Debug.Print(e.StackTrace);
            }
        }
    }
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **requestid** | **string**|  | 

### Return type

[**PinStatus**](PinStatus.md)

### Authorization

[accessToken](../README.md#accessToken)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
| **200** | Successful response (PinStatus object) |  -  |
| **400** | Error response (Bad request) |  -  |
| **401** | Error response (Unauthorized; access token is missing or invalid) |  -  |
| **404** | Error response (The specified resource was not found) |  -  |
| **409** | Error response (Insufficient funds) |  -  |
| **4XX** | Error response (Custom service error) |  -  |
| **5XX** | Error response (Unexpected internal server error) |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

<a name="pinsrequestidpost"></a>
# **PinsRequestidPost**
> PinStatus PinsRequestidPost (string requestid, Pin pin)

Replace pin object

Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)

### Example
```csharp
using System.Collections.Generic;
using System.Diagnostics;
using IpfsPinning.SDK.Api;
using IpfsPinning.SDK.Client;
using IpfsPinning.SDK.Model;

namespace Example
{
    public class PinsRequestidPostExample
    {
        public static void Main()
        {
            Configuration config = new Configuration();
            config.BasePath = "https://pinning-service.example.com";
            // Configure Bearer token for authorization: accessToken
            config.AccessToken = "YOUR_BEARER_TOKEN";

            var apiInstance = new PinsApi(config);
            var requestid = requestid_example;  // string | 
            var pin = new Pin(); // Pin | 

            try
            {
                // Replace pin object
                PinStatus result = apiInstance.PinsRequestidPost(requestid, pin);
                Debug.WriteLine(result);
            }
            catch (ApiException  e)
            {
                Debug.Print("Exception when calling PinsApi.PinsRequestidPost: " + e.Message );
                Debug.Print("Status Code: "+ e.ErrorCode);
                Debug.Print(e.StackTrace);
            }
        }
    }
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **requestid** | **string**|  | 
 **pin** | [**Pin**](Pin.md)|  | 

### Return type

[**PinStatus**](PinStatus.md)

### Authorization

[accessToken](../README.md#accessToken)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
| **202** | Successful response (PinStatus object) |  -  |
| **400** | Error response (Bad request) |  -  |
| **401** | Error response (Unauthorized; access token is missing or invalid) |  -  |
| **404** | Error response (The specified resource was not found) |  -  |
| **409** | Error response (Insufficient funds) |  -  |
| **4XX** | Error response (Custom service error) |  -  |
| **5XX** | Error response (Unexpected internal server error) |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

