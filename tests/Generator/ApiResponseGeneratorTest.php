<?php

namespace MattJanssen\ApiResponseBundle\Test\Generator;

use MattJanssen\ApiResponseBundle\Compiler\ApiConfigCompiler;
use MattJanssen\ApiResponseBundle\DependencyInjection\Configuration;
use MattJanssen\ApiResponseBundle\Factory\SerializerAdapterFactory;
use MattJanssen\ApiResponseBundle\Generator\ApiResponseGenerator;
use MattJanssen\ApiResponseBundle\Serializer\Adapter\JsonEncodeSerializerAdapter;
use MattJanssen\ApiResponseBundle\Test\AppMocker;
use MattJanssen\ApiResponseBundle\Test\AppTestCase;
use MattJanssen\ApiResponseBundle\Test\Fixtures\TestCategory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseGeneratorTest extends AppTestCase
{
    public function testSuccessResponseDataWithJsonSerializer()
    {
        $generator = AppMocker::getApiResponseGenerator();

        $response = $generator->generateSuccessResponse($this->getTestCategory(), null, [], Configuration::SERIALIZER_JSON_ENCODE);

        self::assertJsonStringEqualsArray([
            'data' => $this->getTestCategoryRelationshipDataArray(),
            'error' => null,
        ], $response->getContent());
    }

    public function testSuccessResponseRelationshipDataWithJsonSerializer()
    {
        $generator = AppMocker::getApiResponseGenerator();

        $response = $generator->generateSuccessResponse($this->getTestCategory(), null, ['relationships'], Configuration::SERIALIZER_JSON_ENCODE);

        self::assertJsonStringEqualsArray([
            'data' => $this->getTestCategoryRelationshipDataArray(),
            'error' => null,
        ], $response->getContent());
    }

    public function testSuccessResponseDataWithJsonGroupSerializer()
    {
        $generator = AppMocker::getApiResponseGenerator();

        $response = $generator->generateSuccessResponse($this->getTestCategory(), null, [], Configuration::SERIALIZER_JSON_GROUP_ENCODE);

        self::assertJsonStringEqualsArray([
            'data' => $this->getTestCategoryDataArray(),
            'error' => null,
        ], $response->getContent());
    }

    public function testSuccessResponseRelationshipDataWithJsonGroupSerializer()
    {
        $generator = AppMocker::getApiResponseGenerator();

        $response = $generator->generateSuccessResponse($this->getTestCategory(), null, ['relationships'], Configuration::SERIALIZER_JSON_GROUP_ENCODE);

        self::assertJsonStringEqualsArray([
            'data' => $this->getTestCategoryRelationshipDataArray(),
            'error' => null,
        ], $response->getContent());
    }

    public function testSuccessResponseDataWithJmsSerializer()
    {
        $generator = AppMocker::getApiResponseGenerator();

        $response = $generator->generateSuccessResponse($this->getTestCategory(), null, [], Configuration::SERIALIZER_JMS_SERIALIZER);

        self::assertJsonStringEqualsArray([
            'data' => $this->getTestCategoryDataArray(),
            'error' => null,
        ], $response->getContent());
    }

    public function testSuccessResponseRelationshipDataWithJmsSerializer()
    {
        $generator = AppMocker::getApiResponseGenerator();

        $response = $generator->generateSuccessResponse($this->getTestCategory(), null, ['relationships'], Configuration::SERIALIZER_JMS_SERIALIZER);

        self::assertJsonStringEqualsArray([
            'data' => $this->getTestCategoryRelationshipDataArray(),
            'error' => null,
        ], $response->getContent());
    }

    public function testSuccessResponseWithDefaultHttpCode()
    {
        $generator = AppMocker::getApiResponseGenerator();

        $response = $generator->generateSuccessResponse('foobar');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testSuccessResponseWithSpecificHttpCode()
    {
        $generator = AppMocker::getApiResponseGenerator();

        $response = $generator->generateSuccessResponse('foobar', 234);

        self::assertSame(234, $response->getStatusCode());
    }

    public function testSuccessResponseWithDefaultSerializer()
    {
        $factoryMock = \Mockery::mock(SerializerAdapterFactory::class)->shouldReceive('createSerializerAdapter')->once()->with(Configuration::SERIALIZER_JSON_ENCODE)->andReturn(new JsonEncodeSerializerAdapter())->getMock();
        $generator = new ApiResponseGenerator(new RequestStack(), new ApiConfigCompiler([], []), $factoryMock);

        $generator->generateSuccessResponse('foobar');
    }

    public function testSuccessResponseWithSpecificSerializer()
    {
        $factoryMock = \Mockery::mock(SerializerAdapterFactory::class)->shouldReceive('createSerializerAdapter')->once()->with('other_serializer')->andReturn(new JsonEncodeSerializerAdapter())->getMock();
        $generator = new ApiResponseGenerator(new RequestStack(), new ApiConfigCompiler([], []), $factoryMock);

        $generator->generateSuccessResponse('foobar', null, [], 'other_serializer');
    }

    public function testSuccessResponseContentType()
    {
        $generator = AppMocker::getApiResponseGenerator();

        $response = $generator->generateSuccessResponse('foobar');

        self::assertSame('application/json', $response->headers->get('content-type'));
    }

    public function testErrorResponseWithDefaults()
    {
        $generator = AppMocker::getApiResponseGenerator();

        $response = $generator->generateErrorResponse();

        self::assertJsonStringEqualsArray([
            'data' => null,
            'error' => [
                'code' => 0,
                'title' => null,
                'errorData' => null,
            ],
        ], $response->getContent());
    }

    public function testErrorResponseData()
    {
        $generator = AppMocker::getApiResponseGenerator();

        $response = $generator->generateErrorResponse(500, 42, 'foobar', $this->getTestCategory());

        self::assertJsonStringEqualsArray([
            'data' => null,
            'error' => [
                'code' => 42,
                'title' => 'foobar',
                'errorData' => $this->getTestCategoryRelationshipDataArray(),
            ],
        ], $response->getContent());
    }

    public function testErrorResponseWithDefaultHttpCode()
    {
        $generator = AppMocker::getApiResponseGenerator();

        $response = $generator->generateErrorResponse();

        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function testErrorResponseWithSpecificHttpCode()
    {
        $generator = AppMocker::getApiResponseGenerator();

        $response = $generator->generateErrorResponse(456);

        self::assertSame(456, $response->getStatusCode());
    }

    private function getTestCategory()
    {
        $category = (new TestCategory())
            ->setParent(new TestCategory())
            ->setChildren([
                new TestCategory(),
                new TestCategory(),
            ]);

        return $category;
    }

    private function getTestCategoryDataArray()
    {
        return [
            'id' => 42,
            'name' => 'foobar',
        ];
    }

    private function getTestCategoryRelationshipDataArray()
    {
        return [
            'id' => 42,
            'name' => 'foobar',
            'parent' => 42,
            'children' => [42, 42],
        ];
    }
}
