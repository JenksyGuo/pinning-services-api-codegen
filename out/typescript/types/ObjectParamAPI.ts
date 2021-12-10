import { ResponseContext, RequestContext, HttpFile } from '../http/http';
import * as models from '../models/all';
import { Configuration} from '../configuration'

import { Failure } from '../models/Failure';
import { FailureError } from '../models/FailureError';
import { Pin } from '../models/Pin';
import { PinResults } from '../models/PinResults';
import { PinStatus } from '../models/PinStatus';
import { Status } from '../models/Status';
import { TextMatchingStrategy } from '../models/TextMatchingStrategy';

import { ObservablePinsApi } from "./ObservableAPI";
import { PinsApiRequestFactory, PinsApiResponseProcessor} from "../apis/PinsApi";

export interface PinsApiPinsGetRequest {
    /**
     * Return pin objects responsible for pinning the specified CID(s); be aware that using longer hash functions introduces further constraints on the number of CIDs that will fit under the limit of 2000 characters per URL  in browser contexts
     * @type Set&lt;string&gt;
     * @memberof PinsApipinsGet
     */
    cid?: Set<string>
    /**
     * Return pin objects with specified name (by default a case-sensitive, exact match)
     * @type string
     * @memberof PinsApipinsGet
     */
    name?: string
    /**
     * Customize the text matching strategy applied when the name filter is present; exact (the default) is a case-sensitive exact match, partial matches anywhere in the name, iexact and ipartial are case-insensitive versions of the exact and partial strategies
     * @type TextMatchingStrategy
     * @memberof PinsApipinsGet
     */
    match?: TextMatchingStrategy
    /**
     * Return pin objects for pins with the specified status
     * @type Set&lt;Status&gt;
     * @memberof PinsApipinsGet
     */
    status?: Set<Status>
    /**
     * Return results created (queued) before provided timestamp
     * @type Date
     * @memberof PinsApipinsGet
     */
    before?: Date
    /**
     * Return results created (queued) after provided timestamp
     * @type Date
     * @memberof PinsApipinsGet
     */
    after?: Date
    /**
     * Max records to return
     * @type number
     * @memberof PinsApipinsGet
     */
    limit?: number
    /**
     * Return pin objects that match specified metadata keys passed as a string representation of a JSON object; when implementing a client library, make sure the parameter is URL-encoded to ensure safe transport
     * @type { [key: string]: string; }
     * @memberof PinsApipinsGet
     */
    meta?: { [key: string]: string; }
}

export interface PinsApiPinsPostRequest {
    /**
     * 
     * @type Pin
     * @memberof PinsApipinsPost
     */
    pin: Pin
}

export interface PinsApiPinsRequestidDeleteRequest {
    /**
     * 
     * @type string
     * @memberof PinsApipinsRequestidDelete
     */
    requestid: string
}

export interface PinsApiPinsRequestidGetRequest {
    /**
     * 
     * @type string
     * @memberof PinsApipinsRequestidGet
     */
    requestid: string
}

export interface PinsApiPinsRequestidPostRequest {
    /**
     * 
     * @type string
     * @memberof PinsApipinsRequestidPost
     */
    requestid: string
    /**
     * 
     * @type Pin
     * @memberof PinsApipinsRequestidPost
     */
    pin: Pin
}

export class ObjectPinsApi {
    private api: ObservablePinsApi

    public constructor(configuration: Configuration, requestFactory?: PinsApiRequestFactory, responseProcessor?: PinsApiResponseProcessor) {
        this.api = new ObservablePinsApi(configuration, requestFactory, responseProcessor);
    }

    /**
     * List all the pin objects, matching optional filters; when no filter is provided, only successful pins are returned
     * List pin objects
     * @param param the request object
     */
    public pinsGet(param: PinsApiPinsGetRequest, options?: Configuration): Promise<PinResults> {
        return this.api.pinsGet(param.cid, param.name, param.match, param.status, param.before, param.after, param.limit, param.meta,  options).toPromise();
    }

    /**
     * Add a new pin object for the current access token
     * Add pin object
     * @param param the request object
     */
    public pinsPost(param: PinsApiPinsPostRequest, options?: Configuration): Promise<PinStatus> {
        return this.api.pinsPost(param.pin,  options).toPromise();
    }

    /**
     * Remove a pin object
     * Remove pin object
     * @param param the request object
     */
    public pinsRequestidDelete(param: PinsApiPinsRequestidDeleteRequest, options?: Configuration): Promise<void> {
        return this.api.pinsRequestidDelete(param.requestid,  options).toPromise();
    }

    /**
     * Get a pin object and its status
     * Get pin object
     * @param param the request object
     */
    public pinsRequestidGet(param: PinsApiPinsRequestidGetRequest, options?: Configuration): Promise<PinStatus> {
        return this.api.pinsRequestidGet(param.requestid,  options).toPromise();
    }

    /**
     * Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)
     * Replace pin object
     * @param param the request object
     */
    public pinsRequestidPost(param: PinsApiPinsRequestidPostRequest, options?: Configuration): Promise<PinStatus> {
        return this.api.pinsRequestidPost(param.requestid, param.pin,  options).toPromise();
    }

}
