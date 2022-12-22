<?php

class GeoName implements JsonSerializable, Serializable {

    private int $geonameid;
    private string $name;
    private string $asciiname;
    private string $alternatenames;
    private string $latitude;
    private string $longitude;
    private string $fclass;
    private string $fcode;
    private string $country;
    private string $cc2;
    private string $admin1;
    private string $admin2;
    private string $admin3;
    private string $admin4;
    private int $population;
    private int $elevation;
    private int $gtopo30;
    private string $timezone;
    private string $moddate;

    /**
     * @return int
     */
    public function getGeonameid(): int {
        return $this->geonameid;
    }

    /**
     * @param int $geonameid
     * @return GeoName
     */
    public function setGeonameid(int $geonameid): GeoName {
        $this->geonameid = $geonameid;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     * @return GeoName
     */
    public function setName(string $name): GeoName {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAsciiname(): string {
        return $this->asciiname;
    }

    /**
     * @param string $asciiname
     * @return GeoName
     */
    public function setAsciiname(string $asciiname): GeoName {
        $this->asciiname = $asciiname;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlternatenames(): string {
        return $this->alternatenames;
    }

    /**
     * @param string $alternatenames
     * @return GeoName
     */
    public function setAlternatenames(string $alternatenames): GeoName {
        $this->alternatenames = $alternatenames;
        return $this;
    }

    /**
     * @return string
     */
    public function getLatitude(): string {
        return $this->latitude;
    }

    /**
     * @param string $latitude
     * @return GeoName
     */
    public function setLatitude(string $latitude): GeoName {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return string
     */
    public function getLongitude(): string {
        return $this->longitude;
    }

    /**
     * @param string $longitude
     * @return GeoName
     */
    public function setLongitude(string $longitude): GeoName {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return string
     */
    public function getFclass(): string {
        return $this->fclass;
    }

    /**
     * @param string $fclass
     * @return GeoName
     */
    public function setFclass(string $fclass): GeoName {
        $this->fclass = $fclass;
        return $this;
    }

    /**
     * @return string
     */
    public function getFcode(): string {
        return $this->fcode;
    }

    /**
     * @param string $fcode
     * @return GeoName
     */
    public function setFcode(string $fcode): GeoName {
        $this->fcode = $fcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): string {
        return $this->country;
    }

    /**
     * @param string $country
     * @return GeoName
     */
    public function setCountry(string $country): GeoName {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getCc2(): string {
        return $this->cc2;
    }

    /**
     * @param string $cc2
     * @return GeoName
     */
    public function setCc2(string $cc2): GeoName {
        $this->cc2 = $cc2;
        return $this;
    }

    /**
     * @return string
     */
    public function getAdmin1(): string {
        return $this->admin1;
    }

    /**
     * @param string $admin1
     * @return GeoName
     */
    public function setAdmin1(string $admin1): GeoName {
        $this->admin1 = $admin1;
        return $this;
    }

    /**
     * @return string
     */
    public function getAdmin2(): string {
        return $this->admin2;
    }

    /**
     * @param string $admin2
     * @return GeoName
     */
    public function setAdmin2(string $admin2): GeoName {
        $this->admin2 = $admin2;
        return $this;
    }

    /**
     * @return string
     */
    public function getAdmin3(): string {
        return $this->admin3;
    }

    /**
     * @param string $admin3
     * @return GeoName
     */
    public function setAdmin3(string $admin3): GeoName {
        $this->admin3 = $admin3;
        return $this;
    }

    /**
     * @return string
     */
    public function getAdmin4(): string {
        return $this->admin4;
    }

    /**
     * @param string $admin4
     * @return GeoName
     */
    public function setAdmin4(string $admin4): GeoName {
        $this->admin4 = $admin4;
        return $this;
    }

    /**
     * @return int
     */
    public function getPopulation(): int {
        return $this->population;
    }

    /**
     * @param int $population
     * @return GeoName
     */
    public function setPopulation(int $population): GeoName {
        $this->population = $population;
        return $this;
    }

    /**
     * @return int
     */
    public function getElevation(): int {
        return $this->elevation;
    }

    /**
     * @param int $elevation
     * @return GeoName
     */
    public function setElevation(int $elevation): GeoName {
        $this->elevation = $elevation;
        return $this;
    }

    /**
     * @return int
     */
    public function getGtopo30(): int {
        return $this->gtopo30;
    }

    /**
     * @param int $gtopo30
     * @return GeoName
     */
    public function setGtopo30(int $gtopo30): GeoName {
        $this->gtopo30 = $gtopo30;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone(): string {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     * @return GeoName
     */
    public function setTimezone(string $timezone): GeoName {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @return string
     */
    public function getModdate(): string {
        return $this->moddate;
    }

    /**
     * @param string $moddate
     * @return GeoName
     */
    public function setModdate(string $moddate): GeoName {
        $this->moddate = $moddate;
        return $this;
    }


    public function jsonSerialize() {
        // TODO: Implement jsonSerialize() method.
    }

    public function serialize() {
        // TODO: Implement serialize() method.
    }

    public function unserialize(string $data) {
        $parts = explode("\t", $data);
        $this->geonameid = $parts[0];
        $this->name = $parts[1];
        $this->asciiname = $parts[2];
        $this->alternatenames = $parts[3];
        $this->latitude = $parts[4];
        $this->longitude = $parts[5];
        $this->fclass = $parts[6];
        $this->fcode = $parts[7];
        $this->country = $parts[8];
        $this->cc2 = $parts[9];
        $this->admin1 = $parts[10];
        $this->admin2 = $parts[11];
        $this->admin3 = $parts[12];
        $this->admin4 = $parts[13];
        $this->population = $parts[14];
        $this->elevation = $parts[15];
        $this->gtopo30 = $parts[16];
        $this->timezone = $parts[17];
        $this->moddate = $parts[18];
    }
}