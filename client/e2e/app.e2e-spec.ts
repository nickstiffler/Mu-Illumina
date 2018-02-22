import { MuIlluminaPage } from './app.po';

describe('mu-illumina App', () => {
  let page: MuIlluminaPage;

  beforeEach(() => {
    page = new MuIlluminaPage();
  });

  it('should display welcome message', () => {
    page.navigateTo();
    expect(page.getParagraphText()).toEqual('Welcome to app!!');
  });
});
