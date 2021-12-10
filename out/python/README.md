# IpfsPinningSDK


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



This Python package is automatically generated by the [OpenAPI Generator](https://openapi-generator.tech) project:

- API version: 1.0.0
- Package version: 1.0.0
- Build package: org.openapitools.codegen.languages.PythonClientCodegen

## Requirements.

Python >= 3.6

## Installation & Usage
### pip install

If the python package is hosted on a repository, you can install directly using:

```sh
pip install git+https://github.com/GIT_USER_ID/GIT_REPO_ID.git
```
(you may need to run `pip` with root permission: `sudo pip install git+https://github.com/GIT_USER_ID/GIT_REPO_ID.git`)

Then import the package:
```python
import IpfsPinningSDK
```

### Setuptools

Install via [Setuptools](http://pypi.python.org/pypi/setuptools).

```sh
python setup.py install --user
```
(or `sudo python setup.py install` to install the package for all users)

Then import the package:
```python
import IpfsPinningSDK
```

## Getting Started

Please follow the [installation procedure](#installation--usage) and then run the following:

```python

import time
import IpfsPinningSDK
from pprint import pprint
from IpfsPinningSDK.api import pins_api
from IpfsPinningSDK.model.failure import Failure
from IpfsPinningSDK.model.pin import Pin
from IpfsPinningSDK.model.pin_meta import PinMeta
from IpfsPinningSDK.model.pin_results import PinResults
from IpfsPinningSDK.model.pin_status import PinStatus
from IpfsPinningSDK.model.status import Status
from IpfsPinningSDK.model.text_matching_strategy import TextMatchingStrategy
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
limit = 10 # int | Max records to return (optional) (default to 10)
meta =  # PinMeta | Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)

    try:
        # List pin objects
        api_response = api_instance.pins_get(cid=cid, name=name, match=match, status=status, before=before, after=after, limit=limit, meta=meta)
        pprint(api_response)
    except IpfsPinningSDK.ApiException as e:
        print("Exception when calling PinsApi->pins_get: %s\n" % e)
```

## Documentation for API Endpoints

All URIs are relative to *https://pinning-service.example.com*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*PinsApi* | [**pins_get**](docs/PinsApi.md#pins_get) | **GET** /pins | List pin objects
*PinsApi* | [**pins_post**](docs/PinsApi.md#pins_post) | **POST** /pins | Add pin object
*PinsApi* | [**pins_requestid_delete**](docs/PinsApi.md#pins_requestid_delete) | **DELETE** /pins/{requestid} | Remove pin object
*PinsApi* | [**pins_requestid_get**](docs/PinsApi.md#pins_requestid_get) | **GET** /pins/{requestid} | Get pin object
*PinsApi* | [**pins_requestid_post**](docs/PinsApi.md#pins_requestid_post) | **POST** /pins/{requestid} | Replace pin object


## Documentation For Models

 - [Delegates](docs/Delegates.md)
 - [Failure](docs/Failure.md)
 - [FailureError](docs/FailureError.md)
 - [Origins](docs/Origins.md)
 - [Pin](docs/Pin.md)
 - [PinMeta](docs/PinMeta.md)
 - [PinResults](docs/PinResults.md)
 - [PinStatus](docs/PinStatus.md)
 - [Status](docs/Status.md)
 - [StatusInfo](docs/StatusInfo.md)
 - [TextMatchingStrategy](docs/TextMatchingStrategy.md)


## Documentation For Authorization


## accessToken

- **Type**: Bearer authentication


## Author




## Notes for Large OpenAPI documents
If the OpenAPI document is large, imports in IpfsPinningSDK.apis and IpfsPinningSDK.models may fail with a
RecursionError indicating the maximum recursion limit has been exceeded. In that case, there are a couple of solutions:

Solution 1:
Use specific imports for apis and models like:
- `from IpfsPinningSDK.api.default_api import DefaultApi`
- `from IpfsPinningSDK.model.pet import Pet`

Solution 2:
Before importing the package, adjust the maximum recursion limit as shown below:
```
import sys
sys.setrecursionlimit(1500)
import IpfsPinningSDK
from IpfsPinningSDK.apis import *
from IpfsPinningSDK.models import *
```

