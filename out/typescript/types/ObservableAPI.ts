import { ResponseContext, RequestContext, HttpFile } from '../http/http';
import * as models from '../models/all';
import { Configuration} from '../configuration'
import { Observable, of, from } from '../rxjsStub';
import {mergeMap, map} from  '../rxjsStub';
import { Failure } from '../models/Failure';
import { FailureError } from '../models/FailureError';
import { Pin } from '../models/Pin';
import { PinResults } from '../models/PinResults';
import { PinStatus } from '../models/PinStatus';
import { Status } from '../models/Status';
import { TextMatchingStrategy } from '../models/TextMatchingStrategy';

import { PinsApiRequestFactory, PinsApiResponseProcessor} from "../apis/PinsApi";
export class ObservablePinsApi {
    private requestFactory: PinsApiRequestFactory;
    private responseProcessor: PinsApiResponseProcessor;
    private configuration: Configuration;

    public constructor(
        configuration: Configuration,
        requestFactory?: PinsApiRequestFactory,
        responseProcessor?: PinsApiResponseProcessor
    ) {
        this.configuration = configuration;
        this.requestFactory = requestFactory || new PinsApiRequestFactory(configuration);
        this.responseProcessor = responseProcessor || new PinsApiResponseProcessor();
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
    public pinsGet(cid?: Set<string>, name?: string, match?: TextMatchingStrategy, status?: Set<Status>, before?: Date, after?: Date, limit?: number, meta?: { [key: string]: string; }, _options?: Configuration): Observable<PinResults> {
        const requestContextPromise = this.requestFactory.pinsGet(cid, name, match, status, before, after, limit, meta, _options);

        // build promise chain
        let middlewarePreObservable = from<RequestContext>(requestContextPromise);
        for (let middleware of this.configuration.middleware) {
            middlewarePreObservable = middlewarePreObservable.pipe(mergeMap((ctx: RequestContext) => middleware.pre(ctx)));
        }

        return middlewarePreObservable.pipe(mergeMap((ctx: RequestContext) => this.configuration.httpApi.send(ctx))).
            pipe(mergeMap((response: ResponseContext) => {
                let middlewarePostObservable = of(response);
                for (let middleware of this.configuration.middleware) {
                    middlewarePostObservable = middlewarePostObservable.pipe(mergeMap((rsp: ResponseContext) => middleware.post(rsp)));
                }
                return middlewarePostObservable.pipe(map((rsp: ResponseContext) => this.responseProcessor.pinsGet(rsp)));
            }));
    }
 
    /**
     * Add a new pin object for the current access token
     * Add pin object
     * @param pin 
     */
    public pinsPost(pin: Pin, _options?: Configuration): Observable<PinStatus> {
        const requestContextPromise = this.requestFactory.pinsPost(pin, _options);

        // build promise chain
        let middlewarePreObservable = from<RequestContext>(requestContextPromise);
        for (let middleware of this.configuration.middleware) {
            middlewarePreObservable = middlewarePreObservable.pipe(mergeMap((ctx: RequestContext) => middleware.pre(ctx)));
        }

        return middlewarePreObservable.pipe(mergeMap((ctx: RequestContext) => this.configuration.httpApi.send(ctx))).
            pipe(mergeMap((response: ResponseContext) => {
                let middlewarePostObservable = of(response);
                for (let middleware of this.configuration.middleware) {
                    middlewarePostObservable = middlewarePostObservable.pipe(mergeMap((rsp: ResponseContext) => middleware.post(rsp)));
                }
                return middlewarePostObservable.pipe(map((rsp: ResponseContext) => this.responseProcessor.pinsPost(rsp)));
            }));
    }
 
    /**
     * Remove a pin object
     * Remove pin object
     * @param requestid 
     */
    public pinsRequestidDelete(requestid: string, _options?: Configuration): Observable<void> {
        const requestContextPromise = this.requestFactory.pinsRequestidDelete(requestid, _options);

        // build promise chain
        let middlewarePreObservable = from<RequestContext>(requestContextPromise);
        for (let middleware of this.configuration.middleware) {
            middlewarePreObservable = middlewarePreObservable.pipe(mergeMap((ctx: RequestContext) => middleware.pre(ctx)));
        }

        return middlewarePreObservable.pipe(mergeMap((ctx: RequestContext) => this.configuration.httpApi.send(ctx))).
            pipe(mergeMap((response: ResponseContext) => {
                let middlewarePostObservable = of(response);
                for (let middleware of this.configuration.middleware) {
                    middlewarePostObservable = middlewarePostObservable.pipe(mergeMap((rsp: ResponseContext) => middleware.post(rsp)));
                }
                return middlewarePostObservable.pipe(map((rsp: ResponseContext) => this.responseProcessor.pinsRequestidDelete(rsp)));
            }));
    }
 
    /**
     * Get a pin object and its status
     * Get pin object
     * @param requestid 
     */
    public pinsRequestidGet(requestid: string, _options?: Configuration): Observable<PinStatus> {
        const requestContextPromise = this.requestFactory.pinsRequestidGet(requestid, _options);

        // build promise chain
        let middlewarePreObservable = from<RequestContext>(requestContextPromise);
        for (let middleware of this.configuration.middleware) {
            middlewarePreObservable = middlewarePreObservable.pipe(mergeMap((ctx: RequestContext) => middleware.pre(ctx)));
        }

        return middlewarePreObservable.pipe(mergeMap((ctx: RequestContext) => this.configuration.httpApi.send(ctx))).
            pipe(mergeMap((response: ResponseContext) => {
                let middlewarePostObservable = of(response);
                for (let middleware of this.configuration.middleware) {
                    middlewarePostObservable = middlewarePostObservable.pipe(mergeMap((rsp: ResponseContext) => middleware.post(rsp)));
                }
                return middlewarePostObservable.pipe(map((rsp: ResponseContext) => this.responseProcessor.pinsRequestidGet(rsp)));
            }));
    }
 
    /**
     * Replace an existing pin object (shortcut for executing remove and add operations in one step to avoid unnecessary garbage collection of blocks present in both recursive pins)
     * Replace pin object
     * @param requestid 
     * @param pin 
     */
    public pinsRequestidPost(requestid: string, pin: Pin, _options?: Configuration): Observable<PinStatus> {
        const requestContextPromise = this.requestFactory.pinsRequestidPost(requestid, pin, _options);

        // build promise chain
        let middlewarePreObservable = from<RequestContext>(requestContextPromise);
        for (let middleware of this.configuration.middleware) {
            middlewarePreObservable = middlewarePreObservable.pipe(mergeMap((ctx: RequestContext) => middleware.pre(ctx)));
        }

        return middlewarePreObservable.pipe(mergeMap((ctx: RequestContext) => this.configuration.httpApi.send(ctx))).
            pipe(mergeMap((response: ResponseContext) => {
                let middlewarePostObservable = of(response);
                for (let middleware of this.configuration.middleware) {
                    middlewarePostObservable = middlewarePostObservable.pipe(mergeMap((rsp: ResponseContext) => middleware.post(rsp)));
                }
                return middlewarePostObservable.pipe(map((rsp: ResponseContext) => this.responseProcessor.pinsRequestidPost(rsp)));
            }));
    }
 
}
