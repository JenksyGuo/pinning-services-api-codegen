# IpfsPinning.SDK - the C# library for the IPFS Pinning Service API



## About this spec
The IPFS Pinning Service API is intended to be an implementation-agnostic API&#x3a;
- For use and implementation by pinning service providers
- For use in client mode by IPFS nodes and GUI-based applications

### Document scope and intended audience
The intended audience of this document is **IPFS developers** building pinning service clients or servers compatible with this OpenAPI spec. Your input and feedback are welcome and valuable as we develop this API spec. Please join the design discussion at [github.com/ipfs/pinning-services-api-spec](https://github.com/ipfs/pinning-services-api-spec).

**IPFS users** should see the tutorial at [docs.ipfs.io/how-to/work-with-pinning-services](https://docs.ipfs.io/how-to/work-with-pinning-services/) instead.

### Related resources
The latest version of this spec and additional resources can be found at:
- Specification: https://github.com/ipfs/pinning-services-api-spec/raw/main/ipfs-pinning-service.yaml
- Docs: https://ipfs.github.io/pinning-services-api-spec/
- Clients and services: https://github.com/ipfs/pinning-services-api-spec#adoption

# Schemas
This section describes the most important object types and conventions.

A full list of fields and schemas can be found in the `schemas` section of the [YAML file](https://github.com/ipfs/pinning-services-api-spec/blob/master/ipfs-pinning-service.yaml).

## Identifiers
### cid
[Content Identifier (CID)](https://docs.ipfs.io/concepts/content-addressing/) points at the root of a DAG that is pinned recursively.
### requestid
Unique identifier of a pin request.

When a pin is created, the service responds with unique `requestid` that can be later used for pin removal. When the same `cid` is pinned again, a different `requestid` is returned to differentiate between those pin requests.

Service implementation should use UUID, `hash(accessToken,Pin,PinStatus.created)`, or any other opaque identifier that provides equally strong protection against race conditions.

## Objects
### Pin object

![pin object](https://bafybeideck2fchyxna4wqwc2mo67yriokehw3yujboc5redjdaajrk2fjq.ipfs.dweb.link/pin.png)

The `Pin` object is a representation of a pin request.

It includes the `cid` of data to be pinned, as well as optional metadata in `name`, `origins`, and `meta`.

### Pin status response

![pin status response object](https://bafybeideck2fchyxna4wqwc2mo67yriokehw3yujboc5redjdaajrk2fjq.ipfs.dweb.link/pinstatus.png)

The `PinStatus` object is a representation of the current state of a pinning operation.
It includes the original `pin` object, along with the current `status` and globally unique `requestid` of the entire pinning request, which can be used for future status checks and management. Addresses in the `delegates` array are peers delegated by the pinning service for facilitating direct file transfers (more details in the provider hints section). Any additional vendor-specific information is returned in optional `info`.

# The pin lifecycle

![pinning service objects and lifecycle](https://bafybeideck2fchyxna4wqwc2mo67yriokehw3yujboc5redjdaajrk2fjq.ipfs.dweb.link/lifecycle.png)

## Creating a new pin object
The user sends a `Pin` object to `POST /pins` and receives a `PinStatus` response:
- `requestid` in `PinStatus` is the identifier of the pin operation, which can can be used for checking status, and removing the pin in the future
- `status` in `PinStatus` indicates the current state of a pin

## Checking status of in-progress pinning
`status` (in `PinStatus`) may indicate a pending state (`queued` or `pinning`). This means the data behind `Pin.cid` was not found on the pinning service and is being fetched from the IPFS network at large, which may take time.

In this case, the user can periodically check pinning progress via `GET /pins/{requestid}` until pinning is successful, or the user decides to remove the pending pin.

## Replacing an existing pin object
The user can replace an existing pin object via `POST /pins/{requestid}`. This is a shortcut for removing a pin object identified by `requestid` and creating a new one in a single API call that protects against undesired garbage collection of blocks common to both pins. Useful when updating a pin representing a huge dataset where most of blocks did not change. The new pin object `requestid` is returned in the `PinStatus` response. The old pin object is deleted automatically.

## Removing a pin object
A pin object can be removed via `DELETE /pins/{requestid}`.


# Provider hints
A pinning service will use the DHT and other discovery methods to locate pinned content; however, it is a good practice to provide additional provider hints to speed up the discovery phase and start the transfer immediately, especially if a client has the data in their own datastore or already knows of other providers.

The most common scenario is a client putting its own IPFS node's multiaddrs in `Pin.origins`,  and then attempt to connect to every multiaddr returned by a pinning service in `PinStatus.delegates` to initiate transfer.  At the same time, a pinning service will try to connect to multiaddrs provided by the client in `Pin.origins`.

This ensures data transfer starts immediately (without waiting for provider discovery over DHT), and mutual direct dial between a client and a service works around peer routing issues in restrictive network topologies, such as NATs, firewalls, etc.

**NOTE:** Connections to multiaddrs in `origins` and `delegates` arrays should be attempted in best-effort fashion, and dial failure should not fail the pinning operation. When unable to act on explicit provider hints, DHT and other discovery methods should be used as a fallback by a pinning service.

**NOTE:** All multiaddrs MUST end with `/p2p/{peerID}` and SHOULD be fully resolved and confirmed to be dialable from the public internet. Avoid sending addresses from local networks.

# Custom metadata
Pinning services are encouraged to add support for additional features by leveraging the optional `Pin.meta` and `PinStatus.info` fields. While these attributes can be application- or vendor-specific, we encourage the community at large to leverage these attributes as a sandbox to come up with conventions that could become part of future revisions of this API.
## Pin metadata
String keys and values passed in `Pin.meta` are persisted with the pin object. This is an opt-in feature: It is OK for a client to omit or ignore these optional attributes, and doing so should not impact the basic pinning functionality.

Potential uses:
- `Pin.meta[app_id]`: Attaching a unique identifier to pins created by an app enables meta-filtering pins per app
- `Pin.meta[vendor_policy]`: Vendor-specific policy (for example: which region to use, how many copies to keep)

### Filtering based on metadata
The contents of `Pin.meta` can be used as an advanced search filter for situations where searching by `name` and `cid` is not enough.

Metadata key matching rule is `AND`:
- lookup returns pins that have `meta` with all key-value pairs matching the passed values
- pin metadata may have more keys, but only ones passed in the query are used for filtering

The wire format for the `meta` when used as a query parameter is a [URL-escaped](https://en.wikipedia.org/wiki/Percent-encoding) stringified JSON object. A lookup example for pins that have a `meta` key-value pair `{\"app_id\":\"UUID\"}` is:
- `GET /pins?meta=%7B%22app_id%22%3A%22UUID%22%7D`


## Pin status info
Additional `PinStatus.info` can be returned by pinning service.

Potential uses:
- `PinStatus.info[status_details]`: more info about the current status (queue position, percentage of transferred data, summary of where data is stored, etc); when `PinStatus.status=failed`, it could provide a reason why a pin operation failed (e.g. lack of funds, DAG too big, etc.)
- `PinStatus.info[dag_size]`: the size of pinned data, along with DAG overhead
- `PinStatus.info[raw_size]`: the size of data without DAG overhead (eg. unixfs)
- `PinStatus.info[pinned_until]`: if vendor supports time-bound pins, this could indicate when the pin will expire

# Pagination and filtering
Pin objects can be listed by executing `GET /pins` with optional parameters:

- When no filters are provided, the endpoint will return a small batch of the 10 most recently created items, from the latest to the oldest.
- The number of returned items can be adjusted with the `limit` parameter (implicit default is 10).
- If the value in `PinResults.count` is bigger than the length of `PinResults.results`, the client can infer there are more results that can be queried.
- To read more items, pass the `before` filter with the timestamp from `PinStatus.created` found in the oldest item in the current batch of results. Repeat to read all results.
- Returned results can be fine-tuned by applying optional `after`, `cid`, `name`, `status`, or `meta` filters.

> **Note**: pagination by the `created` timestamp requires each value to be globally unique. Any future considerations to add support for bulk creation must account for this.



This C# SDK is automatically generated by the [OpenAPI Generator](https://openapi-generator.tech) project:

- API version: 1.0.0
- SDK version: 1.0.0
- Build package: org.openapitools.codegen.languages.CSharpNetCoreClientCodegen

<a name="frameworks-supported"></a>
## Frameworks supported
- .NET Core >=1.0
- .NET Framework >=4.6
- Mono/Xamarin >=vNext

<a name="dependencies"></a>
## Dependencies

- [RestSharp](https://www.nuget.org/packages/RestSharp) - 106.11.7 or later
- [Json.NET](https://www.nuget.org/packages/Newtonsoft.Json/) - 12.0.3 or later
- [JsonSubTypes](https://www.nuget.org/packages/JsonSubTypes/) - 1.8.0 or later
- [System.ComponentModel.Annotations](https://www.nuget.org/packages/System.ComponentModel.Annotations) - 5.0.0 or later

The DLLs included in the package may not be the latest version. We recommend using [NuGet](https://docs.nuget.org/consume/installing-nuget) to obtain the latest version of the packages:
```
Install-Package RestSharp
Install-Package Newtonsoft.Json
Install-Package JsonSubTypes
Install-Package System.ComponentModel.Annotations
```

NOTE: RestSharp versions greater than 105.1.0 have a bug which causes file uploads to fail. See [RestSharp#742](https://github.com/restsharp/RestSharp/issues/742).
NOTE: RestSharp for .Net Core creates a new socket for each api call, which can lead to a socket exhaustion problem. See [RestSharp#1406](https://github.com/restsharp/RestSharp/issues/1406).

<a name="installation"></a>
## Installation
Generate the DLL using your preferred tool (e.g. `dotnet build`)

Then include the DLL (under the `bin` folder) in the C# project, and use the namespaces:
```csharp
using IpfsPinning.SDK.Api;
using IpfsPinning.SDK.Client;
using IpfsPinning.SDK.Model;
```
<a name="usage"></a>
## Usage

To use the API client with a HTTP proxy, setup a `System.Net.WebProxy`
```csharp
Configuration c = new Configuration();
System.Net.WebProxy webProxy = new System.Net.WebProxy("http://myProxyUrl:80/");
webProxy.Credentials = System.Net.CredentialCache.DefaultCredentials;
c.Proxy = webProxy;
```

<a name="getting-started"></a>
## Getting Started

```csharp
using System.Collections.Generic;
using System.Diagnostics;
using IpfsPinning.SDK.Api;
using IpfsPinning.SDK.Client;
using IpfsPinning.SDK.Model;

namespace Example
{
    public class Example
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
            catch (ApiException e)
            {
                Debug.Print("Exception when calling PinsApi.PinsGet: " + e.Message );
                Debug.Print("Status Code: "+ e.ErrorCode);
                Debug.Print(e.StackTrace);
            }

        }
    }
}
```

<a name="documentation-for-api-endpoints"></a>
## Documentation for API Endpoints

All URIs are relative to *https://pinning-service.example.com*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*PinsApi* | [**PinsGet**](docs/PinsApi.md#pinsget) | **GET** /pins | List pin objects
*PinsApi* | [**PinsPost**](docs/PinsApi.md#pinspost) | **POST** /pins | Add pin object
*PinsApi* | [**PinsRequestidDelete**](docs/PinsApi.md#pinsrequestiddelete) | **DELETE** /pins/{requestid} | Remove pin object
*PinsApi* | [**PinsRequestidGet**](docs/PinsApi.md#pinsrequestidget) | **GET** /pins/{requestid} | Get pin object
*PinsApi* | [**PinsRequestidPost**](docs/PinsApi.md#pinsrequestidpost) | **POST** /pins/{requestid} | Replace pin object


<a name="documentation-for-models"></a>
## Documentation for Models

 - [Model.Failure](docs/Failure.md)
 - [Model.FailureError](docs/FailureError.md)
 - [Model.Pin](docs/Pin.md)
 - [Model.PinResults](docs/PinResults.md)
 - [Model.PinStatus](docs/PinStatus.md)
 - [Model.Status](docs/Status.md)
 - [Model.TextMatchingStrategy](docs/TextMatchingStrategy.md)


<a name="documentation-for-authorization"></a>
## Documentation for Authorization

<a name="accessToken"></a>
### accessToken

- **Type**: Bearer Authentication

