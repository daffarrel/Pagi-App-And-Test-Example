<?php
use MyApp\App;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class AppTest extends PHPUnit_Framework_TestCase
{
    private $properties;

    public function setUp()
    {
        $this->properties = array(
            'variables' => array(),
            'resultString' => array()
        );
    }

    /**
     * @test
     */
    public function can_read_caller_id()
    {
        $mock = new PAGI\Client\Impl\MockedClientImpl($this->properties);
        $log = new Logger('name');
        $log->pushHandler(new StreamHandler(
          __DIR__ . '/../runtime/log/test.log', Logger::DEBUG
        ));
        $mock->setLogger($log);
        $mock
            ->assert('answer')
            ->assert('getFullVariable', array('CALLERID(num)'))
            ->assert('streamFile', array(SOUNDS_PATH . '/you-are-calling-from'))
            ->assert('sayDigits', array('5555555'))
            ->assert('streamFile', array(SOUNDS_PATH . '/bye'))
            ->assert('hangup')
            ->onAnswer(true)
            ->onGetFullVariable(true, '5555555')
            ->onStreamFile(false, '#')
            ->onSayDigits(true, '#')
            ->onStreamFile(false, '#')
            ->onHangup(true)
        ;

        $app = new App(array('pagiClient' => $mock));
        $app->setLogger($log);
        $app->init();
        $app->run();
        $app->shutdown();
    }

    /**
     * @test
     */
    public function can_handle_anonymous_calls()
    {
        $mock = new PAGI\Client\Impl\MockedClientImpl($this->properties);
        $log = new Logger('name');
        $log->pushHandler(new StreamHandler(
          __DIR__ . '/../runtime/log/test.log', Logger::DEBUG
        ));
        $mock->setLogger($log);
        $mock
            ->assert('answer')
            ->assert('getFullVariable', array('CALLERID(num)'))
            ->assert('streamFile', array(SOUNDS_PATH . '/i-cant-find-your-number'))
            ->assert('streamFile', array(SOUNDS_PATH . '/bye'))
            ->assert('hangup')
            ->onAnswer(true)
            ->onGetFullVariable(true, 'anonymous')
            ->onStreamFile(false, '#')
            ->onStreamFile(false, '#')
            ->onHangup(true)
        ;
        $app = new App(array('pagiClient' => $mock));
        $app->setLogger($log);
        $app->init();
        $app->run();
        $app->shutdown();
    }
}
