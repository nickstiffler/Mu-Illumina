import { TestBed, inject } from '@angular/core/testing';

import { MuIlluminaService } from './mu-illumina.service';

describe('MuIlluminaService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [MuIlluminaService]
    });
  });

  it('should be created', inject([MuIlluminaService], (service: MuIlluminaService) => {
    expect(service).toBeTruthy();
  }));
});
