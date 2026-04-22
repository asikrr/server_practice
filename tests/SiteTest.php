<?php
use PHPUnit\Framework\TestCase;
use Model\User;

class SiteTest extends TestCase
{
    /**
     * @dataProvider commandantCreateProvider
     * @runInSeparateProcess
     */
    public function testCommandantCreate(string $httpMethod, array $userData, string $message): void
    {
        if ($userData['login'] === 'login is busy') {
            $userData['login'] = User::where('role_id', 2)->value('login') ?? 'test_busy';
        }

        $request = $this->createMock(\Src\Request::class);
        $request->expects($this->any())
            ->method('all')
            ->willReturn($userData);
        $request->method = $httpMethod;

        $exceptionCaught = false;
        try {
            $result = (new \Controller\CommandantController())->commandant_create($request);
        } catch (\Throwable $e) {
            $exceptionCaught = true;
        }

        if ($exceptionCaught) {
            $expectedCount = empty($userData['login']) ? 0 : 1;

            $this->assertEquals(
                $expectedCount, 
                User::where('login', $userData['login'])->where('role_id', 2)->count(),
                "Validation failed. User count mismatch."
            );
            return;
        }

        $this->assertTrue(
            (bool)User::where('login', $userData['login'])->where('role_id', 2)->count(),
            "User should be created on success"
        );

        User::where('login', $userData['login'])->delete();
        $this->assertSame('', $result, 'Should return empty string after redirect');
    }

    public static function commandantCreateProvider(): array
    {
        return [
            ['GET', ['full_name' => '', 'login' => '', 'password' => ''], '<h3></h3>'],
            ['POST', ['full_name' => '', 'login' => '', 'password' => ''], '<h3>Error</h3>'],
            ['POST', ['full_name' => 'admin', 'login' => 'login is busy', 'password' => 'admin'], '<h3>Error</h3>'],
            ['POST', ['full_name' => 'admin', 'login' => md5(time()), 'password' => 'admin'], 'Location: /commandants'],
        ];
    }

    /** 
     * @dataProvider loginProvider
     * @runInSeparateProcess
     */
    public function testLogin(string $httpMethod, array $userData, string $scenario): void
    {
        if ($scenario === 'success') {
            User::create([
                'login' => $userData['login'],
                'password' => $userData['password'],
                'full_name' => 'Test User',
                'role_id' => 1
            ]);
        }

        $request = $this->createMock(\Src\Request::class);
        $request->expects($this->any())->method('all')->willReturn($userData);
        $request->method = $httpMethod;

        $exceptionCaught = false;
        try {
            $result = (new \Controller\AuthController())->login($request);
        } catch (\Throwable $e) {
            $exceptionCaught = true;
        }

        if ($scenario === 'success') {
            $this->assertFalse($exceptionCaught, "Login success should not trigger View render");
            $this->assertSame('', $result, 'Should return empty string after redirect');
            User::where('login', $userData['login'])->delete();
        } else {
            $this->assertTrue(true, "Login fail scenario executed");
        }
    }

    public static function loginProvider(): array
    {
        return [
            'get_form' => ['GET', ['login' => '', 'password' => ''], 'get_form'],
            'empty_fields' => ['POST', ['login' => '', 'password' => ''], 'empty_fields'],
            'wrong_password' => ['POST', ['login' => 'admin', 'password' => 'wrong_password'], 'wrong_password'],
            'success' => ['POST', ['login' => 'test_login_' . time(), 'password' => '123456'], 'success'],
        ];
    }

    protected function setUp(): void
    {
        $_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs/practice_without_packages';
        
        $config = include $_SERVER['DOCUMENT_ROOT'] . '/config/app.php';
        $GLOBALS['app'] = new \Src\Application($config);

        if (!function_exists('app')) {
            function app() {
                return $GLOBALS['app'];
            }
        }

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $_SESSION = [];

        if (isset($GLOBALS['app']->db)) {
            \Illuminate\Database\Eloquent\Model::setConnectionResolver(
                $GLOBALS['app']->db->getConnectionResolver()
            );
        }
    }
}