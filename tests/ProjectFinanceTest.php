<?php
require_once __DIR__ . '/bootstrap.php';

class ProjectFinanceTest extends TestCase {
    public function testSameDay() {
        $pf = new projectFinance();
        $result = $pf->durationMathsByDates('2023-10-27', '2023-10-27');
        $this->assertEquals(1, $result['days'], 'Same day should be 1 day');
    }

    public function testMultipleDays() {
        $pf = new projectFinance();
        $result = $pf->durationMathsByDates('2023-10-27', '2023-10-29');
        $this->assertEquals(3, $result['days'], '27th to 29th should be 3 days');
    }

    public function testEndBeforeStart() {
        $pf = new projectFinance();
        $result = $pf->durationMathsByDates('2023-10-27', '2023-10-26');
        $this->assertEquals(1, $result['days'], 'End before start should be 1 day (current behavior)');
    }

    public function testDSTAutumn() {
        // DST ends on 29 Oct 2023 in Europe/London
        $pf = new projectFinance();
        $result = $pf->durationMathsByDates('2023-10-28', '2023-10-30');
        $this->assertEquals(3, $result['days'], '28th to 30th Oct should be 3 days (crossing DST end)');
    }

    public function testDSTSpring() {
        // DST starts on 26 Mar 2023 in Europe/London
        $pf = new projectFinance();
        $result = $pf->durationMathsByDates('2023-03-25', '2023-03-27');
        $this->assertEquals(3, $result['days'], '25th to 27th Mar should be 3 days (crossing DST start)');
    }
}

$test = new ProjectFinanceTest();
if (!$test->run()) {
    exit(1);
}
