# IpfsPinningSDK.PinsApi

All URIs are relative to *https://pinning-service.example.com*

Method | HTTP request | Description
------------- | ------------- | -------------
[**pins_get**](PinsApi.md#pins_get) | **GET** /pins | List pin objects
[**pins_post**](PinsApi.md#pins_post) | **POST** /pins | Add pin object
[**pins_requestid_delete**](PinsApi.md#pins_requestid_delete) | **DELETE** /pins/{requestid} | Remove pin object
[**pins_requestid_get**](PinsApi.md#pins_requestid_get) | **GET** /pins/{requestid} | Get pin object
[**pins_requestid_post**](PinsApi.md#pins_requestid_post) | **POST** /pins/{requestid} | Replace pin object


# **pins_get**
> PinResults pins_get()

List pin objects

List all the pin objects, matching optional filters; when no filter is provided, only successful pins are returned

### Example

* Bearer Authentication (accessToken):
```python
import time
import IpfsPinningSDK
from IpfsPinningSDK.api import pins_api
from IpfsPinningSDK.model.text_matching_strategy import TextMatchingStrategy
from IpfsPinningSDK.model.pin_meta import PinMeta
from IpfsPinningSDK.model.pin_results import PinResults
from IpfsPinningSDK.model.failure import Failure
from IpfsPinningSDK.model.status import Status
from pprint import pprint
# Defining the host is optional and defaults to https://pinning-service.example.com
# See configuration.py for a list of all supported configuration parameters.
configuration = IpfsPinningSDK.Configuration(
    host = "https://pinning-service.example.com"
)

# The client must configure the authentication and authorization parameters
# in accordance with the API server security policy.
# Examples for each auth method are provided below, use the example that
# satisfies your auth use case.

# Configure Bearer authorization: accessToken
configuration = IpfsPinningSDK.Configuration(
    access_token = 'YOUR_BEARER_TOKEN'
)

# Enter a context with an instance of the API client
with IpfsPinningSDK.ApiClient(configuration) as api_client:
    # Create an instance of the API class
    api_instance = pins_api.PinsApi(api_client)
    cid = ["Qm1","Qm2","bafy3"] # [str] | Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional)
    name = "PreciousData.pdf" # str | Return pin objects with specified name (by default a case-sensitive, exact match) (optional)
    match = TextMatchingStrategy("exact") # TextMatchingStrategy | Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional)
    status = [
        Status("["queued","pinning"]"),
    ] # [Status] | Return pin objects for pins with the specified status (optional)
    before = dateutil_parser('2020-07-27T17:32:28Z') # datetime | Return results created (queued) before provided timestamp (optional)
    after = dateutil_parser('2020-07-27T17:32:28Z') # datetime | Return results created (queued) after provided timestamp (optional)
    limit = 10 # int | Max records to return (optional) if omitted the server will use the default value of 10
    meta =  # PinMeta | Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)

    # example passing only required values which don't have defaults set
    # and optional values
    try:
        # List pin objects
        api_response = api_instance.pins_get(cid=cid, name=name, match=match, status=status, before=before, after=after, limit=limit, meta=meta)
        pprint(api_response)
    except IpfsPinningSDK.ApiException as e:
        print("Exception when calling PinsApi->pins_get: %s\n" % e)
```


### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **cid** | **[str]**| Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts | [optional]
 **name** | **str**| Return pin objects with specified name (by default a case-sensitive, exact match) | [optional]
 **match** | **TextMatchingStrategy**| Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies | [optional]
 **status** | [**[Status]**](Status.md)| Return pin objects for pins with the specified status | [optional]
 **before** | **datetime**| Return results created (queued) before provided timestamp | [optional]
 **after** | **datetime**| Return results created (queued) after provided timestamp | [optional]
 **limit** | **int**| Max records to return | [optional] if omitted the server will use the default value of 10
 **meta** | **PinMeta**| Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport | [optional]

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
**200** | Successful response (PinResults object) |  -  |
**400** | Error response (Bad request) |  -  |
**401** | Error response (Unauthorized; access token is missing or invalid) |  -  |
**404** | Error response (The specified resource was not found) |  -  |
**409** | Error response (Insufficient funds) |  -  |
**4XX** | Error response (Custom service error) |  -  |
**5XX** | Error response (Unexpected internal server error) |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **pins_post**
> PinStatus pins_post(pin)

Add pin object

Add a new pin object for the current access token

### Example

* Bearer Authentication (accessToken):
```python
import time
import IpfsPinningSDK
from IpfsPinningSDK.api import pins_api
from IpfsPinningSDK.model.pin import Pin
from IpfsPinningSDK.model.pin_status import PinStatus
from IpfsPinningSDK.model.failure import Failure
from pprint import pprint
# Defining the host is optional and defaults to https://pinning-service.example.com
# See configuration.py for a list of all supported configuration parameters.
configuration = IpfsPinningSDK.Configuration(
    host = "https://pinning-service.example.com"
)

# The client must configure the authentication and authorization parameters
# in accordance with the API server security policy.
# Examples for each auth method are provided below, use the example that
# satisfies your auth use case.

# Configure Bearer authorization: accessToken
configuration = IpfsPinningSDK.Configuration(
    access_token = 'YOUR_BEARER_TOKEN'
)

# Enter a context with an instance of the API client
with IpfsPinningSDK.ApiClient(configuration) as api_client:
    # Create an instance of the API class
    api_instance = pins_api.PinsApi(api_client)
    pin = Pin(
        cid="QmCIDToBePinned",
        name="PreciousData.pdf",
        origins=Origins(["/ip4/203.0.113.142/tcp/4001/p2p/QmSourcePeerId","/ip4/203.0.113.114/udp/4001/quic/p2p/QmSourcePeerId"]),
        meta=PinMeta(
            key="key_example",
        ),
    ) # Pin | 

    # example passing only required values which don't have defaults set
    try:
        # Add pin object
        api_response = api_instance.pins_post(pin)
        pprint(api_response)
    except IpfsPinningSDK.ApiException as e:
        print("Exception when calling PinsApi->pins_post: %s\n" % e)
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
**202** | Successful response (PinStatus object) |  -  |
**400** | Error response (Bad request) |  -  |
**401** | Error response (Unauthorized; access token is missing or invalid) |  -  |
**404** | Error response (The specified resource was not found) |  -  |
**409** | Error response (Insufficient funds) |  -  |
**4XX** | Error response (Custom service error) |  -  |
**5XX** | Error response (Unexpected internal server error) |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **pins_requestid_delete**
> pins_requestid_delete(requestid)

Remove pin object

Remove a pin object

### Example

* Bearer Authentication (accessToken):
```python
import time
import IpfsPinningSDK
from IpfsPinningSDK.api import pins_api
from IpfsPinningSDK.model.failure import Failure
from pprint import pprint
# Defining the host is optional and defaults to https://pinning-service.example.com
# See configuration.py for a list of all supported configuration parameters.
configuration = IpfsPinningSDK.Configuration(
    host = "https://pinning-service.example.com"
)

# The client must configure the authentication and authorization parameters
# in accordance with the API server security policy.
# Examples for each auth method are provided below, use the example that
# satisfies your auth use case.

# Configure Bearer authorization: accessToken
configuration = IpfsPinningSDK.Configuration(
    access_token = 'YOUR_BEARER_TOKEN'
)

# Enter a context with an instance of the API client
with IpfsPinningSDK.ApiClient(configuration) as api_client:
    # Create an instance of the API class
    api_instance = pins_api.PinsApi(api_client)
    requestid = "requestid_example" # str | 

    # example passing only required values which don't have defaults set
    try:
        # Remove pin object
        api_instance.pins_requestid_delete(requestid)
    except IpfsPinningSDK.ApiException as e:
        print("Exception when calling PinsApi->pins_requestid_delete: %s\n" % e)
```


### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **requestid** | **str**|  |

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
**202** | Successful response (no body, pin removed) |  -  |
**400** | Error response (Bad request) |  -  |
**401** | Error response (Unauthorized; access token is missing or invalid) |  -  |
**404** | Error response (The specified resource was not found) |  -  |
**409** | Error response (Insufficient funds) |  -  |
**4XX** | Error response (Custom service error) |  -  |
**5XX** | Error response (Unexpected internal server error) |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **pins_requestid_get**
> PinStatus pins_requestid_get(requestid)

Get pin object

Get a pin object and its status

### Example

* Bearer Authentication (accessToken):
```python
import time
import IpfsPinningSDK
from IpfsPinningSDK.api import pins_api
from IpfsPinningSDK.model.pin_status import PinStatus
from IpfsPinningSDK.model.failure import Failure
from pprint import pprint
# Defining the host is optional and defaults to https://pinning-service.example.com
# See configuration.py for a list of all supported configuration parameters.
configuration = IpfsPinningSDK.Configuration(
    host = "https://pinning-service.example.com"
)

# The client must configure the authentication and authorization parameters
# in accordance with the API server security policy.
# Examples for each auth method are provided below, use the example that
# satisfies your auth use case.

# Configure Bearer authorization: accessToken
configuration = IpfsPinningSDK.Configuration(
    access_token = 'YOUR_BEARER_TOKEN'
)

# Enter a context with an instance of the API client
with IpfsPinningSDK.ApiClient(configuration) as api_client:
    # Create an instance of the API class
    api_instance = pins_api.PinsApi(api_client)
    requestid = "requestid_example" # str | 

    # example passing only required values which don't have defaults set
    try:
        # Get pin object
        api_response = api_instance.pins_requestid_get(requestid)
        pprint(api_response)
    except IpfsPinningSDK.ApiException as e:
        print("Exception when calling PinsApi->pins_requestid_get: %s\n" % e)
```


### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **requestid** | **str**|  |

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
**200** | Successful response (PinStatus object) |  -  |
**400** | Error response (Bad request) |  -  |
**401** | Error response (Unauthorized; access token is missing or invalid) |  -  |
**404** | Error response (The specified resource was not found) |  -  |
**409** | Error response (Insufficient funds) |  -  |
**4XX** | Error response (Custom service error) |  -  |
**5XX** | Error response (Unexpected internal server error) |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **pins_requestid_post**
> PinStatus pins_requestid_post(requestid, pin)

Replace pin object

Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)

### Example

* Bearer Authentication (accessToken):
```python
import time
import IpfsPinningSDK
from IpfsPinningSDK.api import pins_api
from IpfsPinningSDK.model.pin import Pin
from IpfsPinningSDK.model.pin_status import PinStatus
from IpfsPinningSDK.model.failure import Failure
from pprint import pprint
# Defining the host is optional and defaults to https://pinning-service.example.com
# See configuration.py for a list of all supported configuration parameters.
configuration = IpfsPinningSDK.Configuration(
    host = "https://pinning-service.example.com"
)

# The client must configure the authentication and authorization parameters
# in accordance with the API server security policy.
# Examples for each auth method are provided below, use the example that
# satisfies your auth use case.

# Configure Bearer authorization: accessToken
configuration = IpfsPinningSDK.Configuration(
    access_token = 'YOUR_BEARER_TOKEN'
)

# Enter a context with an instance of the API client
with IpfsPinningSDK.ApiClient(configuration) as api_client:
    # Create an instance of the API class
    api_instance = pins_api.PinsApi(api_client)
    requestid = "requestid_example" # str | 
    pin = Pin(
        cid="QmCIDToBePinned",
        name="PreciousData.pdf",
        origins=Origins(["/ip4/203.0.113.142/tcp/4001/p2p/QmSourcePeerId","/ip4/203.0.113.114/udp/4001/quic/p2p/QmSourcePeerId"]),
        meta=PinMeta(
            key="key_example",
        ),
    ) # Pin | 

    # example passing only required values which don't have defaults set
    try:
        # Replace pin object
        api_response = api_instance.pins_requestid_post(requestid, pin)
        pprint(api_response)
    except IpfsPinningSDK.ApiException as e:
        print("Exception when calling PinsApi->pins_requestid_post: %s\n" % e)
```


### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **requestid** | **str**|  |
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
**202** | Successful response (PinStatus object) |  -  |
**400** | Error response (Bad request) |  -  |
**401** | Error response (Unauthorized; access token is missing or invalid) |  -  |
**404** | Error response (The specified resource was not found) |  -  |
**409** | Error response (Insufficient funds) |  -  |
**4XX** | Error response (Custom service error) |  -  |
**5XX** | Error response (Unexpected internal server error) |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

