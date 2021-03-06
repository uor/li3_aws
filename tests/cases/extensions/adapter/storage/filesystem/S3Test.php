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

    public function testSimpleWriteExistsAndDelete() {
        $filename = 'test_file';
        $data = 'test data';

        $this->_checkFileExists($filename, false);

        $closure = $this->s3->write($filename, $data);
        $this->assertTrue(is_callable($closure));

        $params = compact('filename', 'data');
        $result = $closure($this->s3, $params, null);
        $this->assertEqual(200, $result->status);

        $url = 'http://' . $this->configuration['bucket'] . '.s3.amazonaws.com/'.$filename;
        $result = file_get_contents($url);
        $this->assertEqual($data, $result);

        $this->_checkFileExists($filename, true);

        // test simple delete
        $closureDelete = $this->s3->delete($filename);
        $closureDelete($this->s3, ['filename' => $filename]);
        // should now 403 as the file should be gone
        $headers = @get_headers($url);
        $this->assertEqual('HTTP/1.1 403 Forbidden', $headers[0]);

        $this->_checkFileExists($filename, false);
    }

    protected function _checkFileExists($filename, $expected) {
        $closure = $this->s3->exists($filename);
        $result = $closure($this->s3, compact('filename'));
        if ($expected) {
            $this->assertTrue($result);
        }
        $this->assertFalse($result);
    }
}
