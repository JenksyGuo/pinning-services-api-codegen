# .PinsApi

All URIs are relative to *https://pinning-service.example.com*

Method | HTTP request | Description
------------- | ------------- | -------------
[**pinsGet**](PinsApi.md#pinsGet) | **GET** /pins | List pin objects
[**pinsPost**](PinsApi.md#pinsPost) | **POST** /pins | Add pin object
[**pinsRequestidDelete**](PinsApi.md#pinsRequestidDelete) | **DELETE** /pins/{requestid} | Remove pin object
[**pinsRequestidGet**](PinsApi.md#pinsRequestidGet) | **GET** /pins/{requestid} | Get pin object
[**pinsRequestidPost**](PinsApi.md#pinsRequestidPost) | **POST** /pins/{requestid} | Replace pin object


# **pinsGet**
> PinResults pinsGet()

List all the pin objects, matching optional filters; when no filter is provided, only successful pins are returned

### Example


```typescript
import {  } from '';
import * as fs from 'fs';

const configuration = .createConfiguration();
const apiInstance = new .PinsApi(configuration);

let body:.PinsApiPinsGetRequest = {
  // Set<string> | Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional)
  cid: ["Qm1","Qm2","bafy3"],
  // string | Return pin objects with specified name (by default a case-sensitive, exact match) (optional)
  name: "PreciousData.pdf",
  // TextMatchingStrategy | Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional)
  match: "exact",
  // Set<Status> | Return pin objects for pins with the specified status (optional)
  status: [
    "["queued","pinning"]",
  ],
  // Date | Return results created (queued) before provided timestamp (optional)
  before: new Date('2020-07-27T17:32:28Z'),
  // Date | Return results created (queued) after provided timestamp (optional)
  after: new Date('2020-07-27T17:32:28Z'),
  // number | Max records to return (optional)
  limit: 10,
  // { [key: string]: string; } | Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)
  meta: ,
};

apiInstance.pinsGet(body).then((data:any) => {
  console.log('API called successfully. Returned data: ' + data);
}).catch((error:any) => console.error(error));
```


### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **cid** | **Set&lt;string&gt;** | Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts | (optional) defaults to undefined
 **name** | [**string**] | Return pin objects with specified name (by default a case-sensitive, exact match) | (optional) defaults to undefined
 **match** | **TextMatchingStrategy** | Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies | (optional) defaults to undefined
 **status** | **Set&lt;Status&gt;** | Return pin objects for pins with the specified status | (optional) defaults to undefined
 **before** | [**Date**] | Return results created (queued) before provided timestamp | (optional) defaults to undefined
 **after** | [**Date**] | Return results created (queued) after provided timestamp | (optional) defaults to undefined
 **limit** | [**number**] | Max records to return | (optional) defaults to 10
 **meta** | **{ [key: string]: string; }** | Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport | (optional) defaults to undefined


### Return type

**PinResults**

### Authorization

[accessToken](README.md#accessToken)

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

[[Back to top]](#) [[Back to API list]](README.md#documentation-for-api-endpoints) [[Back to Model list]](README.md#documentation-for-models) [[Back to README]](README.md)

# **pinsPost**
> PinStatus pinsPost(pin)

Add a new pin object for the current access token

### Example


```typescript
import {  } from '';
import * as fs from 'fs';

const configuration = .createConfiguration();
const apiInstance = new .PinsApi(configuration);

let body:.PinsApiPinsPostRequest = {
  // Pin
  pin: {
    cid: "QmCIDToBePinned",
    name: "PreciousData.pdf",
    origins: ["/ip4/203.0.113.142/tcp/4001/p2p/QmSourcePeerId","/ip4/203.0.113.114/udp/4001/quic/p2p/QmSourcePeerId"],
    meta: {
      "key": "key_example",
    },
  },
};

apiInstance.pinsPost(body).then((data:any) => {
  console.log('API called successfully. Returned data: ' + data);
}).catch((error:any) => console.error(error));
```


### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **pin** | **Pin**|  |


### Return type

**PinStatus**

### Authorization

[accessToken](README.md#accessToken)

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

[[Back to top]](#) [[Back to API list]](README.md#documentation-for-api-endpoints) [[Back to Model list]](README.md#documentation-for-models) [[Back to README]](README.md)

# **pinsRequestidDelete**
> void pinsRequestidDelete()

Remove a pin object

### Example


```typescript
import {  } from '';
import * as fs from 'fs';

const configuration = .createConfiguration();
const apiInstance = new .PinsApi(configuration);

let body:.PinsApiPinsRequestidDeleteRequest = {
  // string
  requestid: "requestid_example",
};

apiInstance.pinsRequestidDelete(body).then((data:any) => {
  console.log('API called successfully. Returned data: ' + data);
}).catch((error:any) => console.error(error));
```


### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **requestid** | [**string**] |  | defaults to undefined


### Return type

**void**

### Authorization

[accessToken](README.md#accessToken)

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

[[Back to top]](#) [[Back to API list]](README.md#documentation-for-api-endpoints) [[Back to Model list]](README.md#documentation-for-models) [[Back to README]](README.md)

# **pinsRequestidGet**
> PinStatus pinsRequestidGet()

Get a pin object and its status

### Example


```typescript
import {  } from '';
import * as fs from 'fs';

const configuration = .createConfiguration();
const apiInstance = new .PinsApi(configuration);

let body:.PinsApiPinsRequestidGetRequest = {
  // string
  requestid: "requestid_example",
};

apiInstance.pinsRequestidGet(body).then((data:any) => {
  console.log('API called successfully. Returned data: ' + data);
}).catch((error:any) => console.error(error));
```


### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **requestid** | [**string**] |  | defaults to undefined


### Return type

**PinStatus**

### Authorization

[accessToken](README.md#accessToken)

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

[[Back to top]](#) [[Back to API list]](README.md#documentation-for-api-endpoints) [[Back to Model list]](README.md#documentation-for-models) [[Back to README]](README.md)

# **pinsRequestidPost**
> PinStatus pinsRequestidPost(pin)

Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)

### Example


```typescript
import {  } from '';
import * as fs from 'fs';

const configuration = .createConfiguration();
const apiInstance = new .PinsApi(configuration);

let body:.PinsApiPinsRequestidPostRequest = {
  // string
  requestid: "requestid_example",
  // Pin
  pin: {
    cid: "QmCIDToBePinned",
    name: "PreciousData.pdf",
    origins: ["/ip4/203.0.113.142/tcp/4001/p2p/QmSourcePeerId","/ip4/203.0.113.114/udp/4001/quic/p2p/QmSourcePeerId"],
    meta: {
      "key": "key_example",
    },
  },
};

apiInstance.pinsRequestidPost(body).then((data:any) => {
  console.log('API called successfully. Returned data: ' + data);
}).catch((error:any) => console.error(error));
```


### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **pin** | **Pin**|  |
 **requestid** | [**string**] |  | defaults to undefined


### Return type

**PinStatus**

### Authorization

[accessToken](README.md#accessToken)

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

[[Back to top]](#) [[Back to API list]](README.md#documentation-for-api-endpoints) [[Back to Model list]](README.md#documentation-for-models) [[Back to README]](README.md)


