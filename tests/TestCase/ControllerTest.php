<?php

namespace SwaggerBake\Test\TestCase;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class ControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var string[]
     */
    public $fixtures = [
        'plugin.SwaggerBake.Departments',
    ];

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        static::setAppNamespace('SwaggerBakeTest\App');
    }

    public function test_swagger_index(): void
    {
        $this->get('/');
        $this->assertResponseOk();
    }

    public function test_redoc_index(): void
    {
        $this->get('/?doctype=redoc');
        $this->assertResponseOk();
    }
}