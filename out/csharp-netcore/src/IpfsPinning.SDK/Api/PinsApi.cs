/*
 * IPFS Pinning Service API
 *
 *   ## About this spec The IPFS Pinning Service API is intended to be an implementation-agnostic API&#x3a; - For use and implementation by pinning service providers - For use in client mode by IPFS nodes and GUI-based applications  ### Document scope and intended audience The intended audience of this document is **IPFS developers** building pinning service clients or servers compatible with this OpenAPI spec. Your input and feedback are welcome and valuable as we develop this API spec. Please join the design discussion at [github.com/ipfs/pinning-services-api-spec](https://github.com/ipfs/pinning-services-api-spec).  **IPFS users** should see the tutorial at [docs.ipfs.io/how-to/work-with-pinning-services](https://docs.ipfs.io/how-to/work-with-pinning-services/) instead.  ### Related resources The latest version of this spec and additional resources can be found at: - Specification: https://github.com/ipfs/pinning-services-api-spec/raw/main/ipfs-pinning-service.yaml - Docs: https://ipfs.github.io/pinning-services-api-spec/ - Clients and services: https://github.com/ipfs/pinning-services-api-spec#adoption  # Schemas This section describes the most important object types and conventions.  A full list of fields and schemas can be found in the `schemas` section of the [YAML file](https://github.com/ipfs/pinning-services-api-spec/blob/master/ipfs-pinning-service.yaml).  ## Identifiers ### cid [Content Identifier (CID)](https://docs.ipfs.io/concepts/content-addressing/) points at the root of a DAG that is pinned recursively. ### requestid Unique identifier of a pin request.  When a pin is created, the service responds with unique `requestid` that can be later used for pin removal. When the same `cid` is pinned again, a different `requestid` is returned to differentiate between those pin requests.  Service implementation should use UUID, `hash(accessToken,Pin,PinStatus.created)`, or any other opaque identifier that provides equally strong protection against race conditions.  ## Objects ### Pin object  ![pin object](https://bafybeideck2fchyxna4wqwc2mo67yriokehw3yujboc5redjdaajrk2fjq.ipfs.dweb.link/pin.png)  The `Pin` object is a representation of a pin request.  It includes the `cid` of data to be pinned, as well as optional metadata in `name`, `origins`, and `meta`.  ### Pin status response  ![pin status response object](https://bafybeideck2fchyxna4wqwc2mo67yriokehw3yujboc5redjdaajrk2fjq.ipfs.dweb.link/pinstatus.png)  The `PinStatus` object is a representation of the current state of a pinning operation. It includes the original `pin` object, along with the current `status` and globally unique `requestid` of the entire pinning request, which can be used for future status checks and management. Addresses in the `delegates` array are peers delegated by the pinning service for facilitating direct file transfers (more details in the provider hints section). Any additional vendor-specific information is returned in optional `info`.  # The pin lifecycle  ![pinning service objects and lifecycle](https://bafybeideck2fchyxna4wqwc2mo67yriokehw3yujboc5redjdaajrk2fjq.ipfs.dweb.link/lifecycle.png)  ## Creating a new pin object The user sends a `Pin` object to `POST /pins` and receives a `PinStatus` response: - `requestid` in `PinStatus` is the identifier of the pin operation, which can can be used for checking status, and removing the pin in the future - `status` in `PinStatus` indicates the current state of a pin  ## Checking status of in-progress pinning `status` (in `PinStatus`) may indicate a pending state (`queued` or `pinning`). This means the data behind `Pin.cid` was not found on the pinning service and is being fetched from the IPFS network at large, which may take time.  In this case, the user can periodically check pinning progress via `GET /pins/{requestid}` until pinning is successful, or the user decides to remove the pending pin.  ## Replacing an existing pin object The user can replace an existing pin object via `POST /pins/{requestid}`. This is a shortcut for removing a pin object identified by `requestid` and creating a new one in a single API call that protects against undesired garbage collection of blocks common to both pins. Useful when updating a pin representing a huge dataset where most of blocks did not change. The new pin object `requestid` is returned in the `PinStatus` response. The old pin object is deleted automatically.  ## Removing a pin object A pin object can be removed via `DELETE /pins/{requestid}`.   # Provider hints A pinning service will use the DHT and other discovery methods to locate pinned content; however, it is a good practice to provide additional provider hints to speed up the discovery phase and start the transfer immediately, especially if a client has the data in their own datastore or already knows of other providers.  The most common scenario is a client putting its own IPFS node's multiaddrs in `Pin.origins`,  and then attempt to connect to every multiaddr returned by a pinning service in `PinStatus.delegates` to initiate transfer.  At the same time, a pinning service will try to connect to multiaddrs provided by the client in `Pin.origins`.  This ensures data transfer starts immediately (without waiting for provider discovery over DHT), and mutual direct dial between a client and a service works around peer routing issues in restrictive network topologies, such as NATs, firewalls, etc.  **NOTE:** Connections to multiaddrs in `origins` and `delegates` arrays should be attempted in best-effort fashion, and dial failure should not fail the pinning operation. When unable to act on explicit provider hints, DHT and other discovery methods should be used as a fallback by a pinning service.  **NOTE:** All multiaddrs MUST end with `/p2p/{peerID}` and SHOULD be fully resolved and confirmed to be dialable from the public internet. Avoid sending addresses from local networks.  # Custom metadata Pinning services are encouraged to add support for additional features by leveraging the optional `Pin.meta` and `PinStatus.info` fields. While these attributes can be application- or vendor-specific, we encourage the community at large to leverage these attributes as a sandbox to come up with conventions that could become part of future revisions of this API. ## Pin metadata String keys and values passed in `Pin.meta` are persisted with the pin object. This is an opt-in feature: It is OK for a client to omit or ignore these optional attributes, and doing so should not impact the basic pinning functionality.  Potential uses: - `Pin.meta[app_id]`: Attaching a unique identifier to pins created by an app enables meta-filtering pins per app - `Pin.meta[vendor_policy]`: Vendor-specific policy (for example: which region to use, how many copies to keep)  ### Filtering based on metadata The contents of `Pin.meta` can be used as an advanced search filter for situations where searching by `name` and `cid` is not enough.  Metadata key matching rule is `AND`: - lookup returns pins that have `meta` with all key-value pairs matching the passed values - pin metadata may have more keys, but only ones passed in the query are used for filtering  The wire format for the `meta` when used as a query parameter is a [URL-escaped](https://en.wikipedia.org/wiki/Percent-encoding) stringified JSON object. A lookup example for pins that have a `meta` key-value pair `{\"app_id\":\"UUID\"}` is: - `GET /pins?meta=%7B%22app_id%22%3A%22UUID%22%7D`   ## Pin status info Additional `PinStatus.info` can be returned by pinning service.  Potential uses: - `PinStatus.info[status_details]`: more info about the current status (queue position, percentage of transferred data, summary of where data is stored, etc); when `PinStatus.status=failed`, it could provide a reason why a pin operation failed (e.g. lack of funds, DAG too big, etc.) - `PinStatus.info[dag_size]`: the size of pinned data, along with DAG overhead - `PinStatus.info[raw_size]`: the size of data without DAG overhead (eg. unixfs) - `PinStatus.info[pinned_until]`: if vendor supports time-bound pins, this could indicate when the pin will expire  # Pagination and filtering Pin objects can be listed by executing `GET /pins` with optional parameters:  - When no filters are provided, the endpoint will return a small batch of the 10 most recently created items, from the latest to the oldest. - The number of returned items can be adjusted with the `limit` parameter (implicit default is 10). - If the value in `PinResults.count` is bigger than the length of `PinResults.results`, the client can infer there are more results that can be queried. - To read more items, pass the `before` filter with the timestamp from `PinStatus.created` found in the oldest item in the current batch of results. Repeat to read all results. - Returned results can be fine-tuned by applying optional `after`, `cid`, `name`, `status`, or `meta` filters.  > **Note**: pagination by the `created` timestamp requires each value to be globally unique. Any future considerations to add support for bulk creation must account for this.  
 *
 * The version of the OpenAPI document: 1.0.0
 * Generated by: https://github.com/openapitools/openapi-generator.git
 */


using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Net;
using System.Net.Mime;
using IpfsPinning.SDK.Client;
using IpfsPinning.SDK.Model;

namespace IpfsPinning.SDK.Api
{

    /// <summary>
    /// Represents a collection of functions to interact with the API endpoints
    /// </summary>
    public interface IPinsApiSync : IApiAccessor
    {
        #region Synchronous Operations
        /// <summary>
        /// List pin objects
        /// </summary>
        /// <remarks>
        /// List all the pin objects, matching optional filters; when no filter is provided, only successful pins are returned
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="cid">Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional)</param>
        /// <param name="name">Return pin objects with specified name (by default a case-sensitive, exact match) (optional)</param>
        /// <param name="match">Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional)</param>
        /// <param name="status">Return pin objects for pins with the specified status (optional)</param>
        /// <param name="before">Return results created (queued) before provided timestamp (optional)</param>
        /// <param name="after">Return results created (queued) after provided timestamp (optional)</param>
        /// <param name="limit">Max records to return (optional, default to 10)</param>
        /// <param name="meta">Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)</param>
        /// <returns>PinResults</returns>
        PinResults PinsGet(List<string> cid = default(List<string>), string name = default(string), TextMatchingStrategy? match = default(TextMatchingStrategy?), List<Status> status = default(List<Status>), DateTime? before = default(DateTime?), DateTime? after = default(DateTime?), int? limit = default(int?), Dictionary<string, string> meta = default(Dictionary<string, string>));

        /// <summary>
        /// List pin objects
        /// </summary>
        /// <remarks>
        /// List all the pin objects, matching optional filters; when no filter is provided, only successful pins are returned
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="cid">Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional)</param>
        /// <param name="name">Return pin objects with specified name (by default a case-sensitive, exact match) (optional)</param>
        /// <param name="match">Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional)</param>
        /// <param name="status">Return pin objects for pins with the specified status (optional)</param>
        /// <param name="before">Return results created (queued) before provided timestamp (optional)</param>
        /// <param name="after">Return results created (queued) after provided timestamp (optional)</param>
        /// <param name="limit">Max records to return (optional, default to 10)</param>
        /// <param name="meta">Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)</param>
        /// <returns>ApiResponse of PinResults</returns>
        ApiResponse<PinResults> PinsGetWithHttpInfo(List<string> cid = default(List<string>), string name = default(string), TextMatchingStrategy? match = default(TextMatchingStrategy?), List<Status> status = default(List<Status>), DateTime? before = default(DateTime?), DateTime? after = default(DateTime?), int? limit = default(int?), Dictionary<string, string> meta = default(Dictionary<string, string>));
        /// <summary>
        /// Add pin object
        /// </summary>
        /// <remarks>
        /// Add a new pin object for the current access token
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="pin"></param>
        /// <returns>PinStatus</returns>
        PinStatus PinsPost(Pin pin);

        /// <summary>
        /// Add pin object
        /// </summary>
        /// <remarks>
        /// Add a new pin object for the current access token
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="pin"></param>
        /// <returns>ApiResponse of PinStatus</returns>
        ApiResponse<PinStatus> PinsPostWithHttpInfo(Pin pin);
        /// <summary>
        /// Remove pin object
        /// </summary>
        /// <remarks>
        /// Remove a pin object
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <returns></returns>
        void PinsRequestidDelete(string requestid);

        /// <summary>
        /// Remove pin object
        /// </summary>
        /// <remarks>
        /// Remove a pin object
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <returns>ApiResponse of Object(void)</returns>
        ApiResponse<Object> PinsRequestidDeleteWithHttpInfo(string requestid);
        /// <summary>
        /// Get pin object
        /// </summary>
        /// <remarks>
        /// Get a pin object and its status
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <returns>PinStatus</returns>
        PinStatus PinsRequestidGet(string requestid);

        /// <summary>
        /// Get pin object
        /// </summary>
        /// <remarks>
        /// Get a pin object and its status
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <returns>ApiResponse of PinStatus</returns>
        ApiResponse<PinStatus> PinsRequestidGetWithHttpInfo(string requestid);
        /// <summary>
        /// Replace pin object
        /// </summary>
        /// <remarks>
        /// Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="pin"></param>
        /// <returns>PinStatus</returns>
        PinStatus PinsRequestidPost(string requestid, Pin pin);

        /// <summary>
        /// Replace pin object
        /// </summary>
        /// <remarks>
        /// Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="pin"></param>
        /// <returns>ApiResponse of PinStatus</returns>
        ApiResponse<PinStatus> PinsRequestidPostWithHttpInfo(string requestid, Pin pin);
        #endregion Synchronous Operations
    }

    /// <summary>
    /// Represents a collection of functions to interact with the API endpoints
    /// </summary>
    public interface IPinsApiAsync : IApiAccessor
    {
        #region Asynchronous Operations
        /// <summary>
        /// List pin objects
        /// </summary>
        /// <remarks>
        /// List all the pin objects, matching optional filters; when no filter is provided, only successful pins are returned
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="cid">Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional)</param>
        /// <param name="name">Return pin objects with specified name (by default a case-sensitive, exact match) (optional)</param>
        /// <param name="match">Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional)</param>
        /// <param name="status">Return pin objects for pins with the specified status (optional)</param>
        /// <param name="before">Return results created (queued) before provided timestamp (optional)</param>
        /// <param name="after">Return results created (queued) after provided timestamp (optional)</param>
        /// <param name="limit">Max records to return (optional, default to 10)</param>
        /// <param name="meta">Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)</param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of PinResults</returns>
        System.Threading.Tasks.Task<PinResults> PinsGetAsync(List<string> cid = default(List<string>), string name = default(string), TextMatchingStrategy? match = default(TextMatchingStrategy?), List<Status> status = default(List<Status>), DateTime? before = default(DateTime?), DateTime? after = default(DateTime?), int? limit = default(int?), Dictionary<string, string> meta = default(Dictionary<string, string>), System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken));

        /// <summary>
        /// List pin objects
        /// </summary>
        /// <remarks>
        /// List all the pin objects, matching optional filters; when no filter is provided, only successful pins are returned
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="cid">Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional)</param>
        /// <param name="name">Return pin objects with specified name (by default a case-sensitive, exact match) (optional)</param>
        /// <param name="match">Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional)</param>
        /// <param name="status">Return pin objects for pins with the specified status (optional)</param>
        /// <param name="before">Return results created (queued) before provided timestamp (optional)</param>
        /// <param name="after">Return results created (queued) after provided timestamp (optional)</param>
        /// <param name="limit">Max records to return (optional, default to 10)</param>
        /// <param name="meta">Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)</param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of ApiResponse (PinResults)</returns>
        System.Threading.Tasks.Task<ApiResponse<PinResults>> PinsGetWithHttpInfoAsync(List<string> cid = default(List<string>), string name = default(string), TextMatchingStrategy? match = default(TextMatchingStrategy?), List<Status> status = default(List<Status>), DateTime? before = default(DateTime?), DateTime? after = default(DateTime?), int? limit = default(int?), Dictionary<string, string> meta = default(Dictionary<string, string>), System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken));
        /// <summary>
        /// Add pin object
        /// </summary>
        /// <remarks>
        /// Add a new pin object for the current access token
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="pin"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of PinStatus</returns>
        System.Threading.Tasks.Task<PinStatus> PinsPostAsync(Pin pin, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken));

        /// <summary>
        /// Add pin object
        /// </summary>
        /// <remarks>
        /// Add a new pin object for the current access token
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="pin"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of ApiResponse (PinStatus)</returns>
        System.Threading.Tasks.Task<ApiResponse<PinStatus>> PinsPostWithHttpInfoAsync(Pin pin, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken));
        /// <summary>
        /// Remove pin object
        /// </summary>
        /// <remarks>
        /// Remove a pin object
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of void</returns>
        System.Threading.Tasks.Task PinsRequestidDeleteAsync(string requestid, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken));

        /// <summary>
        /// Remove pin object
        /// </summary>
        /// <remarks>
        /// Remove a pin object
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of ApiResponse</returns>
        System.Threading.Tasks.Task<ApiResponse<Object>> PinsRequestidDeleteWithHttpInfoAsync(string requestid, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken));
        /// <summary>
        /// Get pin object
        /// </summary>
        /// <remarks>
        /// Get a pin object and its status
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of PinStatus</returns>
        System.Threading.Tasks.Task<PinStatus> PinsRequestidGetAsync(string requestid, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken));

        /// <summary>
        /// Get pin object
        /// </summary>
        /// <remarks>
        /// Get a pin object and its status
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of ApiResponse (PinStatus)</returns>
        System.Threading.Tasks.Task<ApiResponse<PinStatus>> PinsRequestidGetWithHttpInfoAsync(string requestid, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken));
        /// <summary>
        /// Replace pin object
        /// </summary>
        /// <remarks>
        /// Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="pin"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of PinStatus</returns>
        System.Threading.Tasks.Task<PinStatus> PinsRequestidPostAsync(string requestid, Pin pin, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken));

        /// <summary>
        /// Replace pin object
        /// </summary>
        /// <remarks>
        /// Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)
        /// </remarks>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="pin"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of ApiResponse (PinStatus)</returns>
        System.Threading.Tasks.Task<ApiResponse<PinStatus>> PinsRequestidPostWithHttpInfoAsync(string requestid, Pin pin, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken));
        #endregion Asynchronous Operations
    }

    /// <summary>
    /// Represents a collection of functions to interact with the API endpoints
    /// </summary>
    public interface IPinsApi : IPinsApiSync, IPinsApiAsync
    {

    }

    /// <summary>
    /// Represents a collection of functions to interact with the API endpoints
    /// </summary>
    public partial class PinsApi : IPinsApi
    {
        private IpfsPinning.SDK.Client.ExceptionFactory _exceptionFactory = (name, response) => null;

        /// <summary>
        /// Initializes a new instance of the <see cref="PinsApi"/> class.
        /// </summary>
        /// <returns></returns>
        public PinsApi() : this((string)null)
        {
        }

        /// <summary>
        /// Initializes a new instance of the <see cref="PinsApi"/> class.
        /// </summary>
        /// <returns></returns>
        public PinsApi(string basePath)
        {
            this.Configuration = IpfsPinning.SDK.Client.Configuration.MergeConfigurations(
                IpfsPinning.SDK.Client.GlobalConfiguration.Instance,
                new IpfsPinning.SDK.Client.Configuration { BasePath = basePath }
            );
            this.Client = new IpfsPinning.SDK.Client.ApiClient(this.Configuration.BasePath);
            this.AsynchronousClient = new IpfsPinning.SDK.Client.ApiClient(this.Configuration.BasePath);
            this.ExceptionFactory = IpfsPinning.SDK.Client.Configuration.DefaultExceptionFactory;
        }

        /// <summary>
        /// Initializes a new instance of the <see cref="PinsApi"/> class
        /// using Configuration object
        /// </summary>
        /// <param name="configuration">An instance of Configuration</param>
        /// <returns></returns>
        public PinsApi(IpfsPinning.SDK.Client.Configuration configuration)
        {
            if (configuration == null) throw new ArgumentNullException("configuration");

            this.Configuration = IpfsPinning.SDK.Client.Configuration.MergeConfigurations(
                IpfsPinning.SDK.Client.GlobalConfiguration.Instance,
                configuration
            );
            this.Client = new IpfsPinning.SDK.Client.ApiClient(this.Configuration.BasePath);
            this.AsynchronousClient = new IpfsPinning.SDK.Client.ApiClient(this.Configuration.BasePath);
            ExceptionFactory = IpfsPinning.SDK.Client.Configuration.DefaultExceptionFactory;
        }

        /// <summary>
        /// Initializes a new instance of the <see cref="PinsApi"/> class
        /// using a Configuration object and client instance.
        /// </summary>
        /// <param name="client">The client interface for synchronous API access.</param>
        /// <param name="asyncClient">The client interface for asynchronous API access.</param>
        /// <param name="configuration">The configuration object.</param>
        public PinsApi(IpfsPinning.SDK.Client.ISynchronousClient client, IpfsPinning.SDK.Client.IAsynchronousClient asyncClient, IpfsPinning.SDK.Client.IReadableConfiguration configuration)
        {
            if (client == null) throw new ArgumentNullException("client");
            if (asyncClient == null) throw new ArgumentNullException("asyncClient");
            if (configuration == null) throw new ArgumentNullException("configuration");

            this.Client = client;
            this.AsynchronousClient = asyncClient;
            this.Configuration = configuration;
            this.ExceptionFactory = IpfsPinning.SDK.Client.Configuration.DefaultExceptionFactory;
        }

        /// <summary>
        /// The client for accessing this underlying API asynchronously.
        /// </summary>
        public IpfsPinning.SDK.Client.IAsynchronousClient AsynchronousClient { get; set; }

        /// <summary>
        /// The client for accessing this underlying API synchronously.
        /// </summary>
        public IpfsPinning.SDK.Client.ISynchronousClient Client { get; set; }

        /// <summary>
        /// Gets the base path of the API client.
        /// </summary>
        /// <value>The base path</value>
        public string GetBasePath()
        {
            return this.Configuration.BasePath;
        }

        /// <summary>
        /// Gets or sets the configuration object
        /// </summary>
        /// <value>An instance of the Configuration</value>
        public IpfsPinning.SDK.Client.IReadableConfiguration Configuration { get; set; }

        /// <summary>
        /// Provides a factory method hook for the creation of exceptions.
        /// </summary>
        public IpfsPinning.SDK.Client.ExceptionFactory ExceptionFactory
        {
            get
            {
                if (_exceptionFactory != null && _exceptionFactory.GetInvocationList().Length > 1)
                {
                    throw new InvalidOperationException("Multicast delegate for ExceptionFactory is unsupported.");
                }
                return _exceptionFactory;
            }
            set { _exceptionFactory = value; }
        }

        /// <summary>
        /// List pin objects List all the pin objects, matching optional filters; when no filter is provided, only successful pins are returned
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="cid">Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional)</param>
        /// <param name="name">Return pin objects with specified name (by default a case-sensitive, exact match) (optional)</param>
        /// <param name="match">Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional)</param>
        /// <param name="status">Return pin objects for pins with the specified status (optional)</param>
        /// <param name="before">Return results created (queued) before provided timestamp (optional)</param>
        /// <param name="after">Return results created (queued) after provided timestamp (optional)</param>
        /// <param name="limit">Max records to return (optional, default to 10)</param>
        /// <param name="meta">Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)</param>
        /// <returns>PinResults</returns>
        public PinResults PinsGet(List<string> cid = default(List<string>), string name = default(string), TextMatchingStrategy? match = default(TextMatchingStrategy?), List<Status> status = default(List<Status>), DateTime? before = default(DateTime?), DateTime? after = default(DateTime?), int? limit = default(int?), Dictionary<string, string> meta = default(Dictionary<string, string>))
        {
            IpfsPinning.SDK.Client.ApiResponse<PinResults> localVarResponse = PinsGetWithHttpInfo(cid, name, match, status, before, after, limit, meta);
            return localVarResponse.Data;
        }

        /// <summary>
        /// List pin objects List all the pin objects, matching optional filters; when no filter is provided, only successful pins are returned
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="cid">Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional)</param>
        /// <param name="name">Return pin objects with specified name (by default a case-sensitive, exact match) (optional)</param>
        /// <param name="match">Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional)</param>
        /// <param name="status">Return pin objects for pins with the specified status (optional)</param>
        /// <param name="before">Return results created (queued) before provided timestamp (optional)</param>
        /// <param name="after">Return results created (queued) after provided timestamp (optional)</param>
        /// <param name="limit">Max records to return (optional, default to 10)</param>
        /// <param name="meta">Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)</param>
        /// <returns>ApiResponse of PinResults</returns>
        public IpfsPinning.SDK.Client.ApiResponse<PinResults> PinsGetWithHttpInfo(List<string> cid = default(List<string>), string name = default(string), TextMatchingStrategy? match = default(TextMatchingStrategy?), List<Status> status = default(List<Status>), DateTime? before = default(DateTime?), DateTime? after = default(DateTime?), int? limit = default(int?), Dictionary<string, string> meta = default(Dictionary<string, string>))
        {
            IpfsPinning.SDK.Client.RequestOptions localVarRequestOptions = new IpfsPinning.SDK.Client.RequestOptions();

            string[] _contentTypes = new string[] {
            };

            // to determine the Accept header
            string[] _accepts = new string[] {
                "application/json"
            };

            var localVarContentType = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderContentType(_contentTypes);
            if (localVarContentType != null) localVarRequestOptions.HeaderParameters.Add("Content-Type", localVarContentType);

            var localVarAccept = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderAccept(_accepts);
            if (localVarAccept != null) localVarRequestOptions.HeaderParameters.Add("Accept", localVarAccept);

            if (cid != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("csv", "cid", cid));
            }
            if (name != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("", "name", name));
            }
            if (match != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("", "match", match));
            }
            if (status != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("csv", "status", status));
            }
            if (before != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("", "before", before));
            }
            if (after != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("", "after", after));
            }
            if (limit != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("", "limit", limit));
            }
            if (meta != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("", "meta", meta));
            }

            // authentication (accessToken) required
            // bearer authentication required
            if (!string.IsNullOrEmpty(this.Configuration.AccessToken))
            {
                localVarRequestOptions.HeaderParameters.Add("Authorization", "Bearer " + this.Configuration.AccessToken);
            }

            // make the HTTP request
            var localVarResponse = this.Client.Get<PinResults>("/pins", localVarRequestOptions, this.Configuration);

            if (this.ExceptionFactory != null)
            {
                Exception _exception = this.ExceptionFactory("PinsGet", localVarResponse);
                if (_exception != null) throw _exception;
            }

            return localVarResponse;
        }

        /// <summary>
        /// List pin objects List all the pin objects, matching optional filters; when no filter is provided, only successful pins are returned
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="cid">Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional)</param>
        /// <param name="name">Return pin objects with specified name (by default a case-sensitive, exact match) (optional)</param>
        /// <param name="match">Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional)</param>
        /// <param name="status">Return pin objects for pins with the specified status (optional)</param>
        /// <param name="before">Return results created (queued) before provided timestamp (optional)</param>
        /// <param name="after">Return results created (queued) after provided timestamp (optional)</param>
        /// <param name="limit">Max records to return (optional, default to 10)</param>
        /// <param name="meta">Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)</param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of PinResults</returns>
        public async System.Threading.Tasks.Task<PinResults> PinsGetAsync(List<string> cid = default(List<string>), string name = default(string), TextMatchingStrategy? match = default(TextMatchingStrategy?), List<Status> status = default(List<Status>), DateTime? before = default(DateTime?), DateTime? after = default(DateTime?), int? limit = default(int?), Dictionary<string, string> meta = default(Dictionary<string, string>), System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken))
        {
            IpfsPinning.SDK.Client.ApiResponse<PinResults> localVarResponse = await PinsGetWithHttpInfoAsync(cid, name, match, status, before, after, limit, meta, cancellationToken).ConfigureAwait(false);
            return localVarResponse.Data;
        }

        /// <summary>
        /// List pin objects List all the pin objects, matching optional filters; when no filter is provided, only successful pins are returned
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="cid">Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts (optional)</param>
        /// <param name="name">Return pin objects with specified name (by default a case-sensitive, exact match) (optional)</param>
        /// <param name="match">Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies (optional)</param>
        /// <param name="status">Return pin objects for pins with the specified status (optional)</param>
        /// <param name="before">Return results created (queued) before provided timestamp (optional)</param>
        /// <param name="after">Return results created (queued) after provided timestamp (optional)</param>
        /// <param name="limit">Max records to return (optional, default to 10)</param>
        /// <param name="meta">Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport (optional)</param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of ApiResponse (PinResults)</returns>
        public async System.Threading.Tasks.Task<IpfsPinning.SDK.Client.ApiResponse<PinResults>> PinsGetWithHttpInfoAsync(List<string> cid = default(List<string>), string name = default(string), TextMatchingStrategy? match = default(TextMatchingStrategy?), List<Status> status = default(List<Status>), DateTime? before = default(DateTime?), DateTime? after = default(DateTime?), int? limit = default(int?), Dictionary<string, string> meta = default(Dictionary<string, string>), System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken))
        {

            IpfsPinning.SDK.Client.RequestOptions localVarRequestOptions = new IpfsPinning.SDK.Client.RequestOptions();

            string[] _contentTypes = new string[] {
            };

            // to determine the Accept header
            string[] _accepts = new string[] {
                "application/json"
            };


            var localVarContentType = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderContentType(_contentTypes);
            if (localVarContentType != null) localVarRequestOptions.HeaderParameters.Add("Content-Type", localVarContentType);

            var localVarAccept = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderAccept(_accepts);
            if (localVarAccept != null) localVarRequestOptions.HeaderParameters.Add("Accept", localVarAccept);

            if (cid != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("csv", "cid", cid));
            }
            if (name != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("", "name", name));
            }
            if (match != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("", "match", match));
            }
            if (status != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("csv", "status", status));
            }
            if (before != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("", "before", before));
            }
            if (after != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("", "after", after));
            }
            if (limit != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("", "limit", limit));
            }
            if (meta != null)
            {
                localVarRequestOptions.QueryParameters.Add(IpfsPinning.SDK.Client.ClientUtils.ParameterToMultiMap("", "meta", meta));
            }

            // authentication (accessToken) required
            // bearer authentication required
            if (!string.IsNullOrEmpty(this.Configuration.AccessToken))
            {
                localVarRequestOptions.HeaderParameters.Add("Authorization", "Bearer " + this.Configuration.AccessToken);
            }

            // make the HTTP request

            var localVarResponse = await this.AsynchronousClient.GetAsync<PinResults>("/pins", localVarRequestOptions, this.Configuration, cancellationToken).ConfigureAwait(false);

            if (this.ExceptionFactory != null)
            {
                Exception _exception = this.ExceptionFactory("PinsGet", localVarResponse);
                if (_exception != null) throw _exception;
            }

            return localVarResponse;
        }

        /// <summary>
        /// Add pin object Add a new pin object for the current access token
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="pin"></param>
        /// <returns>PinStatus</returns>
        public PinStatus PinsPost(Pin pin)
        {
            IpfsPinning.SDK.Client.ApiResponse<PinStatus> localVarResponse = PinsPostWithHttpInfo(pin);
            return localVarResponse.Data;
        }

        /// <summary>
        /// Add pin object Add a new pin object for the current access token
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="pin"></param>
        /// <returns>ApiResponse of PinStatus</returns>
        public IpfsPinning.SDK.Client.ApiResponse<PinStatus> PinsPostWithHttpInfo(Pin pin)
        {
            // verify the required parameter 'pin' is set
            if (pin == null)
                throw new IpfsPinning.SDK.Client.ApiException(400, "Missing required parameter 'pin' when calling PinsApi->PinsPost");

            IpfsPinning.SDK.Client.RequestOptions localVarRequestOptions = new IpfsPinning.SDK.Client.RequestOptions();

            string[] _contentTypes = new string[] {
                "application/json"
            };

            // to determine the Accept header
            string[] _accepts = new string[] {
                "application/json"
            };

            var localVarContentType = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderContentType(_contentTypes);
            if (localVarContentType != null) localVarRequestOptions.HeaderParameters.Add("Content-Type", localVarContentType);

            var localVarAccept = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderAccept(_accepts);
            if (localVarAccept != null) localVarRequestOptions.HeaderParameters.Add("Accept", localVarAccept);

            localVarRequestOptions.Data = pin;

            // authentication (accessToken) required
            // bearer authentication required
            if (!string.IsNullOrEmpty(this.Configuration.AccessToken))
            {
                localVarRequestOptions.HeaderParameters.Add("Authorization", "Bearer " + this.Configuration.AccessToken);
            }

            // make the HTTP request
            var localVarResponse = this.Client.Post<PinStatus>("/pins", localVarRequestOptions, this.Configuration);

            if (this.ExceptionFactory != null)
            {
                Exception _exception = this.ExceptionFactory("PinsPost", localVarResponse);
                if (_exception != null) throw _exception;
            }

            return localVarResponse;
        }

        /// <summary>
        /// Add pin object Add a new pin object for the current access token
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="pin"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of PinStatus</returns>
        public async System.Threading.Tasks.Task<PinStatus> PinsPostAsync(Pin pin, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken))
        {
            IpfsPinning.SDK.Client.ApiResponse<PinStatus> localVarResponse = await PinsPostWithHttpInfoAsync(pin, cancellationToken).ConfigureAwait(false);
            return localVarResponse.Data;
        }

        /// <summary>
        /// Add pin object Add a new pin object for the current access token
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="pin"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of ApiResponse (PinStatus)</returns>
        public async System.Threading.Tasks.Task<IpfsPinning.SDK.Client.ApiResponse<PinStatus>> PinsPostWithHttpInfoAsync(Pin pin, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken))
        {
            // verify the required parameter 'pin' is set
            if (pin == null)
                throw new IpfsPinning.SDK.Client.ApiException(400, "Missing required parameter 'pin' when calling PinsApi->PinsPost");


            IpfsPinning.SDK.Client.RequestOptions localVarRequestOptions = new IpfsPinning.SDK.Client.RequestOptions();

            string[] _contentTypes = new string[] {
                "application/json"
            };

            // to determine the Accept header
            string[] _accepts = new string[] {
                "application/json"
            };


            var localVarContentType = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderContentType(_contentTypes);
            if (localVarContentType != null) localVarRequestOptions.HeaderParameters.Add("Content-Type", localVarContentType);

            var localVarAccept = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderAccept(_accepts);
            if (localVarAccept != null) localVarRequestOptions.HeaderParameters.Add("Accept", localVarAccept);

            localVarRequestOptions.Data = pin;

            // authentication (accessToken) required
            // bearer authentication required
            if (!string.IsNullOrEmpty(this.Configuration.AccessToken))
            {
                localVarRequestOptions.HeaderParameters.Add("Authorization", "Bearer " + this.Configuration.AccessToken);
            }

            // make the HTTP request

            var localVarResponse = await this.AsynchronousClient.PostAsync<PinStatus>("/pins", localVarRequestOptions, this.Configuration, cancellationToken).ConfigureAwait(false);

            if (this.ExceptionFactory != null)
            {
                Exception _exception = this.ExceptionFactory("PinsPost", localVarResponse);
                if (_exception != null) throw _exception;
            }

            return localVarResponse;
        }

        /// <summary>
        /// Remove pin object Remove a pin object
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <returns></returns>
        public void PinsRequestidDelete(string requestid)
        {
            PinsRequestidDeleteWithHttpInfo(requestid);
        }

        /// <summary>
        /// Remove pin object Remove a pin object
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <returns>ApiResponse of Object(void)</returns>
        public IpfsPinning.SDK.Client.ApiResponse<Object> PinsRequestidDeleteWithHttpInfo(string requestid)
        {
            // verify the required parameter 'requestid' is set
            if (requestid == null)
                throw new IpfsPinning.SDK.Client.ApiException(400, "Missing required parameter 'requestid' when calling PinsApi->PinsRequestidDelete");

            IpfsPinning.SDK.Client.RequestOptions localVarRequestOptions = new IpfsPinning.SDK.Client.RequestOptions();

            string[] _contentTypes = new string[] {
            };

            // to determine the Accept header
            string[] _accepts = new string[] {
                "application/json"
            };

            var localVarContentType = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderContentType(_contentTypes);
            if (localVarContentType != null) localVarRequestOptions.HeaderParameters.Add("Content-Type", localVarContentType);

            var localVarAccept = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderAccept(_accepts);
            if (localVarAccept != null) localVarRequestOptions.HeaderParameters.Add("Accept", localVarAccept);

            localVarRequestOptions.PathParameters.Add("requestid", IpfsPinning.SDK.Client.ClientUtils.ParameterToString(requestid)); // path parameter

            // authentication (accessToken) required
            // bearer authentication required
            if (!string.IsNullOrEmpty(this.Configuration.AccessToken))
            {
                localVarRequestOptions.HeaderParameters.Add("Authorization", "Bearer " + this.Configuration.AccessToken);
            }

            // make the HTTP request
            var localVarResponse = this.Client.Delete<Object>("/pins/{requestid}", localVarRequestOptions, this.Configuration);

            if (this.ExceptionFactory != null)
            {
                Exception _exception = this.ExceptionFactory("PinsRequestidDelete", localVarResponse);
                if (_exception != null) throw _exception;
            }

            return localVarResponse;
        }

        /// <summary>
        /// Remove pin object Remove a pin object
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of void</returns>
        public async System.Threading.Tasks.Task PinsRequestidDeleteAsync(string requestid, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken))
        {
            await PinsRequestidDeleteWithHttpInfoAsync(requestid, cancellationToken).ConfigureAwait(false);
        }

        /// <summary>
        /// Remove pin object Remove a pin object
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of ApiResponse</returns>
        public async System.Threading.Tasks.Task<IpfsPinning.SDK.Client.ApiResponse<Object>> PinsRequestidDeleteWithHttpInfoAsync(string requestid, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken))
        {
            // verify the required parameter 'requestid' is set
            if (requestid == null)
                throw new IpfsPinning.SDK.Client.ApiException(400, "Missing required parameter 'requestid' when calling PinsApi->PinsRequestidDelete");


            IpfsPinning.SDK.Client.RequestOptions localVarRequestOptions = new IpfsPinning.SDK.Client.RequestOptions();

            string[] _contentTypes = new string[] {
            };

            // to determine the Accept header
            string[] _accepts = new string[] {
                "application/json"
            };


            var localVarContentType = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderContentType(_contentTypes);
            if (localVarContentType != null) localVarRequestOptions.HeaderParameters.Add("Content-Type", localVarContentType);

            var localVarAccept = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderAccept(_accepts);
            if (localVarAccept != null) localVarRequestOptions.HeaderParameters.Add("Accept", localVarAccept);

            localVarRequestOptions.PathParameters.Add("requestid", IpfsPinning.SDK.Client.ClientUtils.ParameterToString(requestid)); // path parameter

            // authentication (accessToken) required
            // bearer authentication required
            if (!string.IsNullOrEmpty(this.Configuration.AccessToken))
            {
                localVarRequestOptions.HeaderParameters.Add("Authorization", "Bearer " + this.Configuration.AccessToken);
            }

            // make the HTTP request

            var localVarResponse = await this.AsynchronousClient.DeleteAsync<Object>("/pins/{requestid}", localVarRequestOptions, this.Configuration, cancellationToken).ConfigureAwait(false);

            if (this.ExceptionFactory != null)
            {
                Exception _exception = this.ExceptionFactory("PinsRequestidDelete", localVarResponse);
                if (_exception != null) throw _exception;
            }

            return localVarResponse;
        }

        /// <summary>
        /// Get pin object Get a pin object and its status
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <returns>PinStatus</returns>
        public PinStatus PinsRequestidGet(string requestid)
        {
            IpfsPinning.SDK.Client.ApiResponse<PinStatus> localVarResponse = PinsRequestidGetWithHttpInfo(requestid);
            return localVarResponse.Data;
        }

        /// <summary>
        /// Get pin object Get a pin object and its status
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <returns>ApiResponse of PinStatus</returns>
        public IpfsPinning.SDK.Client.ApiResponse<PinStatus> PinsRequestidGetWithHttpInfo(string requestid)
        {
            // verify the required parameter 'requestid' is set
            if (requestid == null)
                throw new IpfsPinning.SDK.Client.ApiException(400, "Missing required parameter 'requestid' when calling PinsApi->PinsRequestidGet");

            IpfsPinning.SDK.Client.RequestOptions localVarRequestOptions = new IpfsPinning.SDK.Client.RequestOptions();

            string[] _contentTypes = new string[] {
            };

            // to determine the Accept header
            string[] _accepts = new string[] {
                "application/json"
            };

            var localVarContentType = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderContentType(_contentTypes);
            if (localVarContentType != null) localVarRequestOptions.HeaderParameters.Add("Content-Type", localVarContentType);

            var localVarAccept = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderAccept(_accepts);
            if (localVarAccept != null) localVarRequestOptions.HeaderParameters.Add("Accept", localVarAccept);

            localVarRequestOptions.PathParameters.Add("requestid", IpfsPinning.SDK.Client.ClientUtils.ParameterToString(requestid)); // path parameter

            // authentication (accessToken) required
            // bearer authentication required
            if (!string.IsNullOrEmpty(this.Configuration.AccessToken))
            {
                localVarRequestOptions.HeaderParameters.Add("Authorization", "Bearer " + this.Configuration.AccessToken);
            }

            // make the HTTP request
            var localVarResponse = this.Client.Get<PinStatus>("/pins/{requestid}", localVarRequestOptions, this.Configuration);

            if (this.ExceptionFactory != null)
            {
                Exception _exception = this.ExceptionFactory("PinsRequestidGet", localVarResponse);
                if (_exception != null) throw _exception;
            }

            return localVarResponse;
        }

        /// <summary>
        /// Get pin object Get a pin object and its status
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of PinStatus</returns>
        public async System.Threading.Tasks.Task<PinStatus> PinsRequestidGetAsync(string requestid, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken))
        {
            IpfsPinning.SDK.Client.ApiResponse<PinStatus> localVarResponse = await PinsRequestidGetWithHttpInfoAsync(requestid, cancellationToken).ConfigureAwait(false);
            return localVarResponse.Data;
        }

        /// <summary>
        /// Get pin object Get a pin object and its status
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of ApiResponse (PinStatus)</returns>
        public async System.Threading.Tasks.Task<IpfsPinning.SDK.Client.ApiResponse<PinStatus>> PinsRequestidGetWithHttpInfoAsync(string requestid, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken))
        {
            // verify the required parameter 'requestid' is set
            if (requestid == null)
                throw new IpfsPinning.SDK.Client.ApiException(400, "Missing required parameter 'requestid' when calling PinsApi->PinsRequestidGet");


            IpfsPinning.SDK.Client.RequestOptions localVarRequestOptions = new IpfsPinning.SDK.Client.RequestOptions();

            string[] _contentTypes = new string[] {
            };

            // to determine the Accept header
            string[] _accepts = new string[] {
                "application/json"
            };


            var localVarContentType = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderContentType(_contentTypes);
            if (localVarContentType != null) localVarRequestOptions.HeaderParameters.Add("Content-Type", localVarContentType);

            var localVarAccept = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderAccept(_accepts);
            if (localVarAccept != null) localVarRequestOptions.HeaderParameters.Add("Accept", localVarAccept);

            localVarRequestOptions.PathParameters.Add("requestid", IpfsPinning.SDK.Client.ClientUtils.ParameterToString(requestid)); // path parameter

            // authentication (accessToken) required
            // bearer authentication required
            if (!string.IsNullOrEmpty(this.Configuration.AccessToken))
            {
                localVarRequestOptions.HeaderParameters.Add("Authorization", "Bearer " + this.Configuration.AccessToken);
            }

            // make the HTTP request

            var localVarResponse = await this.AsynchronousClient.GetAsync<PinStatus>("/pins/{requestid}", localVarRequestOptions, this.Configuration, cancellationToken).ConfigureAwait(false);

            if (this.ExceptionFactory != null)
            {
                Exception _exception = this.ExceptionFactory("PinsRequestidGet", localVarResponse);
                if (_exception != null) throw _exception;
            }

            return localVarResponse;
        }

        /// <summary>
        /// Replace pin object Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="pin"></param>
        /// <returns>PinStatus</returns>
        public PinStatus PinsRequestidPost(string requestid, Pin pin)
        {
            IpfsPinning.SDK.Client.ApiResponse<PinStatus> localVarResponse = PinsRequestidPostWithHttpInfo(requestid, pin);
            return localVarResponse.Data;
        }

        /// <summary>
        /// Replace pin object Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="pin"></param>
        /// <returns>ApiResponse of PinStatus</returns>
        public IpfsPinning.SDK.Client.ApiResponse<PinStatus> PinsRequestidPostWithHttpInfo(string requestid, Pin pin)
        {
            // verify the required parameter 'requestid' is set
            if (requestid == null)
                throw new IpfsPinning.SDK.Client.ApiException(400, "Missing required parameter 'requestid' when calling PinsApi->PinsRequestidPost");

            // verify the required parameter 'pin' is set
            if (pin == null)
                throw new IpfsPinning.SDK.Client.ApiException(400, "Missing required parameter 'pin' when calling PinsApi->PinsRequestidPost");

            IpfsPinning.SDK.Client.RequestOptions localVarRequestOptions = new IpfsPinning.SDK.Client.RequestOptions();

            string[] _contentTypes = new string[] {
                "application/json"
            };

            // to determine the Accept header
            string[] _accepts = new string[] {
                "application/json"
            };

            var localVarContentType = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderContentType(_contentTypes);
            if (localVarContentType != null) localVarRequestOptions.HeaderParameters.Add("Content-Type", localVarContentType);

            var localVarAccept = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderAccept(_accepts);
            if (localVarAccept != null) localVarRequestOptions.HeaderParameters.Add("Accept", localVarAccept);

            localVarRequestOptions.PathParameters.Add("requestid", IpfsPinning.SDK.Client.ClientUtils.ParameterToString(requestid)); // path parameter
            localVarRequestOptions.Data = pin;

            // authentication (accessToken) required
            // bearer authentication required
            if (!string.IsNullOrEmpty(this.Configuration.AccessToken))
            {
                localVarRequestOptions.HeaderParameters.Add("Authorization", "Bearer " + this.Configuration.AccessToken);
            }

            // make the HTTP request
            var localVarResponse = this.Client.Post<PinStatus>("/pins/{requestid}", localVarRequestOptions, this.Configuration);

            if (this.ExceptionFactory != null)
            {
                Exception _exception = this.ExceptionFactory("PinsRequestidPost", localVarResponse);
                if (_exception != null) throw _exception;
            }

            return localVarResponse;
        }

        /// <summary>
        /// Replace pin object Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="pin"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of PinStatus</returns>
        public async System.Threading.Tasks.Task<PinStatus> PinsRequestidPostAsync(string requestid, Pin pin, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken))
        {
            IpfsPinning.SDK.Client.ApiResponse<PinStatus> localVarResponse = await PinsRequestidPostWithHttpInfoAsync(requestid, pin, cancellationToken).ConfigureAwait(false);
            return localVarResponse.Data;
        }

        /// <summary>
        /// Replace pin object Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)
        /// </summary>
        /// <exception cref="IpfsPinning.SDK.Client.ApiException">Thrown when fails to make API call</exception>
        /// <param name="requestid"></param>
        /// <param name="pin"></param>
        /// <param name="cancellationToken">Cancellation Token to cancel the request.</param>
        /// <returns>Task of ApiResponse (PinStatus)</returns>
        public async System.Threading.Tasks.Task<IpfsPinning.SDK.Client.ApiResponse<PinStatus>> PinsRequestidPostWithHttpInfoAsync(string requestid, Pin pin, System.Threading.CancellationToken cancellationToken = default(System.Threading.CancellationToken))
        {
            // verify the required parameter 'requestid' is set
            if (requestid == null)
                throw new IpfsPinning.SDK.Client.ApiException(400, "Missing required parameter 'requestid' when calling PinsApi->PinsRequestidPost");

            // verify the required parameter 'pin' is set
            if (pin == null)
                throw new IpfsPinning.SDK.Client.ApiException(400, "Missing required parameter 'pin' when calling PinsApi->PinsRequestidPost");


            IpfsPinning.SDK.Client.RequestOptions localVarRequestOptions = new IpfsPinning.SDK.Client.RequestOptions();

            string[] _contentTypes = new string[] {
                "application/json"
            };

            // to determine the Accept header
            string[] _accepts = new string[] {
                "application/json"
            };


            var localVarContentType = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderContentType(_contentTypes);
            if (localVarContentType != null) localVarRequestOptions.HeaderParameters.Add("Content-Type", localVarContentType);

            var localVarAccept = IpfsPinning.SDK.Client.ClientUtils.SelectHeaderAccept(_accepts);
            if (localVarAccept != null) localVarRequestOptions.HeaderParameters.Add("Accept", localVarAccept);

            localVarRequestOptions.PathParameters.Add("requestid", IpfsPinning.SDK.Client.ClientUtils.ParameterToString(requestid)); // path parameter
            localVarRequestOptions.Data = pin;

            // authentication (accessToken) required
            // bearer authentication required
            if (!string.IsNullOrEmpty(this.Configuration.AccessToken))
            {
                localVarRequestOptions.HeaderParameters.Add("Authorization", "Bearer " + this.Configuration.AccessToken);
            }

            // make the HTTP request

            var localVarResponse = await this.AsynchronousClient.PostAsync<PinStatus>("/pins/{requestid}", localVarRequestOptions, this.Configuration, cancellationToken).ConfigureAwait(false);

            if (this.ExceptionFactory != null)
            {
                Exception _exception = this.ExceptionFactory("PinsRequestidPost", localVarResponse);
                if (_exception != null) throw _exception;
            }

            return localVarResponse;
        }

    }
}
