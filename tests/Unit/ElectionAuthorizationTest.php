<?php

namespace Tests\Unit;

use App\Http\Middleware\RequireUserType;
use App\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ElectionAuthorizationTest extends TestCase
{
    public function test_election_capabilities_are_explicit_by_role(): void
    {
        $national = new User(['user_type_id' => User::TYPE_NATIONAL_DIRECTOR]);
        $regional = new User(['user_type_id' => User::TYPE_REGIONAL_DIRECTOR]);
        $constituency = new User(['user_type_id' => User::TYPE_CONSTITUENCY_DIRECTOR]);
        $agent = new User(['user_type_id' => User::TYPE_POLLING_AGENT]);

        $this->assertTrue($national->canReviewNationalResults());
        $this->assertTrue($regional->canReviewRegionalResults());
        $this->assertTrue($constituency->canApproveResults());
        $this->assertTrue($constituency->canCaptureResults());
        $this->assertTrue($agent->canCaptureResults());
        $this->assertFalse($regional->canApproveResults());
        $this->assertFalse($national->canCaptureResults());
    }

    public function test_exact_role_middleware_allows_only_the_configured_type(): void
    {
        $request = Request::create('/region/dashboard');
        $request->setUserResolver(fn () => new User(['user_type_id' => User::TYPE_REGIONAL_DIRECTOR]));

        $response = (new RequireUserType())->handle(
            $request,
            fn () => new Response('allowed'),
            (string) User::TYPE_REGIONAL_DIRECTOR
        );

        $this->assertSame('allowed', $response->getContent());
    }

    public function test_exact_role_middleware_rejects_another_type(): void
    {
        $request = Request::create('/region/dashboard');
        $request->setUserResolver(fn () => new User(['user_type_id' => User::TYPE_NATIONAL_DIRECTOR]));

        try {
            (new RequireUserType())->handle(
                $request,
                fn () => new Response('not reached'),
                (string) User::TYPE_REGIONAL_DIRECTOR
            );
            $this->fail('The middleware allowed the wrong user type.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }
}
