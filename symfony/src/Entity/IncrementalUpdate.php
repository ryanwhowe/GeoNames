<?php

namespace Chs\Geoname\Entity;

use Exception;

class IncrementalUpdate implements \Serializable {

    const TYPE_DELETE = 1;
    const TYPE_UPDATE = 2;

    private int $type;
    private GeoName $data;

    /**
     * @return int
     */
    public function getType(): int {
        return $this->type;
    }

    /**
     * @param int $type
     * @return IncrementalUpdate
     */
    public function setType(int $type): IncrementalUpdate {
        if(!in_array($type, [self::TYPE_DELETE, self::TYPE_UPDATE])) throw new \InvalidArgumentException('Invalid Update Type: ' . $type);
        $this->type = $type;
        return $this;
    }

    /**
     * @return GeoName
     */
    public function getData(): GeoName {
        return $this->data;
    }

    /**
     * @param GeoName $data
     * @return IncrementalUpdate
     */
    public function setData(GeoName $data): IncrementalUpdate {
        $this->data = $data;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function serialize() {
        return [
            'type' => $this->getType(),
            'data' => $this->getData()->serialize()
        ];
    }

    /**
     * @inheritDoc
     */
    public function unserialize(string $data) {
        $data = json_decode($data, true);
        $this->setType($data['type']);
        $geoname = new GeoName();
        $geoname->unserialize(implode("\t",$data['data']));
        $this->setData($geoname);
    }

    public function isDelete(): bool {
        return self::TYPE_DELETE === $this->type;
    }
    public function isUpdate(): bool {
        return self::TYPE_UPDATE === $this->type;
    }
}