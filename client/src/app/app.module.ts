import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { NgModule } from '@angular/core';
import {HttpModule} from "@angular/http";
import { FormsModule } from '@angular/forms';

import {NgbModule} from '@mattlewis92/ng-bootstrap';

import { ClipboardModule } from 'ngx-clipboard';

import { AppComponent } from './app.component';

import { MuIlluminaService } from './mu-illumina.service';

@NgModule({
  declarations: [
    AppComponent
  ],
  imports: [
    BrowserModule,
	BrowserAnimationsModule,
	HttpModule,
	FormsModule,
	ClipboardModule,
	NgbModule.forRoot()
  ],
  providers: [MuIlluminaService],
  bootstrap: [AppComponent]
})
export class AppModule { }
