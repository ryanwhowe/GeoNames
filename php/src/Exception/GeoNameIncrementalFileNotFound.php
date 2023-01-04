<?php

namespace Chs\Geoname\Exception;


class GeoNameIncrementalFileNotFound extends GeoNameException {

    const MESSAGE = "An incremental update file could not be found on the filesystem: ";

    public function __construct(string $filename = "", int $code = 0, \Throwable $previous = null) {
        parent::__construct(self::MESSAGE . $filename, $code, $previous);
    }

}