<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Laramongo\StoresProductsIntegration\CsvParser,
    Laramongo\StoresProductsIntegration\StoreProduct;

class ImportPriceCsvContext extends BaseContext {

    public function __construct() { }

    /**
     * @Given /^I have no StoreProduct into database$/
     */
    public function iHaveNoStoreproductIntoDatabase()
    {
        foreach (StoreProduct::all() as $storeProduct) {
            $storeProduct->delete();
        }
    }


    /**
     * @Given /^I have the following line in csv:$/
     */
    public function iHaveTheFollowingLineInCsv(TableNode $table)
    {
        $header = $table->getRows()[0];
        $values = $table->getRows()[1];

        $this->line = array_combine($header, $values);
    }

    /**
     * @When /^I process the line$/
     */
    public function iProcessTheLine()
    {
        $parser = new CsvParser;

        $this->testCase()->assertTrue( $parser->parseLine($this->line) );
    }

    /**
     * @Then /^I should have the following StoreProduct into database:$/
     */
    public function iShouldHaveTheFollowingPriceIntoDatabase(PyStringNode $should_be)
    {
        $result = StoreProduct::first()->toArray();
        $should_be = json_decode($should_be->getRaw(), true);
        
        $this->testCase()->assertEquals($should_be, $result);
    }

}
