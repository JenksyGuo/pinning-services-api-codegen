<?php
/**
 * PinsApi
 * PHP version 7.3
 *
 * @category Class
 * @package  OpenAPI\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * IPFS Pinning Service API
 *
 * ## About this spec The IPFS Pinning Service API is intended to be an implementation-agnostic API&#x3a; - For use and implementation by pinning service providers - For use in client mode by IPFS nodes and GUI-based applications  ### Document scope and intended audience The intended audience of this document is **IPFS developers** building pinning service clients or servers compatible with this OpenAPI spec. Your input and feedback are welcome and valuable as we develop this API spec. Please join the design discussion at [github.com/ipfs/pinning-services-api-spec](https://github.com/ipfs/pinning-services-api-spec).  **IPFS users** should see the tutorial at [docs.ipfs.io/how-to/work-with-pinning-services](https://docs.ipfs.io/how-to/work-with-pinning-services/) instead.  ### Related resources The latest version of this spec and additional resources can be found at: - Specification: https://github.com/ipfs/pinning-services-api-spec/raw/main/ipfs-pinning-service.yaml - Docs: https://ipfs.github.io/pinning-services-api-spec/ - Clients and services: https://github.com/ipfs/pinning-services-api-spec#adoption  # Schemas This section describes the most important object types and conventions.  A full list of fields and schemas can be found in the `schemas` section of the [YAML file](https://github.com/ipfs/pinning-services-api-spec/blob/master/ipfs-pinning-service.yaml).  ## Identifiers ### cid [Content Identifier (CID)](https://docs.ipfs.io/concepts/content-addressing/) points at the root of a DAG that is pinned recursively. ### requestid Unique identifier of a pin request.  When a pin is created, the service responds with unique `requestid` that can be later used for pin removal. When the same `cid` is pinned again, a different `requestid` is returned to differentiate between those pin requests.  Service implementation should use UUID, `hash(accessToken,Pin,PinStatus.created)`, or any other opaque identifier that provides equally strong protection against race conditions.  ## Objects ### Pin object  ![pin object](https://bafybeideck2fchyxna4wqwc2mo67yriokehw3yujboc5redjdaajrk2fjq.ipfs.dweb.link/pin.png)  The `Pin` object is a representation of a pin request.  It includes the `cid` of data to be pinned, as well as optional metadata in `name`, `origins`, and `meta`.  ### Pin status response  ![pin status response object](https://bafybeideck2fchyxna4wqwc2mo67yriokehw3yujboc5redjdaajrk2fjq.ipfs.dweb.link/pinstatus.png)  The `PinStatus` object is a representation of the current state of a pinning operation. It includes the original `pin` object, along with the current `status` and globally unique `requestid` of the entire pinning request, which can be used for future status checks and management. Addresses in the `delegates` array are peers delegated by the pinning service for facilitating direct file transfers (more details in the provider hints section). Any additional vendor-specific information is returned in optional `info`.  # The pin lifecycle  ![pinning service objects and lifecycle](https://bafybeideck2fchyxna4wqwc2mo67yriokehw3yujboc5redjdaajrk2fjq.ipfs.dweb.link/lifecycle.png)  ## Creating a new pin object The user sends a `Pin` object to `POST /pins` and receives a `PinStatus` response: - `requestid` in `PinStatus` is the identifier of the pin operation, which can can be used for checking status, and removing the pin in the future - `status` in `PinStatus` indicates the current state of a pin  ## Checking status of in-progress pinning `status` (in `PinStatus`) may indicate a pending state (`queued` or `pinning`). This means the data behind `Pin.cid` was not found on the pinning service and is being fetched from the IPFS network at large, which may take time.  In this case, the user can periodically check pinning progress via `GET /pins/{requestid}` until pinning is successful, or the user decides to remove the pending pin.  ## Replacing an existing pin object The user can replace an existing pin object via `POST /pins/{requestid}`. This is a shortcut for removing a pin object identified by `requestid` and creating a new one in a single API call that protects against undesired garbage collection of blocks common to both pins. Useful when updating a pin representing a huge dataset where most of blocks did not change. The new pin object `requestid` is returned in the `PinStatus` response. The old pin object is deleted automatically.  ## Removing a pin object A pin object can be removed via `DELETE /pins/{requestid}`.   # Provider hints A pinning service will use the DHT and other discovery methods to locate pinned content; however, it is a good practice to provide additional provider hints to speed up the discovery phase and start the transfer immediately, especially if a client has the data in their own datastore or already knows of other providers.  The most common scenario is a client putting its own IPFS node's multiaddrs in `Pin.origins`,  and then attempt to connect to every multiaddr returned by a pinning service in `PinStatus.delegates` to initiate transfer.  At the same time, a pinning service will try to connect to multiaddrs provided by the client in `Pin.origins`.  This ensures data transfer starts immediately (without waiting for provider discovery over DHT), and mutual direct dial between a client and a service works around peer routing issues in restrictive network topologies, such as NATs, firewalls, etc.  **NOTE:** Connections to multiaddrs in `origins` and `delegates` arrays should be attempted in best-effort fashion, and dial failure should not fail the pinning operation. When unable to act on explicit provider hints, DHT and other discovery methods should be used as a fallback by a pinning service.  **NOTE:** All multiaddrs MUST end with `/p2p/{peerID}` and SHOULD be fully resolved and confirmed to be dialable from the public internet. Avoid sending addresses from local networks.  # Custom metadata Pinning services are encouraged to add support for additional features by leveraging the optional `Pin.meta` and `PinStatus.info` fields. While these attributes can be application- or vendor-specific, we encourage the community at large to leverage these attributes as a sandbox to come up with conventions that could become part of future revisions of this API. ## Pin metadata String keys and values passed in `Pin.meta` are persisted with the pin object. This is an opt-in feature: It is OK for a client to omit or ignore these optional attributes, and doing so should not impact the basic pinning functionality.  Potential uses: - `Pin.meta[app_id]`: Attaching a unique identifier to pins created by an app enables meta-filtering pins per app - `Pin.meta[vendor_policy]`: Vendor-specific policy (for example: which region to use, how many copies to keep)  ### Filtering based on metadata The contents of `Pin.meta` can be used as an advanced search filter for situations where searching by `name` and `cid` is not enough.  Metadata key matching rule is `AND`: - lookup returns pins that have `meta` with all key-value pairs matching the passed values - pin metadata may have more keys, but only ones passed in the query are used for filtering  The wire format for the `meta` when used as a query parameter is a [URL-escaped](https://en.wikipedia.org/wiki/Percent-encoding) stringified JSON object. A lookup example for pins that have a `meta` key-value pair `{\"app_id\":\"UUID\"}` is: - `GET /pins?meta=%7B%22app_id%22%3A%22UUID%22%7D`   ## Pin status info Additional `PinStatus.info` can be returned by pinning service.  Potential uses: - `PinStatus.info[status_details]`: more info about the current status (queue position, percentage of transferred data, summary of where data is stored, etc); when `PinStatus.status=failed`, it could provide a reason why a pin operation failed (e.g. lack of funds, DAG too big, etc.) - `PinStatus.info[dag_size]`: the size of pinned data, along with DAG overhead - `PinStatus.info[raw_size]`: the size of data without DAG overhead (eg. unixfs) - `PinStatus.info[pinned_until]`: if vendor supports time-bound pins, this could indicate when the pin will expire  # Pagination and filtering Pin objects can be listed by executing `GET /pins` with optional parameters:  - When no filters are provided, the endpoint will return a small batch of the 10 most recently created items, from the latest to the oldest. - The number of returned items can be adjusted with the `limit` parameter (implicit default is 10). - If the value in `PinResults.count` is bigger than the length of `PinResults.results`, the client can infer there are more results that can be queried. - To read more items, pass the `before` filter with the timestamp from `PinStatus.created` found in the oldest item in the current batch of results. Repeat to read all results. - Returned results can be fine-tuned by applying optional `after`, `cid`, `name`, `status`, or `meta` filters.  > **Note**: pagination by the `created` timestamp requires each value to be globally unique. Any future considerations to add support for bulk creation must account for this.
 *
 * The version of the OpenAPI document: 1.0.0
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 5.2.1
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace OpenAPI\Client\Api;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use OpenAPI\Client\ApiException;
use OpenAPI\Client\Configuration;
use OpenAPI\Client\HeaderSelector;
use OpenAPI\Client\ObjectSerializer;

/**
 * PinsApi Class Doc Comment
 *
 * @category Class
 * @package  OpenAPI\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class PinsApi
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var HeaderSelector
     */
    protected $headerSelector;

    /**
     * @var int Host index
     */
    protected $hostIndex;

    /**
     * @param ClientInterface $client
     * @param Configuration   $config
     * @param HeaderSelector  $selector
     * @param int             $hostIndex (Optional) host index to select the list of hosts if defined in the OpenAPI spec
     */
    public function __construct(
        ClientInterface $client = null,
        Configuration $config = null,
        HeaderSelector $selector = null,
        $hostIndex = 0
    ) {
        $this->client = $client ?: new Client();
        $this->config = $config ?: new Configuration();
        $this->headerSelector = $selector ?: new HeaderSelector();
        $this->hostIndex = $hostIndex;
    }

    /**
     * Set the host index
     *
     * @param int $hostIndex Host index (required)
     */
    public function setHostIndex($hostIndex): void
    {
        $this->hostIndex = $hostIndex;
    }

    /**
     * Get the host index
     *
     * @return int Host index
     */
    public function getHostIndex()
    {
        return $this->hostIndex;
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Operation pinsGet
     *
     * List pin objects
     *
     * @param  string[] $cid Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional)
     * @param  string $name Return pin objects with specified name (by default a case-sensitive, exact match) (optional)
     * @param  \OpenAPI\Client\Model\TextMatchingStrategy $match Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional)
     * @param  \OpenAPI\Client\Model\Status[] $status Return pin objects for pins with the specified status (optional)
     * @param  \DateTime $before Return results created (queued) before provided timestamp (optional)
     * @param  \DateTime $after Return results created (queued) after provided timestamp (optional)
     * @param  int $limit Max records to return (optional, default to 10)
     * @param  array<string,string> $meta Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)
     *
     * @throws \OpenAPI\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \OpenAPI\Client\Model\PinResults|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure
     */
    public function pinsGet($cid = null, $name = null, $match = null, $status = null, $before = null, $after = null, $limit = 10, $meta = null)
    {
        list($response) = $this->pinsGetWithHttpInfo($cid, $name, $match, $status, $before, $after, $limit, $meta);
        return $response;
    }

    /**
     * Operation pinsGetWithHttpInfo
     *
     * List pin objects
     *
     * @param  string[] $cid Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional)
     * @param  string $name Return pin objects with specified name (by default a case-sensitive, exact match) (optional)
     * @param  \OpenAPI\Client\Model\TextMatchingStrategy $match Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional)
     * @param  \OpenAPI\Client\Model\Status[] $status Return pin objects for pins with the specified status (optional)
     * @param  \DateTime $before Return results created (queued) before provided timestamp (optional)
     * @param  \DateTime $after Return results created (queued) after provided timestamp (optional)
     * @param  int $limit Max records to return (optional, default to 10)
     * @param  array<string,string> $meta Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)
     *
     * @throws \OpenAPI\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \OpenAPI\Client\Model\PinResults|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure, HTTP status code, HTTP response headers (array of strings)
     */
    public function pinsGetWithHttpInfo($cid = null, $name = null, $match = null, $status = null, $before = null, $after = null, $limit = 10, $meta = null)
    {
        $request = $this->pinsGetRequest($cid, $name, $match, $status, $before, $after, $limit, $meta);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            switch($statusCode) {
                case 200:
                    if ('\OpenAPI\Client\Model\PinResults' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\PinResults', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 400:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 401:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 404:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 409:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 4XX:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 5XX:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
            }

            $returnType = '\OpenAPI\Client\Model\PinResults';
            if ($returnType === '\SplFileObject') {
                $content = $response->getBody(); //stream goes to serializer
            } else {
                $content = (string) $response->getBody();
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\PinResults',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 409:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 4XX:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 5XX:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation pinsGetAsync
     *
     * List pin objects
     *
     * @param  string[] $cid Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional)
     * @param  string $name Return pin objects with specified name (by default a case-sensitive, exact match) (optional)
     * @param  \OpenAPI\Client\Model\TextMatchingStrategy $match Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional)
     * @param  \OpenAPI\Client\Model\Status[] $status Return pin objects for pins with the specified status (optional)
     * @param  \DateTime $before Return results created (queued) before provided timestamp (optional)
     * @param  \DateTime $after Return results created (queued) after provided timestamp (optional)
     * @param  int $limit Max records to return (optional, default to 10)
     * @param  array<string,string> $meta Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function pinsGetAsync($cid = null, $name = null, $match = null, $status = null, $before = null, $after = null, $limit = 10, $meta = null)
    {
        return $this->pinsGetAsyncWithHttpInfo($cid, $name, $match, $status, $before, $after, $limit, $meta)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation pinsGetAsyncWithHttpInfo
     *
     * List pin objects
     *
     * @param  string[] $cid Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional)
     * @param  string $name Return pin objects with specified name (by default a case-sensitive, exact match) (optional)
     * @param  \OpenAPI\Client\Model\TextMatchingStrategy $match Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional)
     * @param  \OpenAPI\Client\Model\Status[] $status Return pin objects for pins with the specified status (optional)
     * @param  \DateTime $before Return results created (queued) before provided timestamp (optional)
     * @param  \DateTime $after Return results created (queued) after provided timestamp (optional)
     * @param  int $limit Max records to return (optional, default to 10)
     * @param  array<string,string> $meta Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function pinsGetAsyncWithHttpInfo($cid = null, $name = null, $match = null, $status = null, $before = null, $after = null, $limit = 10, $meta = null)
    {
        $returnType = '\OpenAPI\Client\Model\PinResults';
        $request = $this->pinsGetRequest($cid, $name, $match, $status, $before, $after, $limit, $meta);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'pinsGet'
     *
     * @param  string[] $cid Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional)
     * @param  string $name Return pin objects with specified name (by default a case-sensitive, exact match) (optional)
     * @param  \OpenAPI\Client\Model\TextMatchingStrategy $match Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional)
     * @param  \OpenAPI\Client\Model\Status[] $status Return pin objects for pins with the specified status (optional)
     * @param  \DateTime $before Return results created (queued) before provided timestamp (optional)
     * @param  \DateTime $after Return results created (queued) after provided timestamp (optional)
     * @param  int $limit Max records to return (optional, default to 10)
     * @param  array<string,string> $meta Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function pinsGetRequest($cid = null, $name = null, $match = null, $status = null, $before = null, $after = null, $limit = 10, $meta = null)
    {
        if ($cid !== null && count($cid) > 10) {
            throw new \InvalidArgumentException('invalid value for "$cid" when calling PinsApi.pinsGet, number of items must be less than or equal to 10.');
        }
        if ($cid !== null && count($cid) < 1) {
            throw new \InvalidArgumentException('invalid value for "$cid" when calling PinsApi.pinsGet, number of items must be greater than or equal to 1.');
        }

        if ($name !== null && strlen($name) > 255) {
            throw new \InvalidArgumentException('invalid length for "$name" when calling PinsApi.pinsGet, must be smaller than or equal to 255.');
        }

        if ($status !== null && count($status) < 1) {
            throw new \InvalidArgumentException('invalid value for "$status" when calling PinsApi.pinsGet, number of items must be greater than or equal to 1.');
        }

        if ($limit !== null && $limit > 1000) {
            throw new \InvalidArgumentException('invalid value for "$limit" when calling PinsApi.pinsGet, must be smaller than or equal to 1000.');
        }
        if ($limit !== null && $limit < 1) {
            throw new \InvalidArgumentException('invalid value for "$limit" when calling PinsApi.pinsGet, must be bigger than or equal to 1.');
        }


        $resourcePath = '/pins';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        if (is_array($cid)) {
            $cid = ObjectSerializer::serializeCollection($cid, 'form', true);
        }
        if ($cid !== null) {
            $queryParams['cid'] = $cid;
        }
        // query params
        if ($name !== null) {
            if('form' === 'form' && is_array($name)) {
                foreach($name as $key => $value) {
                    $queryParams[$key] = $value;
                }
            }
            else {
                $queryParams['name'] = $name;
            }
        }
        // query params
        if ($match !== null) {
            if('form' === 'form' && is_array($match)) {
                foreach($match as $key => $value) {
                    $queryParams[$key] = $value;
                }
            }
            else {
                $queryParams['match'] = $match;
            }
        }
        // query params
        if (is_array($status)) {
            $status = ObjectSerializer::serializeCollection($status, 'form', true);
        }
        if ($status !== null) {
            $queryParams['status'] = $status;
        }
        // query params
        if ($before !== null) {
            if('form' === 'form' && is_array($before)) {
                foreach($before as $key => $value) {
                    $queryParams[$key] = $value;
                }
            }
            else {
                $queryParams['before'] = $before;
            }
        }
        // query params
        if ($after !== null) {
            if('form' === 'form' && is_array($after)) {
                foreach($after as $key => $value) {
                    $queryParams[$key] = $value;
                }
            }
            else {
                $queryParams['after'] = $after;
            }
        }
        // query params
        if ($limit !== null) {
            if('form' === 'form' && is_array($limit)) {
                foreach($limit as $key => $value) {
                    $queryParams[$key] = $value;
                }
            }
            else {
                $queryParams['limit'] = $limit;
            }
        }
        // query params
        if ($meta !== null) {
            if('form' === 'form' && is_array($meta)) {
                foreach($meta as $key => $value) {
                    $queryParams[$key] = $value;
                }
            }
            else {
                $queryParams['meta'] = $meta;
            }
        }




        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                []
            );
        }

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }

        // this endpoint requires Bearer authentication (access token)
        if ($this->config->getAccessToken() !== null) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);
        return new Request(
            'GET',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation pinsPost
     *
     * Add pin object
     *
     * @param  \OpenAPI\Client\Model\Pin $pin pin (required)
     *
     * @throws \OpenAPI\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \OpenAPI\Client\Model\PinStatus|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure
     */
    public function pinsPost($pin)
    {
        list($response) = $this->pinsPostWithHttpInfo($pin);
        return $response;
    }

    /**
     * Operation pinsPostWithHttpInfo
     *
     * Add pin object
     *
     * @param  \OpenAPI\Client\Model\Pin $pin (required)
     *
     * @throws \OpenAPI\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \OpenAPI\Client\Model\PinStatus|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure, HTTP status code, HTTP response headers (array of strings)
     */
    public function pinsPostWithHttpInfo($pin)
    {
        $request = $this->pinsPostRequest($pin);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            switch($statusCode) {
                case 202:
                    if ('\OpenAPI\Client\Model\PinStatus' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\PinStatus', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 400:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 401:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 404:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 409:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 4XX:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 5XX:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
            }

            $returnType = '\OpenAPI\Client\Model\PinStatus';
            if ($returnType === '\SplFileObject') {
                $content = $response->getBody(); //stream goes to serializer
            } else {
                $content = (string) $response->getBody();
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\PinStatus',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 409:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 4XX:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 5XX:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation pinsPostAsync
     *
     * Add pin object
     *
     * @param  \OpenAPI\Client\Model\Pin $pin (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function pinsPostAsync($pin)
    {
        return $this->pinsPostAsyncWithHttpInfo($pin)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation pinsPostAsyncWithHttpInfo
     *
     * Add pin object
     *
     * @param  \OpenAPI\Client\Model\Pin $pin (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function pinsPostAsyncWithHttpInfo($pin)
    {
        $returnType = '\OpenAPI\Client\Model\PinStatus';
        $request = $this->pinsPostRequest($pin);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'pinsPost'
     *
     * @param  \OpenAPI\Client\Model\Pin $pin (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function pinsPostRequest($pin)
    {
        // verify the required parameter 'pin' is set
        if ($pin === null || (is_array($pin) && count($pin) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $pin when calling pinsPost'
            );
        }

        $resourcePath = '/pins';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;





        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                ['application/json']
            );
        }

        // for model (json/xml)
        if (isset($pin)) {
            if ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode(ObjectSerializer::sanitizeForSerialization($pin));
            } else {
                $httpBody = $pin;
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }

        // this endpoint requires Bearer authentication (access token)
        if ($this->config->getAccessToken() !== null) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);
        return new Request(
            'POST',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation pinsRequestidDelete
     *
     * Remove pin object
     *
     * @param  string $requestid requestid (required)
     *
     * @throws \OpenAPI\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return void
     */
    public function pinsRequestidDelete($requestid)
    {
        $this->pinsRequestidDeleteWithHttpInfo($requestid);
    }

    /**
     * Operation pinsRequestidDeleteWithHttpInfo
     *
     * Remove pin object
     *
     * @param  string $requestid (required)
     *
     * @throws \OpenAPI\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function pinsRequestidDeleteWithHttpInfo($requestid)
    {
        $request = $this->pinsRequestidDeleteRequest($requestid);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return [null, $statusCode, $response->getHeaders()];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 409:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 4XX:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 5XX:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation pinsRequestidDeleteAsync
     *
     * Remove pin object
     *
     * @param  string $requestid (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function pinsRequestidDeleteAsync($requestid)
    {
        return $this->pinsRequestidDeleteAsyncWithHttpInfo($requestid)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation pinsRequestidDeleteAsyncWithHttpInfo
     *
     * Remove pin object
     *
     * @param  string $requestid (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function pinsRequestidDeleteAsyncWithHttpInfo($requestid)
    {
        $returnType = '';
        $request = $this->pinsRequestidDeleteRequest($requestid);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    return [null, $response->getStatusCode(), $response->getHeaders()];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'pinsRequestidDelete'
     *
     * @param  string $requestid (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function pinsRequestidDeleteRequest($requestid)
    {
        // verify the required parameter 'requestid' is set
        if ($requestid === null || (is_array($requestid) && count($requestid) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $requestid when calling pinsRequestidDelete'
            );
        }

        $resourcePath = '/pins/{requestid}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($requestid !== null) {
            $resourcePath = str_replace(
                '{' . 'requestid' . '}',
                ObjectSerializer::toPathValue($requestid),
                $resourcePath
            );
        }


        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                []
            );
        }

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }

        // this endpoint requires Bearer authentication (access token)
        if ($this->config->getAccessToken() !== null) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);
        return new Request(
            'DELETE',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation pinsRequestidGet
     *
     * Get pin object
     *
     * @param  string $requestid requestid (required)
     *
     * @throws \OpenAPI\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \OpenAPI\Client\Model\PinStatus|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure
     */
    public function pinsRequestidGet($requestid)
    {
        list($response) = $this->pinsRequestidGetWithHttpInfo($requestid);
        return $response;
    }

    /**
     * Operation pinsRequestidGetWithHttpInfo
     *
     * Get pin object
     *
     * @param  string $requestid (required)
     *
     * @throws \OpenAPI\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \OpenAPI\Client\Model\PinStatus|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure, HTTP status code, HTTP response headers (array of strings)
     */
    public function pinsRequestidGetWithHttpInfo($requestid)
    {
        $request = $this->pinsRequestidGetRequest($requestid);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            switch($statusCode) {
                case 200:
                    if ('\OpenAPI\Client\Model\PinStatus' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\PinStatus', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 400:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 401:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 404:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 409:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 4XX:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 5XX:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
            }

            $returnType = '\OpenAPI\Client\Model\PinStatus';
            if ($returnType === '\SplFileObject') {
                $content = $response->getBody(); //stream goes to serializer
            } else {
                $content = (string) $response->getBody();
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\PinStatus',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 409:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 4XX:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 5XX:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation pinsRequestidGetAsync
     *
     * Get pin object
     *
     * @param  string $requestid (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function pinsRequestidGetAsync($requestid)
    {
        return $this->pinsRequestidGetAsyncWithHttpInfo($requestid)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation pinsRequestidGetAsyncWithHttpInfo
     *
     * Get pin object
     *
     * @param  string $requestid (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function pinsRequestidGetAsyncWithHttpInfo($requestid)
    {
        $returnType = '\OpenAPI\Client\Model\PinStatus';
        $request = $this->pinsRequestidGetRequest($requestid);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'pinsRequestidGet'
     *
     * @param  string $requestid (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function pinsRequestidGetRequest($requestid)
    {
        // verify the required parameter 'requestid' is set
        if ($requestid === null || (is_array($requestid) && count($requestid) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $requestid when calling pinsRequestidGet'
            );
        }

        $resourcePath = '/pins/{requestid}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($requestid !== null) {
            $resourcePath = str_replace(
                '{' . 'requestid' . '}',
                ObjectSerializer::toPathValue($requestid),
                $resourcePath
            );
        }


        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                []
            );
        }

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }

        // this endpoint requires Bearer authentication (access token)
        if ($this->config->getAccessToken() !== null) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);
        return new Request(
            'GET',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation pinsRequestidPost
     *
     * Replace pin object
     *
     * @param  string $requestid requestid (required)
     * @param  \OpenAPI\Client\Model\Pin $pin pin (required)
     *
     * @throws \OpenAPI\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \OpenAPI\Client\Model\PinStatus|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure
     */
    public function pinsRequestidPost($requestid, $pin)
    {
        list($response) = $this->pinsRequestidPostWithHttpInfo($requestid, $pin);
        return $response;
    }

    /**
     * Operation pinsRequestidPostWithHttpInfo
     *
     * Replace pin object
     *
     * @param  string $requestid (required)
     * @param  \OpenAPI\Client\Model\Pin $pin (required)
     *
     * @throws \OpenAPI\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \OpenAPI\Client\Model\PinStatus|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure|\OpenAPI\Client\Model\Failure, HTTP status code, HTTP response headers (array of strings)
     */
    public function pinsRequestidPostWithHttpInfo($requestid, $pin)
    {
        $request = $this->pinsRequestidPostRequest($requestid, $pin);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            switch($statusCode) {
                case 202:
                    if ('\OpenAPI\Client\Model\PinStatus' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\PinStatus', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 400:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 401:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 404:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 409:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 4XX:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 5XX:
                    if ('\OpenAPI\Client\Model\Failure' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\OpenAPI\Client\Model\Failure', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
            }

            $returnType = '\OpenAPI\Client\Model\PinStatus';
            if ($returnType === '\SplFileObject') {
                $content = $response->getBody(); //stream goes to serializer
            } else {
                $content = (string) $response->getBody();
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\PinStatus',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 409:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 4XX:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 5XX:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\OpenAPI\Client\Model\Failure',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation pinsRequestidPostAsync
     *
     * Replace pin object
     *
     * @param  string $requestid (required)
     * @param  \OpenAPI\Client\Model\Pin $pin (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function pinsRequestidPostAsync($requestid, $pin)
    {
        return $this->pinsRequestidPostAsyncWithHttpInfo($requestid, $pin)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation pinsRequestidPostAsyncWithHttpInfo
     *
     * Replace pin object
     *
     * @param  string $requestid (required)
     * @param  \OpenAPI\Client\Model\Pin $pin (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function pinsRequestidPostAsyncWithHttpInfo($requestid, $pin)
    {
        $returnType = '\OpenAPI\Client\Model\PinStatus';
        $request = $this->pinsRequestidPostRequest($requestid, $pin);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'pinsRequestidPost'
     *
     * @param  string $requestid (required)
     * @param  \OpenAPI\Client\Model\Pin $pin (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function pinsRequestidPostRequest($requestid, $pin)
    {
        // verify the required parameter 'requestid' is set
        if ($requestid === null || (is_array($requestid) && count($requestid) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $requestid when calling pinsRequestidPost'
            );
        }
        // verify the required parameter 'pin' is set
        if ($pin === null || (is_array($pin) && count($pin) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $pin when calling pinsRequestidPost'
            );
        }

        $resourcePath = '/pins/{requestid}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($requestid !== null) {
            $resourcePath = str_replace(
                '{' . 'requestid' . '}',
                ObjectSerializer::toPathValue($requestid),
                $resourcePath
            );
        }


        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                ['application/json']
            );
        }

        // for model (json/xml)
        if (isset($pin)) {
            if ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode(ObjectSerializer::sanitizeForSerialization($pin));
            } else {
                $httpBody = $pin;
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }

        // this endpoint requires Bearer authentication (access token)
        if ($this->config->getAccessToken() !== null) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);
        return new Request(
            'POST',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Create http client option
     *
     * @throws \RuntimeException on file opening failure
     * @return array of http client options
     */
    protected function createHttpClientOption()
    {
        $options = [];
        if ($this->config->getDebug()) {
            $options[RequestOptions::DEBUG] = fopen($this->config->getDebugFile(), 'a');
            if (!$options[RequestOptions::DEBUG]) {
                throw new \RuntimeException('Failed to open the debug file: ' . $this->config->getDebugFile());
            }
        }

        return $options;
    }
}
