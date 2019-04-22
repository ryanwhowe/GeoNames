
class GeoNames:
    def __init__(self):
        self.DIR = {'downloads': '/Downloads/', 'loaders': '/Loaders/'}

    def getDir(self, basedir, requestdir):
        return basedir + self.DIR[requestdir]


class GeoNamesDump:
    """Meta and operational code for the GeoNames Dump location
    There are only specific files that are needed from the GeoNames Dump location and there is no need to scrape all
    the files and download them since the allCountries.zip contains them all.  Once these files have been downloaded an
    initial time then only the incremental files are needed to keep the system up to date.

    """

    def __init__(self):
        self.URL = 'http://download.geonames.org/export/dump/'
        self.FILES = ['allCountries.zip', 'alternateNames.zip', 'admin2Codes.txt', 'admin1CodesASCII.txt',
                      'featureCodes_en.txt', 'timeZones.txt', 'countryInfo.txt']
        self.INCREMENTAL = ['deletes-', 'modifications-', 'alternateNamesDeletes-', 'alternateNamesModifications-']


class GeoNamesPostalCodes:
    def __init__(self):
        self.URL = 'http://download.geonames.org/export/zip/'
        self.FILES = ['US.zip']
