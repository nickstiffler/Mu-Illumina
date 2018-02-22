import { Injectable } from '@angular/core';
import {Http, Response} from '@angular/http';
import {Observable} from "rxjs/Observable";
import "rxjs/add/operator/map";
import "rxjs/add/operator/publishReplay";

import {Run} from './run';

@Injectable()
export class MuIlluminaService {

	url = "http://128.223.23.216:8000/getResponses.php?";	

	constructor(private http: Http) { }

	getSampleClusters(): Observable<any> {
		return this.http.get(this.url + "update_coi=1&run_id=48&barcode_id=&sort=run_date&cluster_size=400&text_search=&search_type=maize")
	  		.map((res: Response) => res.json())
			.publishReplay()
			.refCount();
		
	}

	getRuns(): Observable<any> {
		return this.http.get(this.url + "get_runs=1")
	  		.map((res: Response) => res.json())
			.publishReplay()
			.refCount();
	}

	getBarcodes(runId: number): Observable<any> {
			
		return this.http.get(this.url + "get_barcodes=" + runId)
			.map((res: Response) => res.json())
			.publishReplay()
			.refCount();
	}

	getCluster(cluster: any): Observable<any> {
			
		return this.http.get(this.url + "details=" + cluster)
			.map((res: Response) => res.json())
			.publishReplay()
			.refCount();
	}

	getCOIs(runIds, barcodeIds, barcodeUnion, page, term, sort) {
		return this.http.get(this.url + "update_coi=1&run_id=" + runIds + "&barcode_id=" + barcodeIds + "&sort=run_date&search_type=maize&overlap=" + barcodeUnion + "&page=" + page + "&text_search=" + term + "&sort=" + sort)
	  		.map((res: Response) => res.json())
			.publishReplay()
			.refCount();
	}

	downloadCOIs(runIds, barcodeIds, barcodeUnion, page, term, sort) {
		window.location.href = this.url + "download=1&run_id=" + runIds + "&barcode_id=" + barcodeIds + "&sort=run_date&search_type=maize&overlap=" + barcodeUnion + "&page=" + page + "&text_search=" + term + "&sort=" + sort;

	}

}

	
