<?php

namespace Messerli90\Hunterio\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Messerli90\Hunterio\DomainSearch;
use Messerli90\Hunterio\Exceptions\InvalidRequestException;
use Messerli90\Hunterio\Tests\TestCase;

class DomainSearchTest extends TestCase
{
    /** @var \Messerli90\Hunterio\DomainSearch */
    protected $domain_search;

    protected function setUp(): void
    {
        parent::setUp();

        $this->domain_search = new DomainSearch('apikey');
    }

    /** @test */
    public function it_gets_instantiated_with_an_API_key()
    {
        $this->assertEquals('apikey', $this->domain_search->__get('api_key'));
    }

    /** @test */
    public function it_sets_attributes()
    {
        $this->domain_search->domain('ghost.org');
        $this->assertEquals('ghost.org', $this->domain_search->domain);

        $this->domain_search->company('Ghost');
        $this->assertEquals('Ghost', $this->domain_search->company);

        $this->domain_search->limit(20);
        $this->assertEquals(20, $this->domain_search->limit);

        $this->domain_search->offset(15);
        $this->assertEquals(15, $this->domain_search->offset);

        $this->domain_search->type('personal');
        $this->assertEquals('personal', $this->domain_search->type);

        $this->domain_search->seniority('junior');
        $this->assertEquals(['junior'], $this->domain_search->seniority);

        $this->domain_search->seniority(['junior', 'senior']);
        $this->assertEquals(['junior', 'senior'], $this->domain_search->seniority);

        $this->domain_search->seniority(['junior', 'senior', 'ignore', 'this too']);
        $this->assertEquals(['junior', 'senior'], $this->domain_search->seniority);

        $this->domain_search->department('it');
        $this->assertEquals(['it'], $this->domain_search->department);

        $this->domain_search->department(['it', 'management']);
        $this->assertEquals(['it', 'management'], $this->domain_search->department);

        $this->domain_search->department(['it', 'management', 'ignore', 'this too']);
        $this->assertEquals(['it', 'management'], $this->domain_search->department);
    }

    /** @test */
    public function it_sets_limit_attribute_to_10_if_number_provided_is_bigger_than_100()
    {
        $this->domain_search->limit(101);
        $this->assertEquals(10, $this->domain_search->limit);
    }

    /** @test */
    public function throws_an_InvalidRequestException_when_invalid_type_is_supplied()
    {
        $this->expectException(InvalidRequestException::class);

        $this->domain_search->type('bad type');
    }

    /** @test */
    public function it_can_chain_methods()
    {
        $this->domain_search->domain('ghost.org')->company('Ghost')->limit(22)->offset(8);

        $this->assertEquals('ghost.org', $this->domain_search->domain);
        $this->assertEquals('Ghost', $this->domain_search->company);
        $this->assertEquals(22, $this->domain_search->limit);
        $this->assertEquals(8, $this->domain_search->offset);
    }

    /** @test */
    public function it_builds_the_query()
    {
        $expected_query = [
            'company' => null,
            'domain' => 'ghost.org',
            'type' => 'personal',
            'department' => 'it,management',
            'seniority' => 'junior',
            'limit' => 2,
            'offset' => 2,
            'api_key' => 'apikey'
        ];

        $query = $this->domain_search->domain('ghost.org')->department(['it', 'management'])->type('personal')
            ->seniority('junior')->limit(2)->offset(2)->make();

        $this->assertEquals($expected_query, $query);
    }

    /** @test */
    public function it_throws_an_InvalidRequestException_when_required_fields_are_missing()
    {
        $this->expectException(InvalidRequestException::class);

        $this->domain_search->make();
    }

    /** @test */
    public function it_returns_a_response_with_get()
    {
        $expected_response = file_get_contents(__DIR__ . '/../mocks/domain-search.json');

        Http::fake(function ($request) use ($expected_response) {
            return Http::response($expected_response);
        });

        $response = $this->domain_search->domain('ghost.org')->get();

        $this->assertEquals(json_decode($expected_response, true)['data']['domain'], $response['data']['domain']);
    }
}
