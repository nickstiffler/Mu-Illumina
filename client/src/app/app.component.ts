import { Component, OnInit, Renderer } from '@angular/core';

import {NgbPanelChangeEvent} from '@ng-bootstrap/ng-bootstrap';

import { ClipboardService } from 'ngx-clipboard';

import {MuIlluminaService} from './mu-illumina.service';
import {Cluster} from './cluster';
import {Run} from './run';
import {Barcode} from './barcode';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {
	clusters: Cluster[];
	selectedCluster: Cluster;
	runs: Run[];
	selectedRuns: number[];
	barcodes: Barcode[];
	selectedBarcodes: Barcode[];
	Object = Object;
	serverError = false;
	runQuery = false;
	barcodeUnion = false;
	clusterCount = 0;
	queryDone = false;
	page = 1;
	coiCount = 0;
	term = "";
	sort = false;
	
	constructor(private service: MuIlluminaService, private clipboardService: ClipboardService, private renderer: Renderer) {
	}

	ngOnInit() {
		this.getRuns();
//		this.getClassRuns();	
//		this.getSampleClusters();
		this.selectedRuns = [];
		this.selectedBarcodes = [];
	}

	getSampleClusters() {
		this.service.getSampleClusters().subscribe(applicationData => {
			this.clusters = applicationData;
		});
	}

	getCluster($event: NgbPanelChangeEvent) {
		if($event.nextState) {
			this.service.getCluster($event.panelId).subscribe(applicationData => {
				this.selectedCluster = applicationData;
				let panel =	document.getElementById($event.panelId)
				setTimeout(function() {
						panel.scrollIntoView(); 
						window.scroll(0, window.scrollY - 100);
					}
					, 300);
			});
		}
	}	

	getRuns() {
		this.service.getRuns().subscribe(applicationData => {
			this.runs = applicationData;
		//	this.getBarcodes(this.runs[0]);
		});
	}

	getClassRuns() {
		let ill41 = new Run();
		ill41.date = new Date("2017-07-13");
		ill41.id = 126;
		ill41.lane = 1;
		ill41.name = "Illumina-41";
		ill41.public = 1;

		let pml10 = new Run();
		pml10.date = new Date("2011-07-27");
		pml10.id = 23;
		pml10.lane = 2;
		pml10.name = "PML-10";
		pml10.public = 0;

		let ill11 = new Run();
		ill11.date = new Date("2010-12-15");
		ill11.id = 13;
		ill11.lane = 1;
		ill11.name = "Illumina-11";
		ill11.public = 1;

		let ill30 = new Run();
		ill30.date = new Date("2014-10-30");
		ill30.id = 81;
		ill30.lane = 1;
		ill30.name = "Illumina-30";
		ill30.public = 1;

		let ill35 = new Run();
		ill35.date = new Date("2015-08-25");
		ill35.id = 107;
		ill35.lane = 8;
		ill35.name = "Illumina-355555";
		ill35.public = 1;

		
		let ill8 = new Run();
		ill8.date = new Date("2010-03-17");
		ill8.id = 7;
		ill8.lane = 8;
		ill8.name = "Illumina-8";
		ill8.public = 1;

		this.runs = [ill41, pml10, ill11, ill30, ill35, ill8];
		
	}

	selectRun($event) {
		this.clusters = [];
		this.queryDone = false;
		let runCheckbox = $event.srcElement.value;
		if($event.srcElement.checked) {
			this.selectedRuns.push(runCheckbox);
		} else {
			this.selectedRuns.splice(this.selectedRuns.indexOf(runCheckbox), 1);
		}
		if(this.selectedRuns.length == 1) {
			this.getBarcodes(this.selectedRuns[0]);
		} else {
			this.barcodes = [];
		}
	}

	selectAll($event) {
		this.selectedRuns = [];
		for(let run of this.runs) {
			this.selectedRuns.push(run.id);
			
		}
	}

	isSelected(id) {
		for(let i of this.selectedRuns) {
			if(i == id) {
				return true;
			}
		}
		return false;
	}

	deselect() {

		this.selectedRuns = [];
	}

	selectBarcode($event) {
		this.clusters = [];
		this.queryDone = false;
		let bcCheckbox = $event.srcElement.value;
		if($event.srcElement.checked) {
			this.selectedBarcodes.push(bcCheckbox);
		} else {
			this.selectedBarcodes.splice(this.selectedBarcodes.indexOf(bcCheckbox), 1);
		}
	}

	getBarcodes(run) {
		this.selectedBarcodes = [];
		this.service.getBarcodes(run).subscribe(applicationData => {
			this.barcodes = [];
			for(let a in applicationData) {
				let bc = new Barcode();
				bc.id = parseInt(a);
				bc.barcode = applicationData[a];
				bc.date_id = run.id;
				this.barcodes.push(bc);
			}
		});
	}

	getCOIs() {
		this.clusters = [];
		this.queryDone = false;
		let runIds = this.selectedRuns.join(",");
		let barcodeIds = this.selectedBarcodes.join(",");
		this.runQuery = true;
		let sort = "run_date";
		if(this.sort) {
			sort = "loc";
		}
		this.service.getCOIs(runIds, barcodeIds, this.barcodeUnion, this.page - 1, this.term, sort).subscribe(applicationData => {
			this.clusters = applicationData.data;
			this.clusterCount = applicationData.count;
			this.runQuery = false;
			this.queryDone = true;
		}, error => {
			this.serverError = true;
			this.runQuery = false;
		});
	}

	downloadSelected() {
		let runIds = this.selectedRuns.join(",");
		let barcodeIds = this.selectedBarcodes.join(",");

		this.service.downloadCOIs(runIds, barcodeIds, this.barcodeUnion, this.page - 1, this.term, "")
	}

	copyCluster(cluster: Cluster) {
		this.clipboardService.copyFromContent(cluster.chr + ":" + cluster.start + "-" + cluster.end, this.renderer);
	}
}
