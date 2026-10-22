<?php

use PHPUnit\Framework\TestCase;

class BCMSTest extends TestCase
{
    /**
     * @backupGlobals enabled
     */
    private $bCMS;
    private $dbMock;

    protected function setUp(): void
    {
        global $bCMS;
        $this->bCMS = $bCMS;
        $GLOBALS['DBLIB']->reset();
        $this->dbMock = $GLOBALS['DBLIB'];
    }

    public function testSanitizeString()
    {
        $input = "Hello ' World";
        // sanitizeString calls htmlspecialchars
        $expected = "Hello ' World"; // ENT_NOQUOTES doesn't escape single quotes

        $result = $this->bCMS->sanitizeString($input);
        $this->assertEquals($expected, $result);
    }

    public function testSanitizeStringWithTags()
    {
        $input = "<b>Hello</b>";
        // sanitizeString calls htmlspecialchars
        $expected = "&lt;b&gt;Hello&lt;/b&gt;";

        $result = $this->bCMS->sanitizeString($input);
        $this->assertEquals($expected, $result);
    }

    public function testRandomString()
    {
        // Test standard random string
        $result = $this->bCMS->randomString(50);
        $this->assertEquals(50, strlen($result));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $result);

        // Ensure at least one number is generated when stringonly=false
        // By generating a long enough string, the probability of no numbers is effectively zero
        $hasNumber = preg_match('/[0-9]/', $result);
        $this->assertEquals(1, $hasNumber, "Random string did not contain numbers despite stringonly=false");

        // Test string only
        $resultStringOnly = $this->bCMS->randomString(50, true);
        $this->assertEquals(50, strlen($resultStringOnly));
        $this->assertMatchesRegularExpression('/^[a-zA-Z]+$/', $resultStringOnly);

        $hasNumberStringOnly = preg_match('/[0-9]/', $resultStringOnly);
        $this->assertEquals(0, $hasNumberStringOnly, "Random string contained numbers despite stringonly=true");
    }

    public function testFormatSize()
    {
        $this->assertEquals('1 KB', $this->bCMS->formatSize(1024));
        $this->assertEquals('1.0 MB', $this->bCMS->formatSize(1048576));
        $this->assertEquals('1.0 GB', $this->bCMS->formatSize(1073741824));
        $this->assertEquals('500 bytes', $this->bCMS->formatSize(500));
        $this->assertEquals('0 bytes', $this->bCMS->formatSize(0));
    }

    public function testAuditLog()
    {
        // auditLog($actionType, $table, $revelantData, $userid, $useridTo, $projectid, $targetid)

        // Mock insert to return true
        $this->dbMock->setReturnValue('insert', true);

        $result = $this->bCMS->auditLog('UPDATE', 'users', 'data', 1, 2, 10, 5);

        $this->assertTrue($result);

        // Check if DB insert was called
        $calls = $this->dbMock->calls;
        $insertCall = null;
        foreach ($calls as $call) {
            if ($call['method'] === 'insert' && $call['args'][0] === 'auditLog') {
                $insertCall = $call;
            }
        }

        $this->assertNotNull($insertCall);
        $data = $insertCall['args'][1];
        $this->assertEquals('UPDATE', $data['auditLog_actionType']);
        $this->assertEquals('users', $data['auditLog_actionTable']);
        $this->assertEquals('data', $data['auditLog_actionData']);
        $this->assertEquals(1, $data['users_userid']);
        $this->assertEquals(2, $data['auditLog_actionUserid']);
        $this->assertEquals(10, $data['projects_id']);
        $this->assertEquals(5, $data['auditLog_targetID']);
    }

    public function testS3StorageUsed()
    {
        // Mock getValue to return a sum
        $this->dbMock->setReturnValue('getValue', 1024);

        $result = $this->bCMS->s3StorageUsed(1);

        $this->assertEquals(1024, $result);

        // Check DB calls
        $calls = $this->dbMock->calls;
        $foundWhere = false;
        foreach ($calls as $call) {
            if ($call['method'] === 'where' && $call['args'][0] === 's3files.instances_id' && $call['args'][1] === 1) {
                $foundWhere = true;
            }
        }
        $this->assertTrue($foundWhere);
    }
}
