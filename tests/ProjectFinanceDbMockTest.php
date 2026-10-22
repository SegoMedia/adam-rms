<?php

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\TestCase;

#[BackupGlobals(true)]
class ProjectFinanceDbMockTest extends TestCase
{
    private $projectFinance;
    private $dbMock;

    protected function setUp(): void
    {
        $this->projectFinance = new projectFinance();
        // Reset the mock DB before each test
        $GLOBALS['DBLIB']->reset();
        $this->dbMock = $GLOBALS['DBLIB'];
    }

    public function testDurationMathsByDates()
    {
        $start = '2023-01-01';
        $end = '2023-01-03';
        $result = $this->projectFinance->durationMathsByDates($start, $end);

        $this->assertEquals(3, $result['days']);
        $this->assertEquals(0, $result['weeks']);
        $this->assertEquals(3, $result['calendarDays']);
    }

    public function testDurationMathsByDatesOneDay()
    {
        $start = '2023-01-01';
        $end = '2023-01-01';
        $result = $this->projectFinance->durationMathsByDates($start, $end);

        $this->assertEquals(1, $result['days']);
        $this->assertEquals(0, $result['weeks']);
        $this->assertEquals(1, $result['calendarDays']);
    }

    public function testDurationMathsUsingDB()
    {
        // Mock the DB response
        $this->dbMock->setReturnValue('getone', [
            'projects_dates_finances_days' => 5,
            'projects_dates_finances_weeks' => 1,
            'projects_dates_deliver_start' => '2023-01-01',
            'projects_dates_deliver_end' => '2023-01-05'
        ]);

        $result = $this->projectFinance->durationMaths(123);

        $this->assertEquals(5, $result['days']);
        $this->assertEquals(1, $result['weeks']);
        $this->assertEquals(5, $result['calendarDays']);

        // Check if DB was called correctly
        $calls = $this->dbMock->calls;
        $foundWhere = false;
        foreach ($calls as $call) {
            if ($call['method'] === 'where' && $call['args'][0] === 'projects_id' && $call['args'][1] === 123) {
                $foundWhere = true;
            }
        }
        $this->assertTrue($foundWhere, "DBLIB->where was not called with correct project ID");
    }

    public function testDurationMathsUsingDBWithNulls()
    {
        // Mock the DB response with NULLs for finance days/weeks
        $this->dbMock->setReturnValue('getone', [
            'projects_dates_finances_days' => NULL,
            'projects_dates_finances_weeks' => NULL,
            'projects_dates_deliver_start' => '2023-01-01',
            'projects_dates_deliver_end' => '2023-01-03'
        ]);

        $result = $this->projectFinance->durationMaths(123);

        $this->assertEquals(3, $result['days']);
        $this->assertEquals(0, $result['weeks']);
        $this->assertEquals(3, $result['calendarDays']);
    }
}

class ProjectFinanceCacherTest extends TestCase
{
    /**
     * @backupGlobals enabled
     */
    private $cacher;
    private $dbMock;

    protected function setUp(): void
    {
        // Mock AUTH data needed for constructor
        $GLOBALS['AUTH']->data['instance']['instances_config_currency'] = 'GBP';

        $this->cacher = new projectFinanceCacher(123);
        $GLOBALS['DBLIB']->reset();
        $this->dbMock = $GLOBALS['DBLIB'];
    }

    public function testAdjust()
    {
        // Adjust value
        $money = new Money\Money(1000, new Money\Currency('GBP'));
        $this->cacher->adjust('projectsFinanceCache_equipmentSubTotal', $money);

        // We can't easily inspect private property $data, but we can test saving
        $this->cacher->save();

        // Verify DB update
        $calls = $this->dbMock->calls;
        $updateCall = null;
        foreach ($calls as $call) {
            if ($call['method'] === 'update') {
                $updateCall = $call;
            }
        }

        $this->assertNotNull($updateCall);
        $this->assertEquals('projectsFinanceCache', $updateCall['args'][0]);
        // The value passed to inc is checking by the mock returning ["INC" => value]
        // But here we check if inc was called with 1000

        $incCalls = [];
        foreach ($calls as $call) {
            if ($call['method'] === 'inc') {
                $incCalls[] = $call;
            }
        }

        // We expect inc to be called for the adjusted value
        $foundInc = false;
        foreach ($incCalls as $call) {
            if ($call['args'][0] == 1000) {
                $foundInc = true;
            }
        }
        $this->assertTrue($foundInc, "DBLIB->inc was not called with 1000");
    }

    public function testAdjustPayment()
    {
        $money = new Money\Money(500, new Money\Currency('GBP'));
        $this->cacher->adjustPayment(1, $money); // Type 1 = projectsFinanceCache_paymentsReceived

        $this->cacher->save();

        $calls = $this->dbMock->calls;
        $incCalls = [];
        foreach ($calls as $call) {
            if ($call['method'] === 'inc') {
                $incCalls[] = $call;
            }
        }

        $foundInc = false;
        foreach ($incCalls as $call) {
            if ($call['args'][0] == 500) {
                $foundInc = true;
            }
        }
        $this->assertTrue($foundInc, "DBLIB->inc was not called with 500 for payment adjustment");
    }
}
