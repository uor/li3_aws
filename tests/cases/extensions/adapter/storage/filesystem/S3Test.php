<?php

namespace li3_aws\tests\cases\extensions\adapter\storage\filesystem;

use li3_aws\extensions\adapter\storage\filesystem\S3;

class S3Test extends \lithium\test\Unit {
    protected $configuration = array(
        'bucket' => 'li3_aws_test'
    );

    public function setUp() {
        $this->s3 = new S3($this->configuration);
    }

    public function tearDown() {
        unset($this->s3);
    }

    public function testSimpleWrite() {
        $filename = 'test_file';
        $data = 'test data';

        $closure = $this->s3->write($filename, $data);
        $this->assertTrue(is_callable($closure));

        $params = compact('filename', 'data');
        $result = $closure($this->s3, $params, null);
        $this->assertTrue($result);

        $result = file_get_contents('http://s3.amazonaws.com/'.$this->configuration['bucket'].'/'.$filename);
        $this->assertEqual($data, $result);
    }
}
