<?php

namespace Tests\Feature;

use Tests\TestCase;

class HttpSerializationTest extends TestCase
{
    public function testSerializeGetResponse(): void
    {
        $response = $this->get('/api');

        $response->assertContent('{"a":"aaa","b":"get","type":"AAA"}');
        $response->assertStatus(200);
    }

    public function testSerializeObjectInjectingCallable(): void
    {
        $response = $this->post('/api', [
            'a' => 'aaa',
            'b' => 'post',
            'type' => 'BBB',
        ]);

        $response->assertContent('{"a":"aaa","b":"post","type":"BBB"}');
        $response->assertStatus(200);
    }

    public function testSerializeObjectInjectingController(): void
    {
        $response = $this->post('/api/controller', [
            'a' => 'something',
            'b' => 'something else',
            'type' => 'AAA',
        ]);

        $response->assertContent('{"a":"something","b":"something else","type":"AAA"}');
        $response->assertStatus(200);
    }

    public function testWhenPayloadIsInvalidThenThrowBadRequest(): void
    {
        $response = $this->post('/api/controller', [
            'a' => 'something',
            'b' => 'something else',
            'type' => 'YYY',
        ]);

        $response->assertJson(['message' => '"YYY" is not a valid backing value for enum "App\Models\Type"']);
        $response->assertStatus(400);
    }
}
