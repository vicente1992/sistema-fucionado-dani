<?php

namespace Classes;

use Exception;

class GeoLocation
{
    private $providerKey = 'q49696zvy4aq1y';
    private $providerUrl = 'https://api.ipregistry.co/';

    public function checkLocation($geoIp)
    {
        try {
            $url = implode('', [$this->providerUrl, $geoIp['ip'],'?key=', $this->providerKey]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result);


            return array(
                'address' => $result->location->city,
                'map' => 'https://www.google.com/maps/search/?api=1&query=' . $result->location->latitude . ',' . $result->location->longitude,
                'coordinates' => implode(',', [$result->location->latitude, $result->location->longitude]),
            );

        } catch (Exception $e) {
            return array(
                'address' => null,
                'map' => null,
                'coordinates' => null,
            );
        }
    }
}