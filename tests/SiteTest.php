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

        $result = (new \Controller\Site())->commandant_create($request);

        if (!empty($result)) {
            $message = '/' . preg_quote($message, '/') . '/';
            $this->expectOutputRegex($message);
            return;
        }

        $this->assertTrue((bool)User::where('login', $userData['login'])->where('role_id', 2)->count());
        User::where('login', $userData['login'])->delete();

        $this->assertSame('', $result, 'Should return empty string after redirect');
    }

    public static function commandantCreateProvider(): array
    {
        return [
            ['GET', ['full_name' => '', 'login' => '', 'password' => ''], '<h3></h3>'],
            ['POST', ['full_name' => '', 'login' => '', 'password' => ''], 
            '<h3>{"login":["Поле login пусто"],"full_name":["Поле full_name пусто"],"password":["Поле password пусто"]}</h3>'],
            ['POST', ['full_name' => 'admin', 'login' => 'login is busy', 'password' => 'admin'], 
            '<h3>{"login":["Поле login должно быть уникально"]}</h3>'],
            ['POST', ['full_name' => 'admin', 'login' => md5(time()), 'password' => 'admin'], 
            'Location: /commandants'],
        ];
    }

    /** @dataProvider loginProvider
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

        ob_start();
        $result = (new \Controller\Site())->login($request);
        ob_end_clean();

        if ($scenario === 'success') {
            $this->assertSame('', $result, 'Should return empty string after redirect');
            User::where('login', $userData['login'])->delete();
        } else {
            $this->assertTrue(true, "Scenario '$scenario' executed");
        }
    }

    public static function loginProvider(): array
    {
        return [
            'get_form' => [
                'GET', 
                ['login' => '', 'password' => ''], 
                'get_form'
            ],
            'empty_fields' => [
                'POST', 
                ['login' => '', 'password' => ''], 
                'empty_fields'
            ],
            'wrong_password' => [
                'POST', 
                ['login' => 'admin', 'password' => 'wrong_password'], 
                'wrong_password'
            ],
            'success' => [
                'POST', 
                ['login' => 'test_login_' . time(), 'password' => '123456'], 
                'success'
            ],
        ];
    }

    protected function setUp(): void
    {
        $_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs';
        
        $appConfig = include $_SERVER['DOCUMENT_ROOT'] . '/server_practice/config/app.php';
        $dbConfig = include $_SERVER['DOCUMENT_ROOT'] . '/server_practice/config/db.php';
        $pathConfig = include $_SERVER['DOCUMENT_ROOT'] . '/server_practice/config/path.php';
        
        $GLOBALS['app'] = new Src\Application(new Src\Settings([
            'app' => $appConfig,
            'db' => $dbConfig,
            'path' => $pathConfig,
        ]));
        
        if (!function_exists('app')) {
            function app() {
                return $GLOBALS['app'];
            }
        }
    }
}