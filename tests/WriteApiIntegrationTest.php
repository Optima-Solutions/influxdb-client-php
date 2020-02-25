<?php

namespace InfluxDB2Test;

use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\WriteOptions;
use InfluxDB2\WriteType;
use PHPUnit\Framework\TestCase;

class WriteApiIntegrationTest extends TestCase
{
    private $client;
    private $writeApi;

    /**
     * @before
     */
    public function setUp()
    {
        $this->client = new Client([
            "url" => "http://localhost:9999",
            "token" => "my-token",
            "bucket" => "my-bucket",
            "precision" => WritePrecision::NS,
            "org" => "my-org",
            "debug" => true
        ]);
        $this->writeApi = $this->client->createWriteApi();
    }

    public function testExistsWriteApi()
    {
        $this->assertNotNull($this->writeApi);
    }

    public function testWriteApiWriteRaw()
    {
        $payload = 'h2o_feet,location=coyote_creek water_level=2.0 2';
        $response = $this->writeApi->writeRaw($payload);
        self::assertNull($response);
    }

    public function testWriteArray()
    {
        $data = [
            'name' => "h2o",
            'tags' => [
                'host' => 'aws', 'region' => 'us'
            ],
            'fields' => [
                'level' => 5, 'saturation' => 99
            ],
            'time' => 123
        ];

        $response = $this->writeApi->write($data, WritePrecision::S, "my-bucket", "my-org");
        self::assertNull($response);
    }

    public function testBatchingWrite()
    {
        $writeApi = $this->client->createWriteApi(
            ["writeType"=>WriteType::BATCHING, 'batchSize'=>3, "flushInterval" =>1000]);

        $data = ['name' => 'cpu',
            'tags' => ['host' => 'server_nl', 'region' => 'us'],
            'fields' => ['internal' => 5, 'external' => 6],
            'time' => microtime()];

        //            ['name' => 'gpu', 'fields' => ['value' => 0.9999]];

        $writeApi->write($data,WritePrecision::US);

        $p1 = ['name' => "h2o", 'tags' => ['host' => 'aws', 'region' => 'us'], 'fields' => ['level' => 1, 'saturation' => 99], 'time' => 1];
        $p2 = ['name' => "h2o", 'tags' => ['host' => 'aws', 'region' => 'us'], 'fields' => ['level' => 2, 'saturation' => 98], 'time' => 2];
        $p3 = ['name' => "h2o", 'tags' => ['host' => 'aws', 'region' => 'us'], 'fields' => ['level' => 3, 'saturation' => 97], 'time' => 3];
        $p4 = ['name' => "h2o", 'tags' => ['host' => 'aws', 'region' => 'us'], 'fields' => ['level' => 4, 'saturation' => 96], 'time' => 4];
        $p5 = ['name' => "h2o", 'tags' => ['host' => 'aws', 'region' => 'us'], 'fields' => ['level' => 5, 'saturation' => 95], 'time' => 5];

        $writeApi->write($p1);
        $writeApi->write($p2);
        $writeApi->write($p3);
        $writeApi->write($p4);
        $writeApi->write($p5);

        $this->assertNotNull($writeApi);

    }

}
