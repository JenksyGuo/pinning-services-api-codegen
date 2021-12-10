# OpenAPI\Client\PinsApi

All URIs are relative to https://pinning-service.example.com.

Method | HTTP request | Description
------------- | ------------- | -------------
[**pinsGet()**](PinsApi.md#pinsGet) | **GET** /pins | List pin objects
[**pinsPost()**](PinsApi.md#pinsPost) | **POST** /pins | Add pin object
[**pinsRequestidDelete()**](PinsApi.md#pinsRequestidDelete) | **DELETE** /pins/{requestid} | Remove pin object
[**pinsRequestidGet()**](PinsApi.md#pinsRequestidGet) | **GET** /pins/{requestid} | Get pin object
[**pinsRequestidPost()**](PinsApi.md#pinsRequestidPost) | **POST** /pins/{requestid} | Replace pin object


## `pinsGet()`

```php
pinsGet($cid, $name, $match, $status, $before, $after, $limit, $meta): \OpenAPI\Client\Model\PinResults
```

List pin objects

List all the pin objects, matching optional filters; when no filter is provided, only successful pins are returned

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: accessToken
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\PinsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$cid = ["Qm1","Qm2","bafy3"]; // string[] | Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts
$name = PreciousData.pdf; // string | Return pin objects with specified name (by default a case-sensitive, exact match)
$match = exact; // \OpenAPI\Client\Model\TextMatchingStrategy | Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies
$status = ["queued","pinning"]; // \OpenAPI\Client\Model\Status[] | Return pin objects for pins with the specified status
$before = 2020-07-27T17:32:28Z; // \DateTime | Return results created (queued) before provided timestamp
$after = 2020-07-27T17:32:28Z; // \DateTime | Return results created (queued) after provided timestamp
$limit = 10; // int | Max records to return
$meta = array('key' => 'meta_example'); // array<string,string> | Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport

try {
    $result = $apiInstance->pinsGet($cid, $name, $match, $status, $before, $after, $limit, $meta);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PinsApi->pinsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **cid** | [**string[]**](../Model/string.md)| Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts | [optional]
 **name** | **string**| Return pin objects with specified name (by default a case-sensitive, exact match) | [optional]
 **match** | [**\OpenAPI\Client\Model\TextMatchingStrategy**](../Model/.md)| Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies | [optional]
 **status** | [**\OpenAPI\Client\Model\Status[]**](../Model/\OpenAPI\Client\Model\Status.md)| Return pin objects for pins with the specified status | [optional]
 **before** | **\DateTime**| Return results created (queued) before provided timestamp | [optional]
 **after** | **\DateTime**| Return results created (queued) after provided timestamp | [optional]
 **limit** | **int**| Max records to return | [optional] [default to 10]
 **meta** | [**array<string,string>**](../Model/string.md)| Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport | [optional]

### Return type

[**\OpenAPI\Client\Model\PinResults**](../Model/PinResults.md)

### Authorization

[accessToken](../../README.md#accessToken)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `pinsPost()`

```php
pinsPost($pin): \OpenAPI\Client\Model\PinStatus
```

Add pin object

Add a new pin object for the current access token

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: accessToken
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\PinsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$pin = new \OpenAPI\Client\Model\Pin(); // \OpenAPI\Client\Model\Pin

try {
    $result = $apiInstance->pinsPost($pin);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PinsApi->pinsPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **pin** | [**\OpenAPI\Client\Model\Pin**](../Model/Pin.md)|  |

### Return type

[**\OpenAPI\Client\Model\PinStatus**](../Model/PinStatus.md)

### Authorization

[accessToken](../../README.md#accessToken)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `pinsRequestidDelete()`

```php
pinsRequestidDelete($requestid)
```

Remove pin object

Remove a pin object

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: accessToken
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\PinsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$requestid = 'requestid_example'; // string

try {
    $apiInstance->pinsRequestidDelete($requestid);
} catch (Exception $e) {
    echo 'Exception when calling PinsApi->pinsRequestidDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **requestid** | **string**|  |

### Return type

void (empty response body)

### Authorization

[accessToken](../../README.md#accessToken)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `pinsRequestidGet()`

```php
pinsRequestidGet($requestid): \OpenAPI\Client\Model\PinStatus
```

Get pin object

Get a pin object and its status

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: accessToken
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\PinsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$requestid = 'requestid_example'; // string

try {
    $result = $apiInstance->pinsRequestidGet($requestid);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PinsApi->pinsRequestidGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **requestid** | **string**|  |

### Return type

[**\OpenAPI\Client\Model\PinStatus**](../Model/PinStatus.md)

### Authorization

[accessToken](../../README.md#accessToken)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `pinsRequestidPost()`

```php
pinsRequestidPost($requestid, $pin): \OpenAPI\Client\Model\PinStatus
```

Replace pin object

Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: accessToken
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\PinsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$requestid = 'requestid_example'; // string
$pin = new \OpenAPI\Client\Model\Pin(); // \OpenAPI\Client\Model\Pin

try {
    $result = $apiInstance->pinsRequestidPost($requestid, $pin);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PinsApi->pinsRequestidPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **requestid** | **string**|  |
 **pin** | [**\OpenAPI\Client\Model\Pin**](../Model/Pin.md)|  |

### Return type

[**\OpenAPI\Client\Model\PinStatus**](../Model/PinStatus.md)

### Authorization

[accessToken](../../README.md#accessToken)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
