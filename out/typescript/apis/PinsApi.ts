// TODO: better import syntax?
import { BaseAPIRequestFactory, RequiredError } from './baseapi';
import {Configuration} from '../configuration';
import { RequestContext, HttpMethod, ResponseContext, HttpFile} from '../http/http';
import {ObjectSerializer} from '../models/ObjectSerializer';
import {ApiException} from './exception';
import {isCodeInRange} from '../util';

import { Failure } from '../models/Failure';
import { Pin } from '../models/Pin';
import { PinResults } from '../models/PinResults';
import { PinStatus } from '../models/PinStatus';
import { Set } from '../models/Set';
import { Status } from '../models/Status';
import { TextMatchingStrategy } from '../models/TextMatchingStrategy';

/**
 * no description
 */
export class PinsApiRequestFactory extends BaseAPIRequestFactory {

    /**
     * List all the pin objects, matching optional filters; when no filter is provided, only successful pins are returned
     * List pin objects
     * @param cid Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts
     * @param name Return pin objects with specified name (by default a case-sensitive, exact match)
     * @param match Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies
     * @param status Return pin objects for pins with the specified status
     * @param before Return results created (queued) before provided timestamp
     * @param after Return results created (queued) after provided timestamp
     * @param limit Max records to return
     * @param meta Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport
     */
    public async pinsGet(cid?: Set<string>, name?: string, match?: TextMatchingStrategy, status?: Set<Status>, before?: Date, after?: Date, limit?: number, meta?: { [key: string]: string; }, _options?: Configuration): Promise<RequestContext> {
        let _config = _options || this.configuration;









        // Path Params
        const localVarPath = '/pins';

        // Make Request Context
        const requestContext = _config.baseServer.makeRequestContext(localVarPath, HttpMethod.GET);
        requestContext.setHeaderParam("Accept", "application/json, */*;q=0.8")

        // Query Params
        if (cid !== undefined) {
            requestContext.setQueryParam("cid", ObjectSerializer.serialize(cid, "Set<string>", ""));
        }
        if (name !== undefined) {
            requestContext.setQueryParam("name", ObjectSerializer.serialize(name, "string", ""));
        }
        if (match !== undefined) {
            requestContext.setQueryParam("match", ObjectSerializer.serialize(match, "TextMatchingStrategy", ""));
        }
        if (status !== undefined) {
            requestContext.setQueryParam("status", ObjectSerializer.serialize(status, "Set<Status>", ""));
        }
        if (before !== undefined) {
            requestContext.setQueryParam("before", ObjectSerializer.serialize(before, "Date", "date-time"));
        }
        if (after !== undefined) {
            requestContext.setQueryParam("after", ObjectSerializer.serialize(after, "Date", "date-time"));
        }
        if (limit !== undefined) {
            requestContext.setQueryParam("limit", ObjectSerializer.serialize(limit, "number", "int32"));
        }
        if (meta !== undefined) {
            requestContext.setQueryParam("meta", ObjectSerializer.serialize(meta, "{ [key: string]: string; }", ""));
        }

        // Header Params

        // Form Params


        // Body Params

        let authMethod = null;
        // Apply auth methods
        authMethod = _config.authMethods["accessToken"]
        if (authMethod) {
            await authMethod.applySecurityAuthentication(requestContext);
        }

        return requestContext;
    }

    /**
     * Add a new pin object for the current access token
     * Add pin object
     * @param pin 
     */
    public async pinsPost(pin: Pin, _options?: Configuration): Promise<RequestContext> {
        let _config = _options || this.configuration;

        // verify required parameter 'pin' is not null or undefined
        if (pin === null || pin === undefined) {
            throw new RequiredError('Required parameter pin was null or undefined when calling pinsPost.');
        }


        // Path Params
        const localVarPath = '/pins';

        // Make Request Context
        const requestContext = _config.baseServer.makeRequestContext(localVarPath, HttpMethod.POST);
        requestContext.setHeaderParam("Accept", "application/json, */*;q=0.8")

        // Query Params

        // Header Params

        // Form Params


        // Body Params
        const contentType = ObjectSerializer.getPreferredMediaType([
            "application/json"
        ]);
        requestContext.setHeaderParam("Content-Type", contentType);
        const serializedBody = ObjectSerializer.stringify(
            ObjectSerializer.serialize(pin, "Pin", ""),
            contentType
        );
        requestContext.setBody(serializedBody);

        let authMethod = null;
        // Apply auth methods
        authMethod = _config.authMethods["accessToken"]
        if (authMethod) {
            await authMethod.applySecurityAuthentication(requestContext);
        }

        return requestContext;
    }

    /**
     * Remove a pin object
     * Remove pin object
     * @param requestid 
     */
    public async pinsRequestidDelete(requestid: string, _options?: Configuration): Promise<RequestContext> {
        let _config = _options || this.configuration;

        // verify required parameter 'requestid' is not null or undefined
        if (requestid === null || requestid === undefined) {
            throw new RequiredError('Required parameter requestid was null or undefined when calling pinsRequestidDelete.');
        }


        // Path Params
        const localVarPath = '/pins/{requestid}'
            .replace('{' + 'requestid' + '}', encodeURIComponent(String(requestid)));

        // Make Request Context
        const requestContext = _config.baseServer.makeRequestContext(localVarPath, HttpMethod.DELETE);
        requestContext.setHeaderParam("Accept", "application/json, */*;q=0.8")

        // Query Params

        // Header Params

        // Form Params


        // Body Params

        let authMethod = null;
        // Apply auth methods
        authMethod = _config.authMethods["accessToken"]
        if (authMethod) {
            await authMethod.applySecurityAuthentication(requestContext);
        }

        return requestContext;
    }

    /**
     * Get a pin object and its status
     * Get pin object
     * @param requestid 
     */
    public async pinsRequestidGet(requestid: string, _options?: Configuration): Promise<RequestContext> {
        let _config = _options || this.configuration;

        // verify required parameter 'requestid' is not null or undefined
        if (requestid === null || requestid === undefined) {
            throw new RequiredError('Required parameter requestid was null or undefined when calling pinsRequestidGet.');
        }


        // Path Params
        const localVarPath = '/pins/{requestid}'
            .replace('{' + 'requestid' + '}', encodeURIComponent(String(requestid)));

        // Make Request Context
        const requestContext = _config.baseServer.makeRequestContext(localVarPath, HttpMethod.GET);
        requestContext.setHeaderParam("Accept", "application/json, */*;q=0.8")

        // Query Params

        // Header Params

        // Form Params


        // Body Params

        let authMethod = null;
        // Apply auth methods
        authMethod = _config.authMethods["accessToken"]
        if (authMethod) {
            await authMethod.applySecurityAuthentication(requestContext);
        }

        return requestContext;
    }

    /**
     * Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)
     * Replace pin object
     * @param requestid 
     * @param pin 
     */
    public async pinsRequestidPost(requestid: string, pin: Pin, _options?: Configuration): Promise<RequestContext> {
        let _config = _options || this.configuration;

        // verify required parameter 'requestid' is not null or undefined
        if (requestid === null || requestid === undefined) {
            throw new RequiredError('Required parameter requestid was null or undefined when calling pinsRequestidPost.');
        }


        // verify required parameter 'pin' is not null or undefined
        if (pin === null || pin === undefined) {
            throw new RequiredError('Required parameter pin was null or undefined when calling pinsRequestidPost.');
        }


        // Path Params
        const localVarPath = '/pins/{requestid}'
            .replace('{' + 'requestid' + '}', encodeURIComponent(String(requestid)));

        // Make Request Context
        const requestContext = _config.baseServer.makeRequestContext(localVarPath, HttpMethod.POST);
        requestContext.setHeaderParam("Accept", "application/json, */*;q=0.8")

        // Query Params

        // Header Params

        // Form Params


        // Body Params
        const contentType = ObjectSerializer.getPreferredMediaType([
            "application/json"
        ]);
        requestContext.setHeaderParam("Content-Type", contentType);
        const serializedBody = ObjectSerializer.stringify(
            ObjectSerializer.serialize(pin, "Pin", ""),
            contentType
        );
        requestContext.setBody(serializedBody);

        let authMethod = null;
        // Apply auth methods
        authMethod = _config.authMethods["accessToken"]
        if (authMethod) {
            await authMethod.applySecurityAuthentication(requestContext);
        }

        return requestContext;
    }

}

export class PinsApiResponseProcessor {

    /**
     * Unwraps the actual response sent by the server from the response context and deserializes the response content
     * to the expected objects
     *
     * @params response Response returned by the server for a request to pinsGet
     * @throws ApiException if the response code was not in [200, 299]
     */
     public async pinsGet(response: ResponseContext): Promise<PinResults > {
        const contentType = ObjectSerializer.normalizeMediaType(response.headers["content-type"]);
        if (isCodeInRange("200", response.httpStatusCode)) {
            const body: PinResults = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "PinResults", ""
            ) as PinResults;
            return body;
        }
        if (isCodeInRange("400", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(400, body);
        }
        if (isCodeInRange("401", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(401, body);
        }
        if (isCodeInRange("404", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(404, body);
        }
        if (isCodeInRange("409", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(409, body);
        }
        if (isCodeInRange("4XX", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(4XX, body);
        }
        if (isCodeInRange("5XX", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(5XX, body);
        }

        // Work around for missing responses in specification, e.g. for petstore.yaml
        if (response.httpStatusCode >= 200 && response.httpStatusCode <= 299) {
            const body: PinResults = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "PinResults", ""
            ) as PinResults;
            return body;
        }

        let body = response.body || "";
        throw new ApiException<string>(response.httpStatusCode, "Unknown API Status Code!\nBody: \"" + body + "\"");
    }

    /**
     * Unwraps the actual response sent by the server from the response context and deserializes the response content
     * to the expected objects
     *
     * @params response Response returned by the server for a request to pinsPost
     * @throws ApiException if the response code was not in [200, 299]
     */
     public async pinsPost(response: ResponseContext): Promise<PinStatus > {
        const contentType = ObjectSerializer.normalizeMediaType(response.headers["content-type"]);
        if (isCodeInRange("202", response.httpStatusCode)) {
            const body: PinStatus = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "PinStatus", ""
            ) as PinStatus;
            return body;
        }
        if (isCodeInRange("400", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(400, body);
        }
        if (isCodeInRange("401", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(401, body);
        }
        if (isCodeInRange("404", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(404, body);
        }
        if (isCodeInRange("409", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(409, body);
        }
        if (isCodeInRange("4XX", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(4XX, body);
        }
        if (isCodeInRange("5XX", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(5XX, body);
        }

        // Work around for missing responses in specification, e.g. for petstore.yaml
        if (response.httpStatusCode >= 200 && response.httpStatusCode <= 299) {
            const body: PinStatus = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "PinStatus", ""
            ) as PinStatus;
            return body;
        }

        let body = response.body || "";
        throw new ApiException<string>(response.httpStatusCode, "Unknown API Status Code!\nBody: \"" + body + "\"");
    }

    /**
     * Unwraps the actual response sent by the server from the response context and deserializes the response content
     * to the expected objects
     *
     * @params response Response returned by the server for a request to pinsRequestidDelete
     * @throws ApiException if the response code was not in [200, 299]
     */
     public async pinsRequestidDelete(response: ResponseContext): Promise<void > {
        const contentType = ObjectSerializer.normalizeMediaType(response.headers["content-type"]);
        if (isCodeInRange("202", response.httpStatusCode)) {
            return;
        }
        if (isCodeInRange("400", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(400, body);
        }
        if (isCodeInRange("401", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(401, body);
        }
        if (isCodeInRange("404", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(404, body);
        }
        if (isCodeInRange("409", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(409, body);
        }
        if (isCodeInRange("4XX", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(4XX, body);
        }
        if (isCodeInRange("5XX", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(5XX, body);
        }

        // Work around for missing responses in specification, e.g. for petstore.yaml
        if (response.httpStatusCode >= 200 && response.httpStatusCode <= 299) {
            const body: void = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "void", ""
            ) as void;
            return body;
        }

        let body = response.body || "";
        throw new ApiException<string>(response.httpStatusCode, "Unknown API Status Code!\nBody: \"" + body + "\"");
    }

    /**
     * Unwraps the actual response sent by the server from the response context and deserializes the response content
     * to the expected objects
     *
     * @params response Response returned by the server for a request to pinsRequestidGet
     * @throws ApiException if the response code was not in [200, 299]
     */
     public async pinsRequestidGet(response: ResponseContext): Promise<PinStatus > {
        const contentType = ObjectSerializer.normalizeMediaType(response.headers["content-type"]);
        if (isCodeInRange("200", response.httpStatusCode)) {
            const body: PinStatus = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "PinStatus", ""
            ) as PinStatus;
            return body;
        }
        if (isCodeInRange("400", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(400, body);
        }
        if (isCodeInRange("401", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(401, body);
        }
        if (isCodeInRange("404", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(404, body);
        }
        if (isCodeInRange("409", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(409, body);
        }
        if (isCodeInRange("4XX", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(4XX, body);
        }
        if (isCodeInRange("5XX", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(5XX, body);
        }

        // Work around for missing responses in specification, e.g. for petstore.yaml
        if (response.httpStatusCode >= 200 && response.httpStatusCode <= 299) {
            const body: PinStatus = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "PinStatus", ""
            ) as PinStatus;
            return body;
        }

        let body = response.body || "";
        throw new ApiException<string>(response.httpStatusCode, "Unknown API Status Code!\nBody: \"" + body + "\"");
    }

    /**
     * Unwraps the actual response sent by the server from the response context and deserializes the response content
     * to the expected objects
     *
     * @params response Response returned by the server for a request to pinsRequestidPost
     * @throws ApiException if the response code was not in [200, 299]
     */
     public async pinsRequestidPost(response: ResponseContext): Promise<PinStatus > {
        const contentType = ObjectSerializer.normalizeMediaType(response.headers["content-type"]);
        if (isCodeInRange("202", response.httpStatusCode)) {
            const body: PinStatus = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "PinStatus", ""
            ) as PinStatus;
            return body;
        }
        if (isCodeInRange("400", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(400, body);
        }
        if (isCodeInRange("401", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(401, body);
        }
        if (isCodeInRange("404", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(404, body);
        }
        if (isCodeInRange("409", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(409, body);
        }
        if (isCodeInRange("4XX", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(4XX, body);
        }
        if (isCodeInRange("5XX", response.httpStatusCode)) {
            const body: Failure = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "Failure", ""
            ) as Failure;
            throw new ApiException<Failure>(5XX, body);
        }

        // Work around for missing responses in specification, e.g. for petstore.yaml
        if (response.httpStatusCode >= 200 && response.httpStatusCode <= 299) {
            const body: PinStatus = ObjectSerializer.deserialize(
                ObjectSerializer.parse(await response.body.text(), contentType),
                "PinStatus", ""
            ) as PinStatus;
            return body;
        }

        let body = response.body || "";
        throw new ApiException<string>(response.httpStatusCode, "Unknown API Status Code!\nBody: \"" + body + "\"");
    }

}
