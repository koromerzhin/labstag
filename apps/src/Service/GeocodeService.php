<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\GeoCode;
use Labstag\Repository\GeoCodeRepository;
use Labstag\RequestHandler\GeoCodeRequestHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use ZipArchive;

class GeocodeService
{

    protected HttpClientInterface $client;

    protected EntityManagerInterface $entityManager;

    protected GeoCodeRequestHandler $geoCodeRH;

    protected GeoCodeRepository $repository;

    public function __construct(
        HttpClientInterface $client,
        GeoCodeRepository $repository,
        EntityManagerInterface $entityManager,
        GeoCodeRequestHandler $geoCodeRH
    )
    {
        $this->client        = $client;
        $this->repository    = $repository;
        $this->entityManager = $entityManager;
        $this->geoCodeRH     = $geoCodeRH;
    }

    public function add(array $row)
    {
        $entity = $this->repository->findOneBy(
            [
                'countryCode' => $row[0],
                'postalCode'  => $row[1],
                'placeName'   => $row[2],
            ]
        );
        if (!($entity instanceof GeoCode)) {
            $entity = new GeoCode();
            $entity->setCountryCode($row[0]);
            $entity->setPostalCode($row[1]);
            $entity->setPlaceName($row[2]);
        }

        $old = clone $entity;
        $entity->setStateName($row[3]);
        $entity->setStateCode($row[4]);
        $entity->setProvinceName($row[5]);
        $entity->setProvinceCode($row[6]);
        $entity->setCommunityName($row[7]);
        $entity->setCommunityCode($row[8]);
        $entity->setLatitude($row[9]);
        $entity->setLongitude($row[10]);
        $entity->setAccuracy((int) $row[11]);
        $this->geoCodeRH->handle($old, $entity);
    }

    public function csv(string $country)
    {
        $country    = strtoupper($country);
        $file       = 'http://download.geonames.org/export/zip/'.$country.'.zip';
        $response   = $this->client->request(
            'GET',
            $file
        );
        $statusCode = $response->getStatusCode();
        if (200 != $statusCode) {
            return [];
        }

        $content  = $response->getContent();
        $tempFile = tmpfile();
        $path     = stream_get_meta_data($tempFile)['uri'];
        file_put_contents($path, $content);
        $zip = new ZipArchive();
        if (!$zip->open($path)) {
            return [];
        }

        $content = $zip->getFromName($country.'.txt');
        $csv     = str_getcsv($content, "\n");
        $zip->close();

        return $csv;
    }

    public function tables(array $csv): array
    {
        $data = [];
        foreach ($csv as $line) {
            $row    = str_getcsv($line, "\t");
            $data[] = $row;
        }

        return $data;
    }
}
