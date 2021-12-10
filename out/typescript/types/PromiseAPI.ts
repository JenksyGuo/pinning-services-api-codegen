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
import { ObservablePinsApi } from './ObservableAPI';

import { PinsApiRequestFactory, PinsApiResponseProcessor} from "../apis/PinsApi";
export class PromisePinsApi {
    private api: ObservablePinsApi

    public constructor(
        configuration: Configuration,
        requestFactory?: PinsApiRequestFactory,
        responseProcessor?: PinsApiResponseProcessor
    ) {
        this.api = new ObservablePinsApi(configuration, requestFactory, responseProcessor);
    }

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
    public pinsGet(cid?: Set<string>, name?: string, match?: TextMatchingStrategy, status?: Set<Status>, before?: Date, after?: Date, limit?: number, meta?: { [key: string]: string; }, _options?: Configuration): Promise<PinResults> {
        const result = this.api.pinsGet(cid, name, match, status, before, after, limit, meta, _options);
        return result.toPromise();
    }

    /**
     * Add a new pin object for the current access token
     * Add pin object
     * @param pin 
     */
    public pinsPost(pin: Pin, _options?: Configuration): Promise<PinStatus> {
        const result = this.api.pinsPost(pin, _options);
        return result.toPromise();
    }

    /**
     * Remove a pin object
     * Remove pin object
     * @param requestid 
     */
    public pinsRequestidDelete(requestid: string, _options?: Configuration): Promise<void> {
        const result = this.api.pinsRequestidDelete(requestid, _options);
        return result.toPromise();
    }

    /**
     * Get a pin object and its status
     * Get pin object
     * @param requestid 
     */
    public pinsRequestidGet(requestid: string, _options?: Configuration): Promise<PinStatus> {
        const result = this.api.pinsRequestidGet(requestid, _options);
        return result.toPromise();
    }

    /**
     * Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)
     * Replace pin object
     * @param requestid 
     * @param pin 
     */
    public pinsRequestidPost(requestid: string, pin: Pin, _options?: Configuration): Promise<PinStatus> {
        const result = this.api.pinsRequestidPost(requestid, pin, _options);
        return result.toPromise();
    }


}



