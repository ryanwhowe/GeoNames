def get_dir(basedir, requestdir):
    directories = {'downloads': '/Downloads/', 'loaders': '/Loaders/'}
    return basedir + directories[requestdir]
