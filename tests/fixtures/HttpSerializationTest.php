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

    public function testSerializeObjectInjecting(): void
    {
        $response = $this->postJson('/api', [
            'a' => 'aaa',
            'b' => 'post',
            'type' => 'BBB',
        ]);

        $response->assertContent('{"a":"aaa","b":"post","type":"BBB"}');
        $response->assertStatus(200);
    }
}
