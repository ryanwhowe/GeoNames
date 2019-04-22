
class GeoNames:
    def __init__(self):
        self.url = 'http://download.geonames.org/export/dump/'
        self.directories = {'downloads': '/Downloads/', 'loaders': '/Loaders/'}
    def getDir(self, basedir, requestdir):
        return basedir + self.directories[requestdir]
